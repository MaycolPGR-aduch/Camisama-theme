/**
 * Lienzo de diseño con Fabric.js.
 *
 * El diseño (elementos en cm) es la fuente de verdad: este componente lo dibuja y, cuando el
 * cliente mueve, escala o gira algo, devuelve la nueva geometría en cm (onTransform). Cada objeto
 * queda recortado a su zona y no puede salirse de ella.
 */
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { Canvas, FabricImage } from 'fabric';

import { fitsInside, fromCanvas, pushInside, zoneBox } from './geometry';
import { TEXT_BASE, applyElement, createObject, snapshot } from './objects';

// Zoom máximo al acercar una zona (las mangas y la nuca son pequeñas) y margen alrededor.
const MAX_ZOOM = 4;
const ZOOM_FILL = 0.8;

/**
 * Encuadre que acerca una zona: escala y desplazamiento en px del lienzo, sin dejar ver fuera de
 * la maqueta. Null si no hay que acercar nada.
 *
 * @param {?Object} zone Zona (rect relativo a la maqueta, de 0 a 1).
 * @param {Object}  size Tamaño del lienzo en px.
 * @return {?{scale: number, x: number, y: number}} Encuadre.
 */
function zoneView( zone, size ) {
	if ( ! zone || ! size.width ) {
		return null;
	}
	const { x, y, w, h } = zone.rect;
	const scale = Math.max(
		1,
		Math.min( MAX_ZOOM, ZOOM_FILL / Math.max( w, h ) )
	);
	const clamp = ( value, length ) =>
		Math.min( 0, Math.max( length - length * scale, value ) );
	return {
		scale,
		x: clamp(
			size.width / 2 - scale * ( x + w / 2 ) * size.width,
			size.width
		),
		y: clamp(
			size.height / 2 - scale * ( y + h / 2 ) * size.height,
			size.height
		),
	};
}

/**
 * Dónde poner la etiqueta de una zona para que no pise a las demás. Va centrada encima, salvo
 * en una zona central que tenga otra justo encima (la nuca sobre la espalda) o zonas a su misma
 * altura (las mangas junto al frente): entonces va debajo y las de los lados quedan solas arriba.
 *
 * @param {Object}   zone  Zona (rect relativo a la maqueta, de 0 a 1).
 * @param {Object[]} zones Zonas de la vista.
 * @return {string} Clase de colocación, o cadena vacía.
 */
function labelPlacement( zone, zones ) {
	const { x, y, w } = zone.rect;
	const center = x + w / 2;
	if ( center < 0.4 || center > 0.6 ) {
		return '';
	}
	const others = zones.filter( ( other ) => other.id !== zone.id );
	const below = others.some(
		( other ) =>
			// Otra zona justo encima…
			( other.rect.y < y &&
				other.rect.y + other.rect.h > y - 0.06 &&
				other.rect.x < x + w &&
				other.rect.x + other.rect.w > x ) ||
			// …o empezando a la misma altura, al lado (las mangas junto al frente; no la espalda,
			// que queda justo debajo de la nuca).
			( Math.abs( other.rect.y - y ) < 0.08 &&
				( other.rect.x + other.rect.w <= x || other.rect.x >= x + w ) )
	);
	return below ? ' is-label-below' : '';
}

export default function FabricStage( {
	mockup,
	zones,
	activeZoneId,
	elements,
	fonts,
	selectedId,
	label,
	onSelect,
	onSelectZone,
	onTransform,
	onMeasure,
	onError,
	focusZone,
	children,
} ) {
	const wrapRef = useRef( null );
	const canvasElRef = useRef( null );
	const canvasRef = useRef( null );
	const objects = useRef( new Map() );
	const pending = useRef( new Set() );
	const syncing = useRef( false );
	const latest = useRef( {} );
	const [ size, setSize ] = useState( { width: 0, height: 0 } );
	const aspect = mockup ? mockup.width / mockup.height : 1;

	latest.current = {
		zones,
		elements,
		fonts,
		onSelect,
		onSelectZone,
		onTransform,
		onMeasure,
		onError,
	};

	// Lienzo y eventos (una sola vez).
	useEffect( () => {
		const canvas = new Canvas( canvasElRef.current, {
			selection: false,
			preserveObjectStacking: true,
			uniformScaling: true,
			// En pantallas táctiles el dedo desplaza la página salvo si empieza sobre el
			// elemento seleccionado, que entonces se arrastra: el lienzo no atrapa el scroll.
			allowTouchScrolling: true,
		} );
		canvasRef.current = canvas;

		const boxOf = ( obj ) => {
			const element = latest.current.elements.find(
				( item ) => item.id === obj.elementId
			);
			const zone =
				element &&
				latest.current.zones.find(
					( item ) => item.id === element.zone
				);
			return zone ? zoneBox( zone, canvas.width, canvas.height ) : null;
		};

		// Durante una transformación Fabric no recalcula las coordenadas del objeto: sin
		// setCoords(), getBoundingRect() devolvería la posición anterior.
		canvas.on( 'object:moving', ( { target } ) => {
			const box = boxOf( target );
			if ( ! box ) {
				return;
			}
			target.setCoords();
			const { dx, dy } = pushInside( target.getBoundingRect(), box );
			if ( dx || dy ) {
				target.set( { left: target.left + dx, top: target.top + dy } );
				target.setCoords();
			}
			target.lastValid = snapshot( target );
		} );

		// Escalar o girar no puede sacar el objeto de la zona: se vuelve al último estado válido.
		const keepInside = ( { target } ) => {
			const box = boxOf( target );
			if ( ! box ) {
				return;
			}
			target.setCoords();
			if ( fitsInside( target.getBoundingRect(), box ) ) {
				target.lastValid = snapshot( target );
			} else if ( target.lastValid ) {
				target.set( target.lastValid );
				target.setCoords();
			}
		};
		canvas.on( 'object:scaling', keepInside );
		canvas.on( 'object:rotating', keepInside );

		canvas.on( 'object:modified', ( { target } ) => {
			const box = boxOf( target );
			const element = latest.current.elements.find(
				( item ) => item.id === target.elementId
			);
			if ( ! box || ! element ) {
				return;
			}
			const geometry = fromCanvas(
				target.getCenterPoint(),
				{
					width: target.getScaledWidth(),
					height: target.getScaledHeight(),
					angle: target.angle,
				},
				box
			);
			if ( element.kind === 'text' ) {
				geometry.font_size_cm =
					Math.round(
						( ( target.scaleX * TEXT_BASE ) / box.ppc ) * 100
					) / 100;
			}
			latest.current.onTransform( element.id, geometry );
		} );

		const select = ( { selected } ) => {
			if ( ! syncing.current ) {
				latest.current.onSelect( selected?.[ 0 ]?.elementId ?? null );
			}
		};
		canvas.on( 'selection:created', select );
		canvas.on( 'selection:updated', select );
		canvas.on( 'selection:cleared', () => {
			if ( ! syncing.current ) {
				latest.current.onSelect( null );
			}
		} );

		// Un clic en una zona vacía la convierte en la zona activa.
		canvas.on( 'mouse:down', ( { target, scenePoint } ) => {
			if ( target || ! scenePoint ) {
				return;
			}
			const zone = latest.current.zones.find( ( item ) => {
				const box = zoneBox( item, canvas.width, canvas.height );
				return (
					scenePoint.x >= box.x &&
					scenePoint.x <= box.x + box.w &&
					scenePoint.y >= box.y &&
					scenePoint.y <= box.y + box.h
				);
			} );
			if ( zone ) {
				latest.current.onSelectZone( zone.id );
			}
		} );

		const created = objects.current;
		return () => {
			canvas.dispose();
			canvasRef.current = null;
			created.clear();
		};
	}, [] );

	// Tamaño: ocupa el ancho disponible con la proporción de la maqueta.
	useEffect( () => {
		const observer = new window.ResizeObserver( ( [ entry ] ) => {
			const width = Math.round( entry.contentRect.width );
			setSize( { width, height: Math.round( width / aspect ) } );
		} );
		observer.observe( wrapRef.current );
		return () => observer.disconnect();
	}, [ aspect ] );

	useEffect( () => {
		if ( size.width ) {
			canvasRef.current.setDimensions( size );
		}
	}, [ size ] );

	// Acercar la zona del elemento seleccionado (modo diseño del móvil): zoom de Fabric, que
	// redibuja nítido y traduce el puntero; las líneas de zona (HTML) siguen el mismo encuadre.
	const view = useMemo(
		() => zoneView( focusZone, size ),
		[ focusZone, size ]
	);
	useEffect( () => {
		const canvas = canvasRef.current;
		if ( ! canvas || ! size.width ) {
			return;
		}
		canvas.setViewportTransform(
			view
				? [ view.scale, 0, 0, view.scale, view.x, view.y ]
				: [ 1, 0, 0, 1, 0, 0 ]
		);
		canvas.getObjects().forEach( ( obj ) => obj.setCoords() );
		canvas.requestRenderAll();
	}, [ view, size ] );

	// Maqueta de fondo.
	useEffect( () => {
		const canvas = canvasRef.current;
		if ( ! size.width || ! mockup ) {
			return;
		}
		let cancelled = false;
		FabricImage.fromURL( mockup.url, { crossOrigin: 'anonymous' } ).then(
			( image ) => {
				if ( cancelled || ! canvasRef.current ) {
					return;
				}
				image.set( {
					originX: 'left',
					originY: 'top',
					left: 0,
					top: 0,
					scaleX: size.width / image.width,
					scaleY: size.height / image.height,
				} );
				canvas.backgroundImage = image;
				canvas.requestRenderAll();
			}
		);
		return () => {
			cancelled = true;
		};
	}, [ mockup, size ] );

	// Objetos: crear, actualizar, borrar, ordenar y seleccionar según el diseño.
	useEffect( () => {
		const canvas = canvasRef.current;
		if ( ! canvas || ! size.width ) {
			return;
		}

		const place = ( obj, element ) => {
			const zone = zones.find( ( item ) => item.id === element.zone );
			if ( ! zone ) {
				return;
			}
			const font =
				fonts.find( ( item ) => item.slug === element.font ) ??
				fonts[ 0 ];
			const drawn = applyElement(
				obj,
				element,
				zoneBox( zone, size.width, size.height ),
				font
			);
			// El tamaño real lo da el dibujo: el ancho de un texto depende de sus letras y un SVG
			// agrupado mide lo que ocupan sus trazos, no su viewBox. Así el precio usa lo que se imprime.
			if (
				Math.abs( drawn.width_cm - element.width_cm ) > 0.05 ||
				Math.abs( drawn.height_cm - element.height_cm ) > 0.05
			) {
				latest.current.onMeasure( element.id, drawn );
			}
		};

		const syncSelectionAndOrder = () => {
			const current = latest.current.elements;
			let index = 0;
			for ( const element of current ) {
				const obj = objects.current.get( element.id );
				if ( obj ) {
					canvas.moveObjectTo( obj, index++ );
				}
			}

			syncing.current = true;
			const target = selectedId && objects.current.get( selectedId );
			if ( target && canvas.getActiveObject() !== target ) {
				canvas.setActiveObject( target );
			} else if ( ! target && canvas.getActiveObject() ) {
				canvas.discardActiveObject();
			}
			syncing.current = false;
			canvas.requestRenderAll();
		};

		const ids = new Set( elements.map( ( element ) => element.id ) );
		for ( const [ id, obj ] of objects.current ) {
			if ( ! ids.has( id ) ) {
				canvas.remove( obj );
				objects.current.delete( id );
			}
		}

		for ( const element of elements ) {
			const existing = objects.current.get( element.id );
			if ( existing ) {
				place( existing, element );
				continue;
			}
			if ( pending.current.has( element.id ) ) {
				continue;
			}

			pending.current.add( element.id );
			const font =
				fonts.find( ( item ) => item.slug === element.font ) ??
				fonts[ 0 ];
			createObject( element, font )
				.then( ( obj ) => {
					pending.current.delete( element.id );
					const fresh = latest.current.elements.find(
						( item ) => item.id === element.id
					);
					if ( ! canvasRef.current || ! fresh ) {
						return;
					}
					objects.current.set( element.id, obj );
					canvas.add( obj );
					place( obj, fresh );
					syncSelectionAndOrder();
				} )
				.catch( () => {
					pending.current.delete( element.id );
					latest.current.onError( element.id );
				} );
		}

		syncSelectionAndOrder();
	}, [ elements, zones, fonts, size, selectedId ] );

	return (
		<div
			className="camisama-customizer__stage"
			ref={ wrapRef }
			role="group"
			aria-label={ label }
		>
			<canvas ref={ canvasElRef } />
			<div
				className="camisama-customizer__zones"
				aria-hidden="true"
				style={
					view
						? {
								transform: `translate(${ view.x }px, ${ view.y }px) scale(${ view.scale })`,
								'--zoom': view.scale,
							}
						: undefined
				}
			>
				{ zones.map( ( zone ) => (
					<div
						key={ zone.id }
						className={ `camisama-customizer__zone${
							zone.id === activeZoneId ? ' is-active' : ''
						}${ labelPlacement( zone, zones ) }` }
						style={ {
							left: `${ zone.rect.x * 100 }%`,
							top: `${ zone.rect.y * 100 }%`,
							width: `${ zone.rect.w * 100 }%`,
							height: `${ zone.rect.h * 100 }%`,
						} }
					>
						<span>{ zone.label }</span>
					</div>
				) ) }
			</div>
			{ children }
		</div>
	);
}

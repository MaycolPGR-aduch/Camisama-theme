/**
 * Lienzo de diseño con Fabric.js.
 *
 * El diseño (elementos en cm) es la fuente de verdad: este componente lo dibuja y, cuando el
 * cliente mueve, escala o gira algo, devuelve la nueva geometría en cm (onTransform). Cada objeto
 * queda recortado a su zona y no puede salirse de ella.
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import {
	Canvas,
	FabricImage,
	FabricText,
	Rect,
	loadSVGFromURL,
	util,
} from 'fabric';

import { loadFont } from './fonts';
import {
	fitsInside,
	fromCanvas,
	pushInside,
	toCanvas,
	zoneBox,
} from './geometry';

/** Tamaño de letra (px) con que se crea el texto; el tamaño real lo da la escala. */
const TEXT_BASE = 100;

const coarsePointer = window.matchMedia?.( '(pointer: coarse)' ).matches;

const OBJECT_STYLE = {
	originX: 'center',
	originY: 'center',
	borderColor: '#16161a',
	cornerColor: '#ffffff',
	cornerStrokeColor: '#16161a',
	cornerStyle: 'circle',
	transparentCorners: false,
	// En pantallas táctiles el área que responde al dedo es mayor que el punto que se ve.
	cornerSize: coarsePointer ? 14 : 12,
	touchCornerSize: 40,
	padding: 4,
	lockScalingFlip: true,
};

const snapshot = ( obj ) => ( {
	left: obj.left,
	top: obj.top,
	scaleX: obj.scaleX,
	scaleY: obj.scaleY,
	angle: obj.angle,
} );

async function createObject( element, font ) {
	let obj;
	if ( element.kind === 'text' ) {
		await loadFont( font );
		obj = new FabricText( element.text, {
			fontFamily: font.family,
			fontWeight: font.weight,
			fontSize: TEXT_BASE,
			fill: element.color,
			textAlign: element.align,
		} );
	} else if ( element.mime === 'image/svg+xml' ) {
		const { objects, options } = await loadSVGFromURL( element.src );
		obj = util.groupSVGElements( objects.filter( Boolean ), options );
	} else {
		obj = await FabricImage.fromURL( element.src, {
			crossOrigin: 'anonymous',
		} );
	}

	obj.set( OBJECT_STYLE );
	obj.setControlsVisibility( { mt: false, mb: false, ml: false, mr: false } );
	obj.elementId = element.id;

	return obj;
}

/**
 * Coloca el objeto según el elemento y devuelve su tamaño real en cm (para los textos, cuyo
 * ancho depende de las letras).
 *
 * @param {Object} obj     Objeto de Fabric.
 * @param {Object} element Elemento del diseño.
 * @param {Object} box     Zona en píxeles (zoneBox).
 * @param {Object} font    Fuente del texto.
 * @return {{width_cm: number, height_cm: number}} Tamaño dibujado.
 */
function applyElement( obj, element, box, font ) {
	if ( element.kind === 'text' ) {
		obj.set( {
			text: element.text,
			fontFamily: font.family,
			fontWeight: font.weight,
			fill: element.color,
			textAlign: element.align,
		} );
	}

	const transform =
		element.kind === 'text'
			? {
					left: box.x + element.cx * box.ppc,
					top: box.y + element.cy * box.ppc,
					scale: ( element.font_size_cm * box.ppc ) / TEXT_BASE,
					angle: element.angle,
				}
			: toCanvas( element, box, obj.width );

	obj.set( {
		left: transform.left,
		top: transform.top,
		scaleX: transform.scale,
		scaleY: transform.scale,
		angle: transform.angle,
		clipPath: new Rect( {
			left: box.x,
			top: box.y,
			width: box.w,
			height: box.h,
			originX: 'left',
			originY: 'top',
			absolutePositioned: true,
		} ),
	} );
	obj.setCoords();
	obj.lastValid = snapshot( obj );

	return {
		width_cm: Math.round( ( obj.getScaledWidth() / box.ppc ) * 100 ) / 100,
		height_cm:
			Math.round( ( obj.getScaledHeight() / box.ppc ) * 100 ) / 100,
	};
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
			<div className="camisama-customizer__zones" aria-hidden="true">
				{ zones.map( ( zone ) => (
					<div
						key={ zone.id }
						className={ `camisama-customizer__zone${
							zone.id === activeZoneId ? ' is-active' : ''
						}` }
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
		</div>
	);
}

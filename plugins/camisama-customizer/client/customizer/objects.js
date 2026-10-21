/**
 * Creación y colocación de objetos de Fabric.js a partir de los elementos del diseño.
 *
 * Lo comparten el lienzo del cliente (FabricStage), las vistas previas del carrito y los archivos
 * de impresión (renderer.js), para que todo se dibuje exactamente igual.
 */
import { FabricImage, FabricText, Rect, loadSVGFromURL, util } from 'fabric';

import { loadFont } from './fonts';
import { toCanvas } from './geometry';

/** Tamaño de letra (px) con que se crea el texto; el tamaño real lo da la escala. */
export const TEXT_BASE = 100;

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

export const snapshot = ( obj ) => ( {
	left: obj.left,
	top: obj.top,
	scaleX: obj.scaleX,
	scaleY: obj.scaleY,
	angle: obj.angle,
} );

export async function createObject( element, font ) {
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
export function applyElement( obj, element, box, font ) {
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

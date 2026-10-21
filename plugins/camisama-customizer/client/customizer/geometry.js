/**
 * Conversión entre el diseño (cm de cada zona) y el lienzo (píxeles).
 *
 * El rectángulo de una zona en la maqueta representa exactamente su tamaño en cm, así que la
 * escala px/cm sale de dividir su ancho en píxeles entre su ancho en cm.
 */
import { elementBounds } from './pricing';

/**
 * Rectángulo de la zona en píxeles del lienzo, con su escala.
 *
 * @param {Object} zone   Zona (rect en fracciones de la maqueta y size_cm).
 * @param {number} width  Ancho del lienzo en px.
 * @param {number} height Alto del lienzo en px.
 * @return {{x: number, y: number, w: number, h: number, ppc: number}} Rectángulo y px por cm.
 */
export function zoneBox( zone, width, height ) {
	const w = zone.rect.w * width;

	return {
		x: zone.rect.x * width,
		y: zone.rect.y * height,
		w,
		h: zone.rect.h * height,
		ppc: w / zone.size_cm.w,
	};
}

/**
 * Posición y escala de un objeto del lienzo para dibujar un elemento.
 *
 * @param {Object} element     Elemento (cx, cy, width_cm, angle).
 * @param {Object} box         Resultado de zoneBox().
 * @param {number} objectWidth Ancho propio del objeto, sin escalar.
 * @return {{left: number, top: number, scale: number, angle: number}} Transformación.
 */
export function toCanvas( element, box, objectWidth ) {
	return {
		left: box.x + element.cx * box.ppc,
		top: box.y + element.cy * box.ppc,
		scale: ( element.width_cm * box.ppc ) / objectWidth,
		angle: element.angle ?? 0,
	};
}

/**
 * Geometría del elemento a partir de un objeto del lienzo.
 *
 * @param {{x: number, y: number}}                         center Centro del objeto (px).
 * @param {{width: number, height: number, angle: number}} size   Tamaño escalado (px) y giro.
 * @param {Object}                                         box    Resultado de zoneBox().
 * @return {{cx: number, cy: number, width_cm: number, height_cm: number, angle: number}} Geometría en cm.
 */
export function fromCanvas( center, size, box ) {
	const round = ( value ) => Math.round( value * 100 ) / 100;

	return {
		cx: round( ( center.x - box.x ) / box.ppc ),
		cy: round( ( center.y - box.y ) / box.ppc ),
		width_cm: round( size.width / box.ppc ),
		height_cm: round( size.height / box.ppc ),
		angle: round( ( ( size.angle % 360 ) + 360 ) % 360 ),
	};
}

/**
 * Desplazamiento mínimo para que un rectángulo quede dentro de otro.
 *
 * @param {{left: number, top: number, width: number, height: number}} rect      Rectángulo del objeto.
 * @param {{x: number, y: number, w: number, h: number}}               container Zona.
 * @return {{dx: number, dy: number}} Desplazamiento en px.
 */
export function pushInside( rect, container ) {
	const shift = ( start, size, min, max ) => {
		if ( size >= max - min ) {
			return min + ( max - min ) / 2 - ( start + size / 2 );
		}
		if ( start < min ) {
			return min - start;
		}
		if ( start + size > max ) {
			return max - ( start + size );
		}
		return 0;
	};

	return {
		dx: shift(
			rect.left,
			rect.width,
			container.x,
			container.x + container.w
		),
		dy: shift(
			rect.top,
			rect.height,
			container.y,
			container.y + container.h
		),
	};
}

/**
 * Si un rectángulo cabe dentro de otro (con una tolerancia de 1 px por redondeos).
 *
 * @param {{left: number, top: number, width: number, height: number}} rect      Rectángulo del objeto.
 * @param {{x: number, y: number, w: number, h: number}}               container Zona.
 * @return {boolean} Si cabe.
 */
export function fitsInside( rect, container ) {
	return (
		rect.left >= container.x - 1 &&
		rect.top >= container.y - 1 &&
		rect.left + rect.width <= container.x + container.w + 1 &&
		rect.top + rect.height <= container.y + container.h + 1
	);
}

/**
 * Rectángulo (cm de la zona) que ocupa todo lo que hay en ella, recortado a la zona.
 *
 * @param {Object} zone     Zona (size_cm).
 * @param {Array}  elements Elementos de la zona.
 * @return {{left: number, top: number, right: number, bottom: number}} Rectángulo.
 */
export function printedBounds( zone, elements ) {
	const boxes = elements.map( elementBounds );

	return {
		left: Math.max( 0, Math.min( ...boxes.map( ( box ) => box.left ) ) ),
		top: Math.max( 0, Math.min( ...boxes.map( ( box ) => box.top ) ) ),
		right: Math.min(
			zone.size_cm.w,
			Math.max( ...boxes.map( ( box ) => box.right ) )
		),
		bottom: Math.min(
			zone.size_cm.h,
			Math.max( ...boxes.map( ( box ) => box.bottom ) )
		),
	};
}

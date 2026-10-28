/**
 * Creación de elementos del diseño y reglas que no dependen del lienzo.
 */
import { elementBounds, tierFor } from './pricing';

let counter = 0;

const round = ( value ) => Math.round( value * 100 ) / 100;

/**
 * Encaja un elemento en su zona: lo encoge si no cabe (también el tamaño de letra de un texto)
 * y lo desplaza lo mínimo para que quede dentro.
 *
 * @param {Object} element Elemento.
 * @param {Object} zone    Zona (size_cm).
 * @return {Object} Elemento ajustado (el mismo objeto si ya cabía).
 */
export function fitToZone( element, zone ) {
	const bounds = elementBounds( element );
	const factor = Math.min(
		1,
		zone.size_cm.w / ( bounds.right - bounds.left ),
		zone.size_cm.h / ( bounds.bottom - bounds.top )
	);

	let result = element;
	if ( factor < 0.9999 ) {
		result = {
			...element,
			width_cm: round( element.width_cm * factor ),
			height_cm: round( element.height_cm * factor ),
		};
		if ( element.font_size_cm ) {
			result.font_size_cm = round( element.font_size_cm * factor );
		}
	}

	const fitted = elementBounds( result );
	let dx = 0;
	let dy = 0;
	if ( fitted.left < 0 ) {
		dx = -fitted.left;
	} else if ( fitted.right > zone.size_cm.w ) {
		dx = zone.size_cm.w - fitted.right;
	}
	if ( fitted.top < 0 ) {
		dy = -fitted.top;
	} else if ( fitted.bottom > zone.size_cm.h ) {
		dy = zone.size_cm.h - fitted.bottom;
	}

	return dx || dy
		? {
				...result,
				cx: round( result.cx + dx ),
				cy: round( result.cy + dy ),
			}
		: result;
}

/**
 * Identificador único dentro de la sesión.
 *
 * @return {string} Identificador.
 */
export function newId() {
	counter += 1;
	return `el-${ Date.now().toString( 36 ) }-${ counter }`;
}

/**
 * Factor para que algo nuevo no pase por poco el límite de un escalón de precio: si su lado
 * mayor lo supera en menos de lo que le queda (hasta un 40 % más), se queda en ese límite. Lo
 * nuevo no cae en el escalón caro sin que el cliente lo decida.
 *
 * @param {Object} zone    Zona (si los hay, sus escalones en tiers).
 * @param {number} longest Lado mayor previsto en cm.
 * @return {number} Factor entre 0,6 y 1.
 */
export function tierFit( zone, longest ) {
	const limit = Math.max(
		0,
		...( zone.tiers ?? [] )
			.map( ( tier ) => tier.max_cm )
			.filter( ( cm ) => cm < longest && cm >= longest * 0.6 )
	);
	return limit > 0 ? limit / longest : 1;
}

/**
 * Tamaño inicial: el 60 % de la zona sin deformar la proporción del contenido, ajustado al
 * límite del escalón si lo pasa por poco (tierFit).
 *
 * @param {Object} zone   Zona (size_cm y, si los hay, sus escalones en tiers).
 * @param {number} aspect Ancho / alto del contenido.
 * @return {{width_cm: number, height_cm: number}} Tamaño en cm.
 */
export function initialSize( zone, aspect ) {
	const maxW = zone.size_cm.w * 0.6;
	const maxH = zone.size_cm.h * 0.6;
	let width = Math.min( maxW, maxH * aspect );
	width *= tierFit( zone, Math.max( width, width / aspect ) );

	return {
		width_cm: Math.round( width * 100 ) / 100,
		height_cm: Math.round( ( width / aspect ) * 100 ) / 100,
	};
}

/**
 * Estampado del catálogo centrado en la zona.
 *
 * @param {Object} zone   Zona donde se coloca.
 * @param {Object} design Estampado de la API (id, title, file, premium).
 * @return {Object} Elemento.
 */
export function designElement( zone, design ) {
	return {
		id: newId(),
		zone: zone.id,
		kind: 'design',
		design_id: design.id,
		premium: design.premium,
		title: design.title,
		src: design.file.url,
		mime: design.file.mime,
		cx: zone.size_cm.w / 2,
		cy: zone.size_cm.h / 2,
		angle: 0,
		...initialSize( zone, design.file.width / design.file.height ),
	};
}

/**
 * Imagen subida por el cliente centrada en la zona.
 *
 * @param {Object} zone   Zona donde se coloca.
 * @param {Object} upload Respuesta de la API de subidas (id, url, width, height).
 * @return {Object} Elemento.
 */
export function imageElement( zone, upload ) {
	return {
		id: newId(),
		zone: zone.id,
		kind: 'image',
		upload_id: upload.id,
		src: upload.url,
		pixel_width: upload.width,
		pixel_height: upload.height,
		cx: zone.size_cm.w / 2,
		cy: zone.size_cm.h / 2,
		angle: 0,
		...initialSize( zone, upload.width / upload.height ),
	};
}

/**
 * Texto centrado en la zona. El ancho real lo mide el lienzo al dibujarlo.
 *
 * @param {Object} zone  Zona donde se coloca.
 * @param {Object} style Texto, fuente y color iniciales.
 * @return {Object} Elemento.
 */
export function textElement( zone, style ) {
	const fontSize =
		Math.round( Math.min( zone.size_cm.h * 0.15, 4 ) * 100 ) / 100;

	return {
		id: newId(),
		zone: zone.id,
		kind: 'text',
		text: style.text,
		font: style.font,
		color: style.color,
		align: style.align ?? 'center',
		font_size_cm: fontSize,
		cx: zone.size_cm.w / 2,
		cy: zone.size_cm.h / 2,
		width_cm: fontSize * style.text.length * 0.6,
		height_cm: fontSize * 1.2,
		angle: 0,
	};
}

/**
 * Calidad de impresión de una imagen al tamaño elegido (misma regla que PrintResolution en PHP).
 *
 * @param {Object}                                element    Elemento de tipo image.
 * @param {{target_dpi: number, min_dpi: number}} resolution Umbrales.
 * @return {{dpi: number, level: string}} DPI efectivo y nivel optimal | acceptable | low.
 */
export function imageQuality( element, resolution ) {
	const dpi = Math.round( element.pixel_width / ( element.width_cm / 2.54 ) );

	let level = 'low';
	if ( dpi >= resolution.target_dpi ) {
		level = 'optimal';
	} else if ( dpi >= resolution.min_dpi ) {
		level = 'acceptable';
	}

	return { dpi, level };
}

// Paso (cm) con el que se buscan huecos en la zona, y tamaños que se prueban si no cabe entero.
const PLACE_STEP = 0.5;
const PLACE_SCALES = [ 1, 0.75, 0.5 ];

/**
 * Área (cm²) que comparten dos rectángulos.
 *
 * @param {Object} a Límites (left, top, right, bottom).
 * @param {Object} b Límites.
 * @return {number} Área común.
 */
function overlapArea( a, b ) {
	const w = Math.min( a.right, b.right ) - Math.max( a.left, b.left );
	const h = Math.min( a.bottom, b.bottom ) - Math.max( a.top, b.top );
	return w > 0 && h > 0 ? w * h : 0;
}

/**
 * Coloca un elemento nuevo donde no pise a los que ya hay en su zona. Entre los huecos libres
 * gana el que deja la zona en el escalón de precio más barato (el precio va por el lado mayor de
 * todo lo que hay en ella); a igualdad, el elemento más grande y, después, el más cercano a donde
 * iba (el centro). Prueba el tamaño completo, el 75 % y la mitad; si no cabe libre en ninguno, lo
 * deja a tamaño completo donde menos se solape. Sin nada debajo, no lo mueve.
 *
 * @param {Object}   element Elemento nuevo, ya dentro de la zona (fitToZone).
 * @param {Object}   zone    Zona (size_cm y, si los hay, sus escalones en tiers).
 * @param {Object[]} others  Elementos del diseño (se miran solo los de la misma zona).
 * @return {Object} Elemento colocado (el mismo objeto si no hacía falta moverlo).
 */
export function placeFree( element, zone, others ) {
	const boxes = others
		.filter( ( item ) => item.zone === element.zone )
		.map( elementBounds );
	if ( ! boxes.length ) {
		return element;
	}
	const union = boxes.reduce( ( all, box ) => ( {
		left: Math.min( all.left, box.left ),
		top: Math.min( all.top, box.top ),
		right: Math.max( all.right, box.right ),
		bottom: Math.max( all.bottom, box.bottom ),
	} ) );

	// Cómo queda un sitio: cuánto pisa y qué escalón cobra la zona (sin escalones, no cuenta).
	const rate = ( candidate ) => {
		const bounds = elementBounds( candidate );
		const overlap = boxes.reduce(
			( sum, box ) => sum + overlapArea( bounds, box ),
			0
		);
		const longest = Math.max(
			Math.max( union.right, bounds.right ) -
				Math.min( union.left, bounds.left ),
			Math.max( union.bottom, bounds.bottom ) -
				Math.min( union.top, bounds.top )
		);
		let cost = 0;
		if ( zone.tiers?.length ) {
			cost = tierFor( zone.tiers, longest )?.amount ?? Infinity;
		}
		return {
			candidate,
			overlap,
			cost,
			distance: Math.hypot(
				candidate.cx - element.cx,
				candidate.cy - element.cy
			),
		};
	};
	if ( rate( element ).overlap === 0 ) {
		return element;
	}

	let free = null;
	let fallback = null;
	for ( const scale of PLACE_SCALES ) {
		const sized =
			scale === 1
				? element
				: {
						...element,
						width_cm: round( element.width_cm * scale ),
						height_cm: round( element.height_cm * scale ),
						...( element.font_size_cm && {
							font_size_cm: round( element.font_size_cm * scale ),
						} ),
					};
		const bounds = elementBounds( sized );
		const halfW = ( bounds.right - bounds.left ) / 2;
		const halfH = ( bounds.bottom - bounds.top ) / 2;
		for (
			let y = halfH;
			y <= zone.size_cm.h - halfH + 0.001;
			y += PLACE_STEP
		) {
			for (
				let x = halfW;
				x <= zone.size_cm.w - halfW + 0.001;
				x += PLACE_STEP
			) {
				const spot = rate( {
					...sized,
					cx: round( x ),
					cy: round( y ),
				} );
				// Libre: el más barato; a igualdad gana el tamaño anterior (más grande) y el más cercano.
				if (
					spot.overlap < 0.001 &&
					( ! free ||
						spot.cost < free.cost ||
						( spot.cost === free.cost &&
							spot.candidate.width_cm ===
								free.candidate.width_cm &&
							spot.distance < free.distance ) )
				) {
					free = spot;
				}
				if (
					scale === 1 &&
					( ! fallback ||
						spot.overlap < fallback.overlap - 0.001 ||
						( spot.overlap <= fallback.overlap + 0.001 &&
							spot.distance < fallback.distance ) )
				) {
					fallback = spot;
				}
			}
		}
	}

	return ( free ?? fallback )?.candidate ?? element;
}

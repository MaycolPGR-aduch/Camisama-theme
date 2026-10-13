/**
 * Utilidades puras del editor de zonas (probadas en utils.test.js).
 */

/**
 * Identificador en minúsculas, sin tildes ni espacios ("Manga izquierda" → "manga-izquierda").
 *
 * @param {string} text     Texto de partida.
 * @param {string} fallback Valor si el texto no deja ningún carácter válido.
 * @return {string} Slug.
 */
export function slugify( text, fallback = 'zona' ) {
	const slug = String( text )
		.normalize( 'NFD' )
		.replace( /[̀-ͯ]/g, '' )
		.toLowerCase()
		.replace( /[^a-z0-9]+/g, '-' )
		.replace( /^-+|-+$/g, '' );

	return slug || fallback;
}

/**
 * Primer identificador libre: base, base-2, base-3…
 *
 * @param {string}   base  Identificador deseado.
 * @param {string[]} taken Identificadores ya usados.
 * @return {string} Identificador único.
 */
export function uniqueId( base, taken ) {
	let id = base;
	for ( let n = 2; taken.includes( id ); n++ ) {
		id = `${ base }-${ n }`;
	}
	return id;
}

/**
 * Limita un valor a un rango.
 *
 * @param {number} value Valor.
 * @param {number} min   Mínimo.
 * @param {number} max   Máximo.
 * @return {number} Valor limitado.
 */
export function clamp( value, min, max ) {
	return Math.min( Math.max( value, min ), max );
}

/**
 * Redondea a 4 decimales, la precisión con la que se guardan las posiciones.
 *
 * @param {number} value Valor.
 * @return {number} Valor redondeado.
 */
export function round4( value ) {
	return Math.round( value * 10000 ) / 10000;
}

/**
 * Alto (fracción de la imagen) que conserva la proporción física de la zona.
 *
 * La zona representa size.w × size.h cm; en una imagen de proporción ancho/alto = aspect,
 * su alto en fracción es w · aspect · (size.h / size.w).
 *
 * @param {number}                 w      Ancho de la zona (fracción del ancho de la imagen).
 * @param {{w: number, h: number}} size   Tamaño de la zona en cm.
 * @param {number}                 aspect Ancho / alto de la imagen.
 * @return {number} Alto (fracción del alto de la imagen).
 */
export function heightFor( w, size, aspect ) {
	return w * aspect * ( size.h / size.w );
}

/**
 * Rectángulo con la proporción de `size`, centrado en el mismo punto y dentro de la imagen.
 *
 * @param {{x: number, y: number, w: number, h: number}} rect   Rectángulo actual.
 * @param {{w: number, h: number}}                       size   Tamaño en cm.
 * @param {number}                                       aspect Ancho / alto de la imagen.
 * @return {{x: number, y: number, w: number, h: number}} Rectángulo ajustado.
 */
export function fitRect( rect, size, aspect ) {
	let w = rect.w;
	let h = heightFor( w, size, aspect );

	if ( h > 1 ) {
		w = w / h;
		h = 1;
	}

	const cx = rect.x + rect.w / 2;
	const cy = rect.y + rect.h / 2;

	return {
		x: round4( clamp( cx - w / 2, 0, 1 - w ) ),
		y: round4( clamp( cy - h / 2, 0, 1 - h ) ),
		w: round4( w ),
		h: round4( h ),
	};
}

/**
 * Escalones de precio que puede alcanzar una zona (misma regla que PriceTable::tiers_within en PHP).
 *
 * @param {Array<{label: string, max_cm: number, amount: number}>} tiers     Escalones ordenados.
 * @param {number}                                                 longestCm Lado mayor de la zona.
 * @return {Array<{label: string, max_cm: number, amount: number}>} Escalones alcanzables.
 */
export function tiersWithin( tiers, longestCm ) {
	const reachable = [];
	let previous = 0;

	for ( const tier of tiers ) {
		if ( longestCm <= previous + 0.001 ) {
			break;
		}
		reachable.push( tier );
		previous = tier.max_cm;
	}

	return reachable;
}

/**
 * Normaliza la configuración que llega de PHP (un array vacío de PHP llega como [] y no como {}).
 *
 * @param {Object} config Configuración guardada.
 * @return {Object} Configuración con la forma que espera el editor.
 */
export function normalizeConfig( config ) {
	return {
		version: 1,
		enabled: Boolean( config?.enabled ),
		views: ( config?.views ?? [] ).map( ( view ) => ( {
			...view,
			mockups: Array.isArray( view.mockups )
				? {}
				: ( view.mockups ?? {} ),
		} ) ),
		zones: config?.zones ?? [],
	};
}

/**
 * Contraste entre colores (fórmula de las WCAG), para que un texto no quede invisible sobre la
 * prenda: negro sobre una camiseta negra se imprime igual de bien, pero nadie lo verá.
 */

/** Por debajo de este contraste un texto apenas se distingue de la prenda (WCAG, texto grande). */
export const MIN_CONTRAST = 3;

/**
 * Luminancia relativa de un color #RGB o #RRGGBB, o null si no es un color válido.
 *
 * @param {string} hex Color en hexadecimal.
 * @return {?number} Luminancia entre 0 (negro) y 1 (blanco).
 */
function luminance( hex ) {
	const match = /^#?([0-9a-f]{3}|[0-9a-f]{6})$/i.exec( hex ?? '' );
	if ( ! match ) {
		return null;
	}
	const digits =
		match[ 1 ].length === 3
			? [ ...match[ 1 ] ].map( ( digit ) => digit + digit ).join( '' )
			: match[ 1 ];
	const [ r, g, b ] = [ 0, 2, 4 ].map( ( start ) => {
		const channel = parseInt( digits.slice( start, start + 2 ), 16 ) / 255;
		return channel <= 0.04045
			? channel / 12.92
			: ( ( channel + 0.055 ) / 1.055 ) ** 2.4;
	} );
	return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

/**
 * Contraste entre dos colores, de 1 (iguales) a 21 (negro sobre blanco).
 *
 * @param {string} a Color en hexadecimal.
 * @param {string} b Color en hexadecimal.
 * @return {?number} Contraste, o null si alguno no es un color válido.
 */
export function contrastRatio( a, b ) {
	const la = luminance( a );
	const lb = luminance( b );
	if ( la === null || lb === null ) {
		return null;
	}
	return ( Math.max( la, lb ) + 0.05 ) / ( Math.min( la, lb ) + 0.05 );
}

/**
 * ¿Se distingue el color sobre el fondo? Si no se conoce el fondo, se da por bueno.
 *
 * @param {string} color      Color del texto.
 * @param {string} background Color de la prenda.
 * @return {boolean} Verdadero si se ve.
 */
export function isReadable( color, background ) {
	const ratio = contrastRatio( color, background );
	return ratio === null || ratio >= MIN_CONTRAST;
}

/**
 * El candidato que más contrasta con el fondo (el primero si el fondo no es válido).
 *
 * @param {string}   background Color de la prenda.
 * @param {string[]} candidates Colores posibles, en hexadecimal.
 * @return {string} Uno de los candidatos.
 */
export function mostReadable( background, candidates ) {
	return candidates.reduce( ( best, color ) =>
		( contrastRatio( color, background ) ?? 0 ) >
		( contrastRatio( best, background ) ?? 0 )
			? color
			: best
	);
}

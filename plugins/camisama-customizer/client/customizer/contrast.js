/**
 * Contraste entre colores (fórmula de las WCAG), para que un texto no quede invisible sobre la
 * prenda: negro sobre una camiseta negra se imprime igual de bien, pero nadie lo verá.
 */

/** Por debajo de este contraste un texto apenas se distingue de la prenda (WCAG, texto grande). */
export const MIN_CONTRAST = 3;

/**
 * Un estampado o una foto "no se ve" si la mayor parte de lo que se imprime apenas se distingue
 * de la prenda: azul marino sobre negro. Basta un contraste menor que el del texto, porque una
 * imagen se reconoce por su forma y sus partes claras.
 */
export const MIN_IMAGE_CONTRAST = 1.5;
export const MAX_HIDDEN_SHARE = 0.5;

/**
 * Luminancia relativa de un color en canales de 0 a 255.
 *
 * @param {number} r Rojo.
 * @param {number} g Verde.
 * @param {number} b Azul.
 * @return {number} Luminancia entre 0 (negro) y 1 (blanco).
 */
export function rgbLuminance( r, g, b ) {
	const [ lr, lg, lb ] = [ r, g, b ].map( ( value ) => {
		const channel = value / 255;
		return channel <= 0.04045
			? channel / 12.92
			: ( ( channel + 0.055 ) / 1.055 ) ** 2.4;
	} );
	return 0.2126 * lr + 0.7152 * lg + 0.0722 * lb;
}

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
	const [ r, g, b ] = [ 0, 2, 4 ].map( ( start ) =>
		parseInt( digits.slice( start, start + 2 ), 16 )
	);
	return rgbLuminance( r, g, b );
}

const contrastOf = ( la, lb ) =>
	( Math.max( la, lb ) + 0.05 ) / ( Math.min( la, lb ) + 0.05 );

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
	return contrastOf( la, lb );
}

/**
 * Parte de una imagen que apenas se distingue de la prenda.
 *
 * @param {?number[]} tones      Luminancias de una muestra de sus píxeles visibles (imageTones).
 * @param {string}    background Color de la prenda.
 * @return {?number} Entre 0 y 1, o null si no se sabe (sin muestra o sin color de prenda).
 */
export function hiddenShare( tones, background ) {
	const lb = luminance( background );
	if ( lb === null || ! tones?.length ) {
		return null;
	}
	const hidden = tones.filter(
		( tone ) => contrastOf( tone, lb ) < MIN_IMAGE_CONTRAST
	).length;
	return hidden / tones.length;
}

/**
 * ¿Se ve la imagen sobre la prenda? Si no se sabe, se da por buena.
 *
 * @param {?number[]} tones      Luminancias de una muestra de sus píxeles visibles.
 * @param {string}    background Color de la prenda.
 * @return {boolean} Verdadero si se ve.
 */
export function isImageVisible( tones, background ) {
	const share = hiddenShare( tones, background );
	return share === null || share <= MAX_HIDDEN_SHARE;
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

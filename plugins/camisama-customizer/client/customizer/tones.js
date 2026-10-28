/**
 * Tonos de una imagen (estampado o foto) para saber si se verá sobre la prenda (contrast.js):
 * luminancias de una muestra de sus píxeles visibles. Se calcula una vez por imagen.
 */
import { rgbLuminance } from './contrast';

// Lado de la muestra: suficiente para la proporción de tonos, barato de leer.
const SAMPLE = 48;

const cache = new Map();

/**
 * Luminancias de los píxeles visibles (no transparentes) de una imagen reducida.
 *
 * @param {string} src Dirección de la imagen (del mismo sitio).
 * @return {Promise<?number[]>} Luminancias, o null si no se pudo leer.
 */
export function imageTones( src ) {
	if ( ! cache.has( src ) ) {
		cache.set(
			src,
			new Promise( ( resolve ) => {
				const image = new window.Image();
				image.crossOrigin = 'anonymous';
				image.onload = () => {
					try {
						const canvas = document.createElement( 'canvas' );
						canvas.width = SAMPLE;
						canvas.height = SAMPLE;
						const context = canvas.getContext( '2d' );
						context.drawImage( image, 0, 0, SAMPLE, SAMPLE );
						const { data } = context.getImageData(
							0,
							0,
							SAMPLE,
							SAMPLE
						);
						const tones = [];
						for ( let i = 0; i < data.length; i += 4 ) {
							if ( data[ i + 3 ] >= 128 ) {
								tones.push(
									rgbLuminance(
										data[ i ],
										data[ i + 1 ],
										data[ i + 2 ]
									)
								);
							}
						}
						resolve( tones );
					} catch {
						resolve( null );
					}
				};
				image.onerror = () => resolve( null );
				image.src = src;
			} )
		);
	}
	return cache.get( src );
}

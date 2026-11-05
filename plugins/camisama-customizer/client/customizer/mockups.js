/**
 * La maqueta pertenece al color elegido; nunca se sustituye por otro color.
 */
import { __ } from '@wordpress/i18n';

export const PREVIEW_BACKGROUND = '#F7F4EE';

/**
 * Maqueta válida para una vista y un color, o null si falta.
 *
 * @param {?Object} view  Vista.
 * @param {string}  color Slug del color.
 * @return {?Object} Imagen con URL y dimensiones.
 */
export function mockupFor( view, color ) {
	const image = view?.mockups?.[ color ];
	return image &&
		typeof image.url === 'string' &&
		image.url.length > 0 &&
		Number.isFinite( image.width ) &&
		image.width > 0 &&
		Number.isFinite( image.height ) &&
		image.height > 0
		? image
		: null;
}

/**
 * Evita confirmar una prenda cuya representación del color está incompleta.
 *
 * @param {Object} data  Datos del producto.
 * @param {string} color Color elegido.
 * @throws {Error} Aviso visible al cliente si falta una maqueta.
 */
export function requireColorMockups( data, color ) {
	if (
		! data.views?.length ||
		data.views.some( ( view ) => ! mockupFor( view, color ) )
	) {
		const error = new Error(
			__(
				'Falta una vista de esta prenda en el color elegido. Elige otro color o vuelve a intentarlo más tarde.',
				'camisama-customizer'
			)
		);
		error.notice = true;
		throw error;
	}
}

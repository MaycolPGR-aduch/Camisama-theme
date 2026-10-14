/**
 * Llamadas a la API camisama/v1.
 */
import { __ } from '@wordpress/i18n';

/**
 * Petición a la API; los errores devuelven el mensaje que manda el servidor.
 *
 * @param {Object} api     Raíz y nonce de la API.
 * @param {string} path    Ruta relativa ("/designs?page=1").
 * @param {Object} options Opciones de fetch.
 * @return {Promise<{data: any, headers: Headers}>} Respuesta.
 */
async function request( api, path, options = {} ) {
	let response;
	try {
		response = await window.fetch( api.root + path, {
			credentials: 'same-origin',
			...options,
			headers: { 'X-WP-Nonce': api.nonce, ...( options.headers ?? {} ) },
		} );
	} catch {
		throw new Error(
			__(
				'No hay conexión con la tienda. Revisa tu internet e inténtalo de nuevo.',
				'camisama-customizer'
			)
		);
	}

	const data = await response.json().catch( () => null );
	if ( ! response.ok ) {
		throw new Error(
			data?.message ??
				__(
					'Algo salió mal. Inténtalo de nuevo.',
					'camisama-customizer'
				)
		);
	}

	return { data, headers: response.headers };
}

/**
 * Categorías del catálogo.
 *
 * @param {Object} api Raíz y nonce de la API.
 * @return {Promise<Array>} Categorías.
 */
export async function fetchCategories( api ) {
	return ( await request( api, '/design-categories' ) ).data;
}

/**
 * Página de estampados.
 *
 * @param {Object}      api      Raíz y nonce de la API.
 * @param {string|null} category Slug de categoría o null.
 * @param {number}      page     Página.
 * @return {Promise<{items: Array, pages: number}>} Estampados y número de páginas.
 */
export async function fetchDesigns( api, category, page ) {
	const query = new URLSearchParams( {
		page: String( page ),
		per_page: '24',
	} );
	if ( category ) {
		query.set( 'category', category );
	}

	const { data, headers } = await request( api, `/designs?${ query }` );

	return {
		items: data,
		pages: Number( headers.get( 'X-WP-TotalPages' ) ?? 1 ),
	};
}

/**
 * Sube una imagen del cliente.
 *
 * @param {Object} api  Raíz y nonce de la API.
 * @param {File}   file Archivo elegido.
 * @return {Promise<Object>} Subida (id, url, width, height, mime).
 */
export async function uploadImage( api, file ) {
	const body = new window.FormData();
	body.append( 'file', file );

	return ( await request( api, '/uploads', { method: 'POST', body } ) ).data;
}

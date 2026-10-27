/**
 * Borrador del diseño en el dispositivo del cliente (localStorage), por producto.
 *
 * Si se recarga la página, se cierra la pestaña o el móvil la descarta al cambiar de app, el
 * diseño vuelve al entrar. Caduca a los 7 días (las imágenes subidas que no acaban en un pedido
 * se borran a los 30) y no se recupera si ese mismo diseño ya se añadió al carrito.
 */

const PREFIX = 'camisama:draft:';
const VERSION = 1;
const KINDS = [ 'design', 'text', 'image' ];

export const DRAFT_TTL = 7 * 24 * 60 * 60 * 1000;

/**
 * Almacenamiento del navegador, o null si no se puede usar (modo privado, cookies bloqueadas).
 *
 * @return {?Storage} localStorage.
 */
function browserStorage() {
	try {
		return window.localStorage;
	} catch {
		return null;
	}
}

/**
 * Recupera el borrador de un producto, descartando lo que ya no encaja con su configuración.
 *
 * @param {number}   productId ID del producto.
 * @param {Object}   data      Datos del personalizador (zonas, vistas y colores válidos).
 * @param {?Storage} storage   Almacenamiento (por defecto localStorage).
 * @param {number}   now       Momento actual en ms.
 * @return {?Object} { elements, color, viewId, zoneId } o null si no hay nada que recuperar.
 */
export function loadDraft(
	productId,
	data,
	storage = browserStorage(),
	now = Date.now()
) {
	let draft;
	try {
		draft = JSON.parse( storage?.getItem( PREFIX + productId ) ?? 'null' );
	} catch {
		return null;
	}
	if (
		! draft ||
		draft.v !== VERSION ||
		draft.added ||
		! ( now - draft.saved < DRAFT_TTL ) ||
		! Array.isArray( draft.elements )
	) {
		return null;
	}

	const zoneIds = new Set( data.zones.map( ( zone ) => zone.id ) );
	const elements = draft.elements.filter(
		( element ) =>
			element &&
			typeof element.id === 'string' &&
			KINDS.includes( element.kind ) &&
			zoneIds.has( element.zone )
	);
	if ( ! elements.length ) {
		return null;
	}

	const has = ( list, value, key ) =>
		list.some( ( item ) => item[ key ] === value ) ? value : null;
	return {
		elements,
		color: has( data.colors, draft.color, 'slug' ),
		viewId: has( data.views, draft.viewId, 'id' ),
		zoneId: zoneIds.has( draft.zoneId ) ? draft.zoneId : null,
	};
}

/**
 * Guarda el borrador; sin elementos, lo borra. Los fallos del almacenamiento se ignoran: el
 * borrador es una ayuda, no puede romper el editor.
 *
 * @param {number}   productId ID del producto.
 * @param {Object}   draft     { elements, color, viewId, zoneId, added }.
 * @param {?Storage} storage   Almacenamiento (por defecto localStorage).
 * @param {number}   now       Momento actual en ms.
 */
export function saveDraft(
	productId,
	draft,
	storage = browserStorage(),
	now = Date.now()
) {
	try {
		if ( ! draft.elements.length ) {
			storage?.removeItem( PREFIX + productId );
			return;
		}
		storage?.setItem(
			PREFIX + productId,
			JSON.stringify( {
				v: VERSION,
				saved: now,
				added: Boolean( draft.added ),
				elements: draft.elements,
				color: draft.color,
				viewId: draft.viewId,
				zoneId: draft.zoneId,
			} )
		);
	} catch {
		// Lleno o bloqueado: se sigue sin borrador.
	}
}

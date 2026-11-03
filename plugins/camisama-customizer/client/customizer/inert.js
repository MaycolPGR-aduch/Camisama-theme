/**
 * Diálogo a pantalla completa (modo diseño y "Talla y pago" en el móvil): el resto de la página
 * queda inerte, así ni el tabulador ni un lector de pantalla salen del diálogo.
 */

const NEVER = 'script, style, template';

// Lo que la tienda abre encima al añadir (el minicarrito de WooCommerce) tiene que funcionar.
export const KEEP_DRAWER = '.wc-block-components-drawer__screen-overlay';

/**
 * Marca como inerte todo lo que no contiene al nodo (los hermanos de él y de cada antecesor).
 *
 * @param {?Element} node Raíz del diálogo.
 * @param {string}   keep Selector de lo que no se toca, además de scripts y estilos.
 * @return {Function} Deshace lo marcado.
 */
export function inertOutside( node, keep = '' ) {
	const skip = keep ? `${ NEVER }, ${ keep }` : NEVER;
	const changed = [];
	for (
		let current = node;
		current?.parentElement;
		current = current.parentElement
	) {
		for ( const sibling of current.parentElement.children ) {
			if (
				sibling !== current &&
				! sibling.inert &&
				! sibling.matches( skip )
			) {
				sibling.inert = true;
				changed.push( sibling );
			}
		}
		if ( current.parentElement === current.ownerDocument.body ) {
			break;
		}
	}
	return () => changed.forEach( ( element ) => ( element.inert = false ) );
}

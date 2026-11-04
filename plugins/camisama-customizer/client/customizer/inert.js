/**
 * Diálogo a pantalla completa (modo diseño y "Talla y cantidad" en el móvil): el resto de la página
 * queda inerte, así ni el tabulador ni un lector de pantalla salen del diálogo.
 */

const NEVER = 'script, style, template';

/**
 * Marca como inerte todo lo que no contiene al nodo (los hermanos de él y de cada antecesor).
 *
 * @param {?Element} node Raíz del diálogo.
 * @return {Function} Deshace lo marcado.
 */
export function inertOutside( node ) {
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
				! sibling.matches( NEVER )
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

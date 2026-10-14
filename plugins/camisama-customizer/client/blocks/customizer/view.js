/**
 * Monta el personalizador en la ficha del producto.
 *
 * Los datos vienen incrustados por render.php; mientras carga se ve la galería del producto,
 * que se oculta en cuanto el editor está listo.
 */
import { createRoot } from '@wordpress/element';

import App from '../../customizer/App';

document
	.querySelectorAll( '.wp-block-camisama-customizer' )
	.forEach( ( node ) => {
		const script = node.querySelector(
			'.wp-block-camisama-customizer__data'
		);
		if ( ! script ) {
			return;
		}

		const mount = document.createElement( 'div' );
		node.appendChild( mount );
		createRoot( mount ).render(
			<App data={ JSON.parse( script.textContent ) } />
		);
		node.classList.add( 'is-ready' );
	} );

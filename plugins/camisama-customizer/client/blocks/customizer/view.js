/**
 * Monta el personalizador en la ficha del producto.
 *
 * Los datos vienen incrustados por render.php. El editor (con Fabric.js, ~300 KB) se descarga
 * aparte con import() para no bloquear la página: mientras tanto se ve la galería del producto,
 * que se oculta en cuanto el editor está listo. Si la descarga falla, la galería se queda.
 */
import { createRoot } from '@wordpress/element';

const nodes = [
	...document.querySelectorAll( '.wp-block-camisama-customizer' ),
]
	.map( ( node ) => ( {
		node,
		script: node.querySelector( '.wp-block-camisama-customizer__data' ),
	} ) )
	.filter( ( { script } ) => script );

if ( nodes.length ) {
	import( /* webpackChunkName: "customizer-app" */ '../../customizer/App' )
		.then( ( { default: App } ) => {
			for ( const { node, script } of nodes ) {
				const mount = document.createElement( 'div' );
				node.appendChild( mount );
				createRoot( mount ).render(
					<App data={ JSON.parse( script.textContent ) } />
				);
				node.classList.add( 'is-ready' );
			}
		} )
		.catch( () => {
			// Sin editor, el cliente sigue viendo la galería y puede comprar la prenda lisa.
		} );
}

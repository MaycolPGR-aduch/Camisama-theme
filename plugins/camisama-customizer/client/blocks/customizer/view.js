/**
 * Monta el personalizador en la ficha del producto.
 *
 * Los datos vienen incrustados por render.php. El editor (con Fabric.js, ~300 KB) se descarga
 * aparte con import() para no bloquear la página. Mientras tanto, la ficha ya tiene su forma final,
 * para que nada salte: el servidor pinta la prenda en el sitio del lienzo (render.php) y, en el
 * móvil (docs/flujo-movil.md), la entrada "Personalizar" en el suyo (Products\FlowPlaceholder); el
 * formulario de compra de WooCommerce se reserva para el paso de talla y pago. Si la descarga
 * falla, todo vuelve a la ficha de WooCommerce de siempre, con su galería.
 */
import { createRoot, flushSync } from '@wordpress/element';

const nodes = [
	...document.querySelectorAll( '.wp-block-camisama-customizer' ),
]
	.map( ( node ) => ( {
		node,
		script: node.querySelector( '.wp-block-camisama-customizer__data' ),
	} ) )
	.filter( ( { script } ) => script );

if ( nodes.length ) {
	const root = document.documentElement;
	root.classList.add( 'camisama-flow' );
	root.dataset.camisamaEditor = 'loading';

	import( /* webpackChunkName: "customizer-app" */ '../../customizer/App' )
		.then( ( { default: App } ) => {
			for ( const { node, script } of nodes ) {
				const mount = document.createElement( 'div' );
				node.appendChild( mount );
				// De una vez: si el editor se pintara después de quitar la prenda de muestra, la
				// ficha se encogería un instante.
				flushSync( () =>
					createRoot( mount ).render(
						<App data={ JSON.parse( script.textContent ) } />
					)
				);
				node.classList.add( 'is-ready' );
			}
		} )
		.catch( () => {
			// Sin editor, el cliente ve la galería y puede comprar la prenda lisa.
			root.classList.remove( 'camisama-flow' );
			document
				.querySelector(
					'.camisama-customizer__entry[data-placeholder]'
				)
				?.remove();
			// La galería estaba oculta: que se mida ahora.
			window.dispatchEvent( new window.Event( 'resize' ) );
		} );
}

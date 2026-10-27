/**
 * Monta el personalizador en la ficha del producto.
 *
 * Los datos vienen incrustados por render.php. El editor (con Fabric.js, ~300 KB) se descarga
 * aparte con import() para no bloquear la página: mientras tanto se ve la galería del producto,
 * que se oculta en cuanto el editor está listo. Si la descarga falla, la galería se queda.
 *
 * En el móvil la ficha es el paso 1 del flujo (docs/flujo-movil.md): mientras llega el editor,
 * el botón "Personalizar" ya ocupa su sitio diciendo que se está preparando, y el formulario de
 * compra de WooCommerce se reserva para el paso de talla y pago. Si la descarga falla, todo
 * vuelve a la ficha de WooCommerce de siempre.
 */
import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const nodes = [
	...document.querySelectorAll( '.wp-block-camisama-customizer' ),
]
	.map( ( node ) => ( {
		node,
		script: node.querySelector( '.wp-block-camisama-customizer__data' ),
	} ) )
	.filter( ( { script } ) => script );

/**
 * Hueco del botón "Personalizar" junto al formulario de compra, con el estado de carga. App lo
 * reutiliza (data-placeholder) para no mover la página al montarse.
 *
 * @return {?HTMLElement} El hueco, o null si la ficha no tiene formulario de compra.
 */
function placeholderEntry() {
	const form = document.querySelector( '.wp-block-add-to-cart-form' );
	if ( ! form ) {
		return null;
	}
	const entry = document.createElement( 'div' );
	entry.className = 'camisama-customizer camisama-customizer__entry';
	entry.dataset.placeholder = '';
	const button = document.createElement( 'button' );
	button.type = 'button';
	button.className = 'camisama-customizer__cta';
	button.disabled = true;
	button.textContent = __( 'Preparando el editor…', 'camisama-customizer' );
	entry.appendChild( button );
	form.before( entry );
	return entry;
}

if ( nodes.length ) {
	document.documentElement.classList.add( 'camisama-flow' );
	const entry = placeholderEntry();

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
			document.documentElement.classList.remove( 'camisama-flow' );
			entry?.remove();
		} );
}

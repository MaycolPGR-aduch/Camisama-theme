/**
 * Puente con el formulario de variaciones de WooCommerce (jQuery).
 *
 * El color se puede elegir en el editor o en el desplegable de WooCommerce y ambos quedan
 * sincronizados; el precio de la variante elegida es la base del precio en vivo.
 */

const COLOR_FIELD = 'select[name="attribute_pa_color"]';

/**
 * Conecta el editor con el formulario de variaciones del producto.
 *
 * @param {number}   productId         ID del producto.
 * @param {Object}   handlers          Funciones a las que avisar.
 * @param {Function} handlers.onColor  Recibe el slug del color elegido en WooCommerce.
 * @param {Function} handlers.onPrice  Recibe el precio de la variante en céntimos, o null.
 * @param {Function} handlers.onSubmit Antes de enviar al carrito: devuelve (async) los campos
 *                                     ocultos que acompañan al pedido ({ nombre: valor }).
 * @param {Function} handlers.onError  Recibe el error si onSubmit falla (el envío se cancela).
 * @return {{setColor: Function, disconnect: Function, summaryNode: ?HTMLElement}} Control del
 *         puente; summaryNode es el hueco del resumen de precio dentro del formulario.
 */
export function connectVariationForm(
	productId,
	{ onColor, onPrice, onSubmit, onError }
) {
	const $ = window.jQuery;
	const form =
		document.querySelector(
			`form.variations_form[data-product_id="${ productId }"]`
		) ?? document.querySelector( 'form.variations_form' );

	if ( ! $ || ! form ) {
		return { setColor: () => {}, disconnect: () => {}, summaryNode: null };
	}

	const $form = $( form );
	const select = form.querySelector( COLOR_FIELD );

	// Hueco para el resumen del precio justo encima de cantidad y botón: el total queda pegado a
	// "Añadir al carrito" y sustituye al precio de la variante, que solo cuenta la prenda lisa.
	const actions = form.querySelector( '.woocommerce-variation-add-to-cart' );
	let summaryNode = null;
	if ( actions ) {
		summaryNode = document.createElement( 'div' );
		summaryNode.className =
			'camisama-customizer camisama-customizer__purchase';
		actions.before( summaryNode );
		form.classList.add( 'has-camisama-summary' );
	}

	// Los manejadores de jQuery reciben tanto los cambios del usuario como los de .trigger().
	$form.on( 'change.camisama', COLOR_FIELD, ( event ) => {
		if ( event.target.value ) {
			onColor( event.target.value );
		}
	} );
	$form.on( 'found_variation.camisama', ( event, variation ) => {
		onPrice( Math.round( Number( variation.display_price ) * 100 ) );
	} );
	$form.on( 'reset_data.camisama', () => onPrice( null ) );

	if ( select?.value ) {
		onColor( select.value );
	}

	// Añadir al carrito: se detiene el envío, se adjuntan el diseño y las vistas previas (que
	// tardan en generarse) y se vuelve a enviar con requestSubmit, que esta vez sigue de largo.
	const button = form.querySelector( '.single_add_to_cart_button' );
	const submit = async ( event ) => {
		if ( form.dataset.camisama === 'ready' ) {
			delete form.dataset.camisama;
			return;
		}
		event.preventDefault();
		if ( form.dataset.camisama === 'busy' ) {
			return;
		}

		form.dataset.camisama = 'busy';
		button?.classList.add( 'loading' );
		try {
			const fields = await onSubmit();
			for ( const [ name, value ] of Object.entries( fields ) ) {
				let input = form.querySelector( `input[name="${ name }"]` );
				if ( ! input ) {
					input = document.createElement( 'input' );
					input.type = 'hidden';
					input.name = name;
					form.appendChild( input );
				}
				input.value = value;
			}
			form.dataset.camisama = 'ready';
			form.requestSubmit( button ?? undefined );
		} catch ( error ) {
			delete form.dataset.camisama;
			onError( error );
		} finally {
			button?.classList.remove( 'loading' );
		}
	};
	form.addEventListener( 'submit', submit );

	return {
		setColor( slug ) {
			if ( select && select.value !== slug ) {
				$( select ).val( slug ).trigger( 'change' );
			}
		},
		disconnect() {
			$form.off( '.camisama' );
			form.removeEventListener( 'submit', submit );
			summaryNode?.remove();
			form.classList.remove( 'has-camisama-summary' );
		},
		summaryNode,
	};
}

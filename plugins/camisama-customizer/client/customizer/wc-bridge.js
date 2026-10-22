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
 * @param {number}   productId          ID del producto.
 * @param {Object}   handlers           Funciones a las que avisar.
 * @param {Function} handlers.onColor   Recibe el slug del color elegido en WooCommerce.
 * @param {Function} handlers.onPrice   Recibe el precio de la variante en céntimos, o null.
 * @param {Function} handlers.onOptions Recibe las opciones elegidas aparte del color ("Talla M"),
 *                                      o null si falta alguna.
 * @param {Function} handlers.onBusy    Recibe true mientras se prepara el envío al carrito.
 * @param {Function} handlers.onSubmit  Antes de enviar al carrito: devuelve (async) los campos
 *                                      ocultos que acompañan al pedido ({ nombre: valor }).
 * @param {Function} handlers.onError   Recibe el error si onSubmit falla (el envío se cancela).
 * @return {Object} Control del puente: setColor, buy, disconnect y summaryNode (el hueco del
 *                  resumen de precio dentro del formulario, o null).
 */
export function connectVariationForm(
	productId,
	{ onColor, onPrice, onOptions, onBusy, onSubmit, onError }
) {
	const $ = window.jQuery;
	const form =
		document.querySelector(
			`form.variations_form[data-product_id="${ productId }"]`
		) ?? document.querySelector( 'form.variations_form' );

	if ( ! $ || ! form ) {
		return {
			setColor: () => {},
			buy: () => {},
			disconnect: () => {},
			summaryNode: null,
		};
	}

	const $form = $( form );
	const select = form.querySelector( COLOR_FIELD );
	const button = form.querySelector( '.single_add_to_cart_button' );
	const options = [ ...form.querySelectorAll( 'table.variations select' ) ];

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

	// "Talla M": las opciones que el editor no muestra, con su etiqueta del formulario.
	const describeOptions = () =>
		options
			.filter( ( field ) => field !== select )
			.map( ( field ) =>
				[
					form
						.querySelector( `label[for="${ field.id }"]` )
						?.textContent.trim(),
					field.selectedOptions[ 0 ]?.text,
				]
					.filter( Boolean )
					.join( ' ' )
			)
			.join( ' · ' );

	// Último color elegido: "Limpiar" de WooCommerce vacía el desplegable, pero el editor sigue
	// mostrando ese color y es el que se vuelve a poner al comprar.
	let color = select?.value || null;

	// Los manejadores de jQuery reciben tanto los cambios del usuario como los de .trigger().
	$form.on( 'change.camisama', COLOR_FIELD, ( event ) => {
		if ( event.target.value ) {
			color = event.target.value;
			onColor( event.target.value );
		}
	} );
	$form.on( 'found_variation.camisama', ( event, variation ) => {
		onPrice( Math.round( Number( variation.display_price ) * 100 ) );
		onOptions( describeOptions() );
	} );
	$form.on( 'reset_data.camisama', () => {
		onPrice( null );
		onOptions( null );
	} );

	if ( select?.value ) {
		onColor( select.value );
	}
	// Opciones ya elegidas antes de que cargara el editor (p. ej. la talla por defecto).
	if ( options.length && options.every( ( field ) => field.value ) ) {
		onOptions( describeOptions() );
	}

	// Añadir al carrito: se detiene el envío, se adjuntan el diseño y las vistas previas (que
	// tardan en generarse) y se vuelve a enviar con requestSubmit, que esta vez sigue de largo.
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
		onBusy( true );
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
			onBusy( false );
		}
	};
	form.addEventListener( 'submit', submit );

	return {
		setColor( slug ) {
			color = slug;
			if ( select && select.value !== slug ) {
				$( select ).val( slug ).trigger( 'change' );
			}
		},
		// Compra desde fuera del formulario (la barra del móvil). Si falta una opción, lleva al
		// formulario y enfoca la primera sin elegir en lugar de enviarlo incompleto.
		buy() {
			if ( select && ! select.value && color ) {
				$( select ).val( color ).trigger( 'change' );
			}
			const missing = options.find( ( field ) => ! field.value );
			if ( missing || ! button ) {
				form.scrollIntoView( { block: 'center' } );
				missing?.focus( { preventScroll: true } );
				return;
			}
			form.requestSubmit( button );
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

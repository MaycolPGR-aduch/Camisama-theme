/**
 * Puente con el formulario de variaciones de WooCommerce (jQuery).
 *
 * El color se puede elegir en el editor o en el desplegable de WooCommerce y ambos quedan
 * sincronizados; el precio de la variante elegida es la base del precio en vivo.
 */

import { __ } from '@wordpress/i18n';

const COLOR_FIELD = 'select[name="attribute_pa_color"]';

// Avisos de error de WooCommerce en la página que devuelve el envío (plantilla clásica y bloques).
const ERROR_NOTICES =
	'.woocommerce-error li, .wc-block-components-notice-banner.is-error .wc-block-components-notice-banner__content';

/**
 * Estado del carrito según la Store API: unidades y claves de sus líneas.
 *
 * @param {string} api URL de wc/store/v1/cart.
 * @return {Promise<{count: number, keys: string[]}>} Carrito.
 */
async function cartState( api ) {
	const response = await fetch( api, { credentials: 'same-origin' } );
	if ( ! response.ok ) {
		throw new Error( `Store API ${ response.status }` );
	}
	const cart = await response.json();
	return {
		count: cart.items_count,
		keys: cart.items.map( ( item ) => item.key ),
	};
}

/**
 * Envía el formulario de compra en segundo plano, como lo haría el navegador, para no recargar
 * la página y perder el editor. El resultado se comprueba en la Store API (¿creció el carrito?)
 * y no en el HTML, que solo se lee para mostrar el motivo si WooCommerce lo rechaza.
 *
 * Al reemplazar una línea (editar desde el carrito) el número de unidades puede no cambiar:
 * entonces vale que WooCommerce no haya dado error.
 *
 * @param {HTMLFormElement}    form    Formulario de variaciones.
 * @param {?HTMLButtonElement} button  Botón de envío (en productos simples lleva add-to-cart).
 * @param {string}             api     URL de wc/store/v1/cart.
 * @param {?string}            replace Clave de la línea que se sustituye, o null.
 * @return {Promise<?string>} Clave de la línea con el diseño (si se puede saber).
 */
async function addToCart( form, button, api, replace ) {
	const before = await cartState( api );
	const body = new window.FormData( form );
	if ( button?.name && ! body.has( button.name ) ) {
		body.append( button.name, button.value );
	}
	const response = await fetch( form.action || window.location.href, {
		method: 'POST',
		body,
		credentials: 'same-origin',
	} );
	const html = await response.text();
	const reason = new window.DOMParser()
		.parseFromString( html, 'text/html' )
		.querySelector( ERROR_NOTICES )
		?.textContent.trim();
	const after = await cartState( api );
	const added = after.keys.find( ( key ) => ! before.keys.includes( key ) );
	if ( after.count > before.count || ( replace && ! reason ) ) {
		return added ?? ( after.keys.includes( replace ) ? replace : null );
	}
	const error = new Error(
		reason ||
			__(
				'No pudimos añadir tu diseño al carrito. Inténtalo de nuevo.',
				'camisama-customizer'
			)
	);
	error.notice = true; // Texto pensado para el cliente: se muestra tal cual.
	throw error;
}

/**
 * Conecta el editor con el formulario de variaciones del producto.
 *
 * @param {number}   productId          ID del producto.
 * @param {Object}   cart               Datos del carrito: api (Store API) y url (página).
 * @param {Object}   handlers           Funciones a las que avisar.
 * @param {Function} handlers.onColor   Recibe el slug del color elegido en WooCommerce.
 * @param {Function} handlers.onPrice   Recibe el precio de la variante en céntimos, o null.
 * @param {Function} handlers.onOptions Recibe las opciones elegidas aparte del color ("Talla M"),
 *                                      o null si falta alguna.
 * @param {Function} handlers.onBusy    Recibe true mientras se prepara el envío al carrito.
 * @param {Function} handlers.onSubmit  Antes de enviar al carrito: devuelve (async) los campos
 *                                      ocultos que acompañan al pedido ({ nombre: valor }).
 * @param {Function} handlers.onAdded   Se llama cuando el diseño ya está en el carrito, con la
 *                                      clave de su línea si se puede saber.
 * @param {Function} handlers.onError   Recibe el error si algo falla (el diseño no se añadió).
 * @return {Object} Control del puente: prefill, setLabel, setColor, buy, disconnect y summaryNode (el hueco del
 *                  resumen de precio dentro del formulario, o null).
 */
export function connectVariationForm(
	productId,
	cart,
	{ onColor, onPrice, onOptions, onBusy, onSubmit, onAdded, onError }
) {
	const $ = window.jQuery;
	const form =
		document.querySelector(
			`form.variations_form[data-product_id="${ productId }"]`
		) ?? document.querySelector( 'form.variations_form' );

	if ( ! $ || ! form ) {
		return {
			prefill: () => {},
			setLabel: () => {},
			setColor: () => {},
			buy: () => {},
			disconnect: () => {},
			summaryNode: null,
		};
	}

	const $form = $( form );
	const select = form.querySelector( COLOR_FIELD );
	const button = form.querySelector( '.single_add_to_cart_button' );
	const defaultLabel = button?.textContent;
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
	// tardan en generarse) y se envía en segundo plano. El editor sigue con el diseño y el
	// minicarrito se actualiza (y se abre, si está configurado así) con su miniatura.
	const submit = async ( event ) => {
		event.preventDefault();
		if ( form.dataset.camisama === 'busy' ) {
			return;
		}

		form.dataset.camisama = 'busy';
		// Las vistas previas tardan un momento: el botón dice qué está pasando.
		const label = button?.textContent;
		button?.classList.add( 'loading' );
		if ( button ) {
			button.textContent = __(
				'Preparando tu diseño…',
				'camisama-customizer'
			);
		}
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
			const key = await addToCart(
				form,
				button,
				cart.api,
				fields.camisama_replace || null
			);
			if ( cart.redirect ) {
				// La tienda pide ir al carrito tras añadir: se respeta.
				window.location.assign( cart.url );
				return;
			}
			// Mismo aviso que dan los botones de WooCommerce: refresca el minicarrito.
			$( document.body ).trigger( 'added_to_cart', [
				{},
				'',
				$( button ),
			] );
			document.body.dispatchEvent(
				new window.CustomEvent( 'wc-blocks_added_to_cart', {
					bubbles: true,
					cancelable: true,
					detail: { preserveCartData: false },
				} )
			);
			onAdded( key );
		} catch ( error ) {
			onError( error );
		} finally {
			delete form.dataset.camisama;
			button?.classList.remove( 'loading' );
			if ( button && label ) {
				button.textContent = label;
			}
			onBusy( false );
		}
	};
	form.addEventListener( 'submit', submit );

	return {
		// Editar desde el carrito: la talla (y demás opciones) y la cantidad de esa línea.
		prefill( attributes, quantity ) {
			for ( const [ name, value ] of Object.entries( attributes ) ) {
				const field = form.querySelector(
					`select[name="${ window.CSS.escape( name ) }"]`
				);
				if ( field && value && field.value !== value ) {
					$( field ).val( value ).trigger( 'change' );
				}
			}
			const qty = form.querySelector( 'input.qty' );
			if ( qty && quantity ) {
				qty.value = String( quantity );
			}
		},
		// Texto del botón ("Guardar cambios" al editar); sin texto, vuelve al de WooCommerce.
		setLabel( text ) {
			if ( button ) {
				button.textContent = text || defaultLabel;
			}
		},
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
			// WooCommerce marca el botón como disabled (sin el atributo) si la variante no existe
			// o está agotada: se lleva al formulario, que lo explica.
			if (
				missing ||
				! button ||
				button.classList.contains( 'disabled' )
			) {
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

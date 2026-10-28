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
 * @param {number}   productId           ID del producto.
 * @param {Object}   cart                Datos del carrito: api (Store API) y url (página).
 * @param {Object}   handlers            Funciones a las que avisar.
 * @param {Function} handlers.onColor    Recibe el slug del color elegido en WooCommerce.
 * @param {Function} handlers.onPrice    Recibe el precio de la variante en céntimos, o null.
 * @param {Function} handlers.onQuantity Recibe la cantidad cuando el cliente la cambia en el
 *                                       formulario (escritorio).
 * @param {Function} handlers.onSubmit   Antes de enviar al carrito: devuelve (async) los campos
 *                                       ocultos que acompañan al pedido ({ nombre: valor }).
 * @param {Function} handlers.onAdded    Se llama cuando el diseño ya está en el carrito, con la
 *                                       clave de su línea si se puede saber.
 * @param {Function} handlers.onError    Recibe el error si algo falla (el diseño no se añadió).
 * @param {Function} handlers.onBusy     Recibe true mientras se prepara y envía el diseño.
 * @return {Object} Control del puente: options, choose, guard, buy, prefill, setLabel, setColor, disconnect y
 *                  summaryNode (el hueco del resumen de precio dentro del formulario, o null).
 */
export function connectVariationForm(
	productId,
	cart,
	{ onColor, onPrice, onQuantity, onSubmit, onAdded, onError, onBusy }
) {
	const $ = window.jQuery;
	const form =
		document.querySelector(
			`form.variations_form[data-product_id="${ productId }"]`
		) ?? document.querySelector( 'form.variations_form' );

	if ( ! $ || ! form ) {
		return {
			options: () => [],
			choose: () => {},
			guard: () => () => {},
			buy: () => {},
			prefill: () => {},
			setLabel: () => {},
			setColor: () => {},
			disconnect: () => {},
			summaryNode: null,
		};
	}

	const $form = $( form );
	const select = form.querySelector( COLOR_FIELD );
	const button = form.querySelector( '.single_add_to_cart_button' );
	const defaultLabel = button?.textContent;
	const quantityField = form.querySelector( 'input.qty' );

	// Elige una opción del formulario como si la eligiera el cliente (WooCommerce busca la variante).
	const choose = ( name, value ) => {
		const field = form.querySelector(
			`select[name="${ window.CSS.escape( name ) }"]`
		);
		if ( field && field.value !== value ) {
			$( field ).val( value ).trigger( 'change' );
		}
	};

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
	const quantityChanged = () =>
		onQuantity?.( Math.max( 1, parseInt( quantityField.value, 10 ) || 1 ) );
	quantityField?.addEventListener( 'input', quantityChanged );
	quantityField?.addEventListener( 'change', quantityChanged );

	// Recargo de cada valor de una opción frente a la prenda más barata (la XXL cuesta más), con
	// las variantes que WooCommerce deja en el formulario. Sin ellas (muchas variantes), 0.
	const variations = $form.data( 'product_variations' ) || [];
	const cheapest = Math.min(
		...variations.map( ( variation ) => Number( variation.display_price ) )
	);
	const extraFor = ( name, value ) => {
		const prices = variations
			.filter( ( variation ) =>
				[ value, '' ].includes( variation.attributes?.[ name ] )
			)
			.map( ( variation ) => Number( variation.display_price ) );
		return prices.length
			? Math.round( ( Math.min( ...prices ) - cheapest ) * 100 )
			: 0;
	};

	if ( select?.value ) {
		onColor( select.value );
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
		onBusy?.( true );
		// Las vistas previas tardan un momento: el botón dice qué está pasando.
		const label = button?.textContent;
		button?.classList.add( 'loading' );
		if ( button ) {
			button.textContent = __(
				'Preparando tu diseño…',
				'camisama-customizer'
			);
		}
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
			onBusy?.( false );
			button?.classList.remove( 'loading' );
			if ( button && label ) {
				button.textContent = label;
			}
		}
	};
	form.addEventListener( 'submit', submit );

	return {
		// Opciones del producto aparte del color (la talla), para elegirlas fuera del formulario.
		options() {
			return [ ...form.querySelectorAll( 'table.variations select' ) ]
				.filter( ( field ) => field !== select )
				.map( ( field ) => ( {
					name: field.name,
					label:
						form
							.querySelector( `label[for="${ field.id }"]` )
							?.textContent.trim() ?? field.name,
					values: [ ...field.options ]
						.filter( ( option ) => option.value )
						.map( ( option ) => ( {
							value: option.value,
							text: option.text,
							extra: extraFor( field.name, option.value ),
						} ) ),
				} ) );
		},
		choose,
		// Antes de que actúe WooCommerce al pulsar su botón (escritorio): si falta la talla, el
		// editor avisa en la página en lugar del alert() del navegador. Devuelve cómo quitarlo.
		guard( handler ) {
			button?.addEventListener( 'click', handler, true );
			return () => button?.removeEventListener( 'click', handler, true );
		},
		// Paso de talla y pago del móvil: elige las opciones y la cantidad y envía el formulario
		// (que adjunta el diseño y lo añade sin recargar, como el botón de WooCommerce).
		buy( choices, quantity ) {
			for ( const [ name, value ] of Object.entries( choices ) ) {
				choose( name, value );
			}
			if ( quantityField ) {
				quantityField.value = String( quantity );
			}
			form.requestSubmit( button ?? undefined );
		},
		// Editar desde el carrito: la talla (y demás opciones) y la cantidad de esa línea.
		prefill( attributes, quantity ) {
			for ( const [ name, value ] of Object.entries( attributes ) ) {
				if ( value ) {
					choose( name, value );
				}
			}
			if ( quantityField && quantity ) {
				quantityField.value = String( quantity );
			}
		},
		// Texto del botón ("Guardar cambios" al editar); sin texto, vuelve al de WooCommerce.
		setLabel( text ) {
			if ( button ) {
				button.textContent = text || defaultLabel;
			}
		},
		setColor( slug ) {
			if ( select && select.value !== slug ) {
				$( select ).val( slug ).trigger( 'change' );
			}
		},
		disconnect() {
			$form.off( '.camisama' );
			form.removeEventListener( 'submit', submit );
			quantityField?.removeEventListener( 'input', quantityChanged );
			quantityField?.removeEventListener( 'change', quantityChanged );
			summaryNode?.remove();
			form.classList.remove( 'has-camisama-summary' );
		},
		summaryNode,
	};
}

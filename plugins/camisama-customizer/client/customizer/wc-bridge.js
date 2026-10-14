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
 * @param {number}   productId        ID del producto.
 * @param {Object}   handlers         Funciones a las que avisar.
 * @param {Function} handlers.onColor Recibe el slug del color elegido en WooCommerce.
 * @param {Function} handlers.onPrice Recibe el precio de la variante en céntimos, o null.
 * @return {{setColor: Function, disconnect: Function}} Control del puente.
 */
export function connectVariationForm( productId, { onColor, onPrice } ) {
	const $ = window.jQuery;
	const form =
		document.querySelector(
			`form.variations_form[data-product_id="${ productId }"]`
		) ?? document.querySelector( 'form.variations_form' );

	if ( ! $ || ! form ) {
		return { setColor: () => {}, disconnect: () => {} };
	}

	const $form = $( form );
	const select = form.querySelector( COLOR_FIELD );

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

	return {
		setColor( slug ) {
			if ( select && select.value !== slug ) {
				$( select ).val( slug ).trigger( 'change' );
			}
		},
		disconnect() {
			$form.off( '.camisama' );
		},
	};
}

/**
 * La calle es obligatoria con envío y sobra al recoger. WooCommerce no admite esa condición en sus
 * campos: en Perú va como opcional (Checkout\Delivery) y aquí se vuelve obligatoria cuando toca.
 */
import { __ } from '@wordpress/i18n';

/**
 * Si la calle de una dirección es obligatoria.
 *
 * @param {string}  group      shipping o billing.
 * @param {boolean} collecting Si se recoge en el taller.
 * @return {boolean} Si lo es.
 */
export function needsStreet( group, collecting ) {
	return group === 'shipping' || ! collecting;
}

/**
 * Pone o quita la obligación en los campos que haya. WooCommerce valida con el propio campo; como ya
 * lo revisó al pintarlo, el error que falte se deja listo para enseñarse al pedir.
 */
export function wireStreet() {
	const data = window.wp?.data;
	const checkout = data?.select( 'wc/store/checkout' );
	const store = data?.dispatch( 'wc/store/validation' );
	if ( ! checkout?.prefersCollection || ! store ) {
		return;
	}

	[ 'shipping', 'billing' ].forEach( ( group ) => {
		const input = document.getElementById( `${ group }-address_1` );
		const required = needsStreet(
			group,
			Boolean( checkout.prefersCollection() )
		);
		if ( ! input || input.required === required ) {
			return;
		}
		input.required = required;
		const id = `${ group }_address_1`;
		if ( required && input.value.trim() === '' ) {
			store.setValidationErrors( {
				[ id ]: {
					message: __(
						'Falta este dato: dirección',
						'camisama-customizer'
					),
					hidden: true,
				},
			} );
		} else if ( ! required ) {
			store.clearValidationError( id );
		}
	} );
}

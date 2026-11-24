/**
 * Correo y teléfono del pago: qué se acepta y qué se dice si no. Los patrones son los del atributo
 * `pattern` de HTML (todo el valor, sin ^ ni $).
 */
import { __ } from '@wordpress/i18n';

// Dígitos separados como cada quien los escribe: espacios, guiones, puntos o paréntesis.
const SEPARATOR = String.raw`[\s.\-\(\)]*`;
const digits = ( min, max = min ) =>
	String.raw`(?:${ SEPARATOR }\d){${ min },${ max }}`;
const PERU = String.raw`(?:\+?${ SEPARATOR }5${ SEPARATOR }1${ SEPARATOR })?`;

export const EMAIL_PATTERN = String.raw`[^\s@]+@[^\s@.]+(?:\.[^\s@.]+)*\.[^\s@.]{2,}`;

// Celular peruano (9 y ocho dígitos más), fijo con su código de ciudad, u otro país con "+": quien
// regala puede escribir desde fuera.
export const PHONE_PATTERN = [
	String.raw`${ PERU }9${ digits( 8 ) }`,
	String.raw`${ PERU }\(?0?${ SEPARATOR }[1-8]${ digits( 6, 8 ) }`,
	String.raw`\+(?!${ SEPARATOR }5${ SEPARATOR }1)${ digits( 8, 15 ) }`,
].join( '|' );

/**
 * Campos que se revisan: id del campo, su error en el almacén de WooCommerce, patrón y mensaje.
 */
export const CONTACT_FIELDS = [
	{
		id: 'email',
		errorId: 'billing_email',
		pattern: EMAIL_PATTERN,
		message: () =>
			__(
				'Revisa tu correo: debe ser como nombre@ejemplo.com',
				'camisama-customizer'
			),
	},
	...[ 'shipping', 'billing' ].map( ( group ) => ( {
		id: `${ group }-phone`,
		errorId: `${ group }_phone`,
		pattern: PHONE_PATTERN,
		message: () =>
			__(
				'Revisa tu teléfono: un celular tiene 9 dígitos, como 987 654 321',
				'camisama-customizer'
			),
	} ) ),
];

/**
 * Si un valor cumple un patrón, con la misma regla que el navegador.
 *
 * @param {string} pattern Patrón.
 * @param {string} value   Valor.
 * @return {boolean} Si lo cumple.
 */
export function matches( pattern, value ) {
	return new RegExp( `^(?:${ pattern })$`, 'u' ).test( value.trim() );
}

/**
 * Mensaje que le toca a un campo, o null si no hay que cambiar el de WooCommerce: vacío (le falta el
 * dato, ya lo dice) o bien escrito.
 *
 * @param {Object} field Campo de CONTACT_FIELDS.
 * @param {string} value Valor escrito.
 * @return {string|null} Mensaje.
 */
export function messageFor( field, value ) {
	return value.trim() === '' || matches( field.pattern, value )
		? null
		: field.message();
}

/**
 * Pone los patrones en los campos que haya. WooCommerce valida con ellos al salir del campo y al
 * pedir; si el campo ya venía mal escrito, el error queda listo para enseñarse al pedir.
 */
export function wireContact() {
	const store = window.wp?.data?.dispatch( 'wc/store/validation' );

	CONTACT_FIELDS.forEach( ( field ) => {
		const input = document.getElementById( field.id );
		if ( ! input || input.pattern === field.pattern ) {
			return;
		}
		input.pattern = field.pattern;
		const message = messageFor( field, input.value );
		if ( message && store ) {
			store.setValidationErrors( {
				[ field.errorId ]: { message, hidden: true },
			} );
		}
	} );
}

/**
 * WooCommerce no tiene texto para un patrón que no se cumple y enseña el del navegador ("Haz
 * coincidir el formato solicitado"): se cambia por el nuestro.
 */
export function watchMessages() {
	const data = window.wp?.data;
	data?.subscribe( () => {
		const store = data.select( 'wc/store/validation' );
		CONTACT_FIELDS.forEach( ( field ) => {
			const error = store?.getValidationError( field.errorId );
			const input = error && document.getElementById( field.id );
			const message = input && messageFor( field, input.value );
			if ( message && error.message !== message ) {
				data.dispatch( 'wc/store/validation' ).setValidationErrors( {
					[ field.errorId ]: { message, hidden: error.hidden },
				} );
			}
		} );
	} );
}

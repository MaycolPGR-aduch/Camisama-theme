<?php
/**
 * Textos del carrito y del pago, en la voz de la tienda.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Checkout;

/**
 * Corrige textos de la traducción de los bloques de WooCommerce sin tocar el plugin: las plantillas
 * de error ("introduce un %s válido") concuerdan mal con la etiqueta de cada campo ("un dirección")
 * y hablan de "válido" sobre un campo vacío.
 */
final class Texts {

	/**
	 * Texto original de WooCommerce => texto de la tienda.
	 */
	private const TEXTS = array(
		// Sale sobre todo con el campo vacío; correo y teléfono mal escritos tienen el suyo (contact.js).
		'Please enter a valid %s'            => 'Falta este dato: %s',
		'Please select a %s'                 => 'Elige una opción: %s',
		'Please select a valid option'       => 'Elige una opción de la lista',
		'Please enter a valid email address' => 'Revisa tu correo: debe ser como nombre@ejemplo.com',
		'Privacy Policy'                     => 'Política de privacidad',
		'Proceed to Checkout'                => 'Ir al pago',
		'Estimated total'                    => 'Total',
		// Títulos del pago: con una sola opción ya no se elige nada (style.css).
		'Shipping options'                   => 'Costo del envío',
		'Pickup locations'                   => 'Dónde lo recoges',
		'Payment options'                    => 'Cómo pagas',
		// Sin distrito no hay envío a domicilio todavía (Delivery::rates_in_zone): no es un error.
		'No shipping options are available for this address. Please verify the address is correct or try a different address.' => 'Elige tu distrito para ver el costo del envío. Si estás fuera de Lima Metropolitana y el Callao, elige “Recojo en el taller”.',
		'Shipping options are not available' => 'Elige tu distrito para ver el costo del envío',
		'No available delivery option'       => 'Elige tu distrito',
		'Billing address'                    => 'Datos de facturación',
	);

	/**
	 * Textos que salen del navegador o del servidor: al pedir con envío y sin distrito.
	 */
	private const SHARED_TEXTS = array(
		'Sorry, this order requires a shipping option.' => 'Elige tu distrito para calcular el envío, o pasa a “Recojo en el taller”.',
	);

	/**
	 * Engancha el filtro de WordPress.
	 */
	public function register(): void {
		add_filter( 'load_script_translations', array( self::class, 'filter' ), 10, 4 );
		add_filter( 'render_block_woocommerce/mini-cart-contents', array( self::class, 'close_label' ) );
		add_filter( 'gettext_woocommerce', array( self::class, 'server_text' ), 10, 2 );
	}

	/**
	 * Texto del servidor en la voz de la tienda.
	 *
	 * @param string $translation Texto traducido.
	 * @param string $text        Texto original.
	 */
	public static function server_text( string $translation, string $text ): string {
		return self::SHARED_TEXTS[ $text ] ?? $translation;
	}

	/**
	 * Los textos de los scripts, con los que llevan datos de la tienda (tarifa más baja y plazo).
	 *
	 * @return array<string, string>
	 */
	private static function texts(): array {
		return self::TEXTS + self::SHARED_TEXTS + array(
			'Shipping will be calculated at checkout' => sprintf(
				/* translators: 1: tarifa más baja, en soles, 2: plazo. */
				__( 'Entrega desde S/ %1$s en Lima y Callao, o recojo gratis en el taller: la eliges al pagar. %2$s', 'camisama-customizer' ),
				DeliveryRates::lowest() / 100,
				PaymentTimes::promise()
			),
		);
	}

	/**
	 * El botón de cerrar del carrito lateral se anuncia en inglés: WooCommerce lo escribe tal cual en
	 * su HTML, sin pasar por las traducciones.
	 *
	 * @param string $content HTML del bloque.
	 * @return string
	 */
	public static function close_label( string $content ): string {
		return str_replace( 'aria-label="Close"', 'aria-label="Cerrar"', $content );
	}

	/**
	 * Filtra las traducciones de los scripts de WooCommerce.
	 *
	 * @param mixed  $translations JSON de traducciones.
	 * @param mixed  $file         Archivo.
	 * @param mixed  $handle       Script.
	 * @param string $domain       Dominio.
	 * @return mixed
	 */
	public static function filter( $translations, $file, $handle, string $domain ) {
		return 'woocommerce' === $domain && is_string( $translations ) ? self::rewrite( $translations ) : $translations;
	}

	/**
	 * Cambia los textos que estén en un JSON de traducciones (los demás, y el JSON no válido, no se tocan).
	 *
	 * @param string $json JSON de traducciones.
	 * @return string JSON resultante.
	 */
	public static function rewrite( string $json ): string {
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) || ! isset( $data['locale_data']['messages'] ) || ! is_array( $data['locale_data']['messages'] ) ) {
			return $json;
		}

		$found = array_intersect_key( self::texts(), $data['locale_data']['messages'] );
		if ( array() === $found ) {
			return $json;
		}
		foreach ( $found as $source => $text ) {
			$data['locale_data']['messages'][ $source ] = array( $text );
		}

		$encoded = json_encode( $data, JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- Corre también en las pruebas, sin WordPress.

		return false === $encoded ? $json : $encoded;
	}
}

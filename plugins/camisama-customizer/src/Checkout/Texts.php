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
		'Please enter a valid %s'            => 'Revisa este dato: %s',
		'Please select a %s'                 => 'Elige una opción: %s',
		'Please select a valid option'       => 'Elige una opción de la lista',
		'Please enter a valid email address' => 'Revisa tu correo: debe ser como nombre@ejemplo.com',
		'Privacy Policy'                     => 'Política de privacidad',
		'Proceed to Checkout'                => 'Ir al pago',
		'Estimated total'                    => 'Total',
	);

	/**
	 * Engancha el filtro de WordPress.
	 */
	public function register(): void {
		add_filter( 'load_script_translations', array( self::class, 'filter' ), 10, 4 );
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

		$found = array_intersect_key( self::TEXTS, $data['locale_data']['messages'] );
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

<?php
/**
 * Dirección de entrega adaptada a Perú.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Checkout;

/**
 * Campos de la dirección en Perú: se reparte solo en Lima Metropolitana, así que no se pide región
 * (se queda la de la tienda, Lima) y sí lo que hace falta para llegar: distrito y referencia. Sin
 * código postal, que aquí casi nadie usa.
 */
final class Delivery {

	/**
	 * Código de WooCommerce de la Municipalidad Metropolitana de Lima.
	 */
	public const LIMA = 'LMA';

	/**
	 * Engancha los filtros de WooCommerce.
	 */
	public function register(): void {
		add_filter( 'woocommerce_get_country_locale', array( self::class, 'peru_fields' ) );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( self::class, 'set_lima' ) );
	}

	/**
	 * El pedido guarda Lima como región de entrega: el formulario no la pide.
	 *
	 * @param \WC_Order $order Pedido.
	 */
	public static function set_lima( \WC_Order $order ): void {
		if ( 'PE' === $order->get_shipping_country() && '' === $order->get_shipping_state() ) {
			$order->set_shipping_state( self::LIMA );
		}
	}

	/**
	 * Campos de la dirección para Perú.
	 *
	 * @param array<string, mixed> $locale Campos por país.
	 * @return array<string, mixed>
	 */
	public static function peru_fields( array $locale ): array {
		$locale['PE'] = array(
			'city'      => array( 'label' => __( 'Distrito', 'camisama-customizer' ) ),
			'address_2' => array( 'label' => __( 'Referencia', 'camisama-customizer' ) ),
			'state'     => array(
				'hidden'   => true,
				'required' => false,
			),
			'postcode'  => array(
				'hidden'   => true,
				'required' => false,
			),
		);

		return $locale;
	}
}

<?php
/**
 * Entrega de la tienda local: envío en Lima Metropolitana y el Callao, y recojo en el taller.
 *
 * Se puede ejecutar varias veces. LOS DATOS SON PROVISIONALES (tarifa, dirección y horario, marcados
 * como tales en lo que ve el cliente): antes de abrir al público hay que cambiarlos por los reales
 * en WooCommerce > Ajustes > Envío.
 *
 * Uso: wp eval-file bin/seed-delivery.php --use-include --user=admin
 *
 * @package Camisama
 */

declare( strict_types=1 );

if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
	WP_CLI::error( 'WooCommerce no está activo.' );
}

// La entrega se elige primero; la facturación es opcional (regalos).
update_option( 'woocommerce_ship_to_destination', 'shipping' );

// Sin cupones todavía: "Añadir cupones" haría buscar uno que no existe.
update_option( 'woocommerce_enable_coupons', 'no' );

// Teléfono obligatorio: hace falta para coordinar la entrega.
update_option( 'woocommerce_checkout_phone_field', 'required' );

// Zona de entrega con su tarifa.
$camisama_zone_name = 'Lima Metropolitana y Callao';
$camisama_zone      = null;
foreach ( WC_Shipping_Zones::get_zones() as $camisama_existing ) {
	if ( in_array( $camisama_existing['zone_name'], array( $camisama_zone_name, 'Lima Metropolitana' ), true ) ) {
		$camisama_zone = new WC_Shipping_Zone( $camisama_existing['id'] );
	}
}
if ( null === $camisama_zone ) {
	$camisama_zone = new WC_Shipping_Zone();
}
$camisama_zone->set_zone_name( $camisama_zone_name );
$camisama_zone->save();
// Todo Perú: el envío se retira al elegir un distrito fuera de la zona (Checkout\Delivery). Con una zona
// por región, el pago empezaría en "Recojo" hasta elegir distrito, porque aún no habría región.
$camisama_zone->clear_locations();
$camisama_zone->add_location( 'PE', 'country' );
$camisama_zone->save();

$camisama_method_id = null;
foreach ( $camisama_zone->get_shipping_methods() as $camisama_method ) {
	if ( 'flat_rate' === $camisama_method->id ) {
		$camisama_method_id = $camisama_method->instance_id;
	}
}
if ( null === $camisama_method_id ) {
	$camisama_method_id = $camisama_zone->add_shipping_method( 'flat_rate' );
}
update_option(
	"woocommerce_flat_rate_{$camisama_method_id}_settings",
	array(
		'title'      => 'Envío a domicilio (tarifa provisional)',
		'tax_status' => 'none',
		'cost'       => '10',
	)
);

// Recojo en el taller (selector de entrega del pago de WooCommerce).
update_option(
	'woocommerce_pickup_location_settings',
	array(
		'enabled'    => 'yes',
		'title'      => 'Recojo en el taller',
		'tax_status' => 'none',
		'cost'       => '',
	)
);
update_option(
	'pickup_location_pickup_locations',
	array(
		array(
			'name'    => 'Taller Camisama (dirección provisional)',
			'address' => array(
				'address_1' => 'Dirección por confirmar',
				'city'      => 'Lima',
				'state'     => 'LMA',
				'postcode'  => '',
				'country'   => 'PE',
			),
			'details' => 'Horario por confirmar. Te avisamos cuando tu pedido esté listo.',
			'enabled' => true,
		),
	)
);

// Rótulos del selector de entrega en la página de pago.
$camisama_checkout = get_post( wc_get_page_id( 'checkout' ) );
if ( $camisama_checkout instanceof WP_Post ) {
	$camisama_labels = '{"title":"¿Cómo lo recibes?","localPickupText":"Recojo en el taller","shippingText":"Envío en Lima y Callao"}';
	$camisama_data   = 'data-title="¿Cómo lo recibes?" data-local-pickup-text="Recojo en el taller" data-shipping-text="Envío en Lima y Callao"';
	$camisama_new    = (string) preg_replace(
		array(
			'#<!-- wp:woocommerce/checkout-shipping-method-block( \{.*?\})? -->#',
			'#<div class="wp-block-woocommerce-checkout-shipping-method-block"[^>]*></div>#',
		),
		array(
			"<!-- wp:woocommerce/checkout-shipping-method-block {$camisama_labels} -->",
			"<div class=\"wp-block-woocommerce-checkout-shipping-method-block\" {$camisama_data}></div>",
		),
		$camisama_checkout->post_content
	);
	if ( $camisama_new !== $camisama_checkout->post_content ) {
		wp_update_post(
			array(
				'ID'           => $camisama_checkout->ID,
				'post_content' => wp_slash( $camisama_new ),
			)
		);
	}
}

WC_Cache_Helper::get_transient_version( 'shipping', true );

WP_CLI::success( 'Entrega configurada con datos provisionales.' );

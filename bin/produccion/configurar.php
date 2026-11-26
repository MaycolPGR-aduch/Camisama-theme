<?php
/**
 * Ajustes de la tienda en el servidor: lo mismo que bin/setup-local.ps1 deja en local, sin las
 * opciones de depuración ni los datos de prueba. Lo llama bin/produccion/configurar.sh.
 *
 * Uso: wp eval-file bin/produccion/configurar.php --user=<administrador>
 *
 * @package Camisama
 */

declare( strict_types=1 );

if ( ! defined( 'WP_CLI' ) || ! class_exists( 'WooCommerce' ) ) {
	return;
}

// Perú, soles con el formato "S/ 1,234.50" y tienda en Lima. La región sale del distrito
// (Checkout\Delivery), no de la tienda.
$camisama_options = array(
	'woocommerce_default_country'            => 'PE:LMA',
	'woocommerce_currency'                   => 'PEN',
	'woocommerce_currency_pos'               => 'left_space',
	'woocommerce_price_thousand_sep'         => ',',
	'woocommerce_price_decimal_sep'          => '.',
	'woocommerce_price_num_decimals'         => '2',
	'woocommerce_allowed_countries'          => 'specific',
	'woocommerce_ship_to_countries'          => 'specific',
	'woocommerce_specific_allowed_countries' => array( 'PE' ),
	'woocommerce_specific_ship_to_countries' => array( 'PE' ),
	'woocommerce_weight_unit'                => 'kg',
	'woocommerce_dimension_unit'             => 'cm',
	// Sin valoraciones mientras no haya clientes que puedan dejarlas.
	'woocommerce_enable_reviews'             => 'no',
	'woocommerce_onboarding_profile'         => array( 'skipped' => true ),
	'woocommerce_permalinks'                 => array(
		'product_base'           => '/producto',
		'category_base'          => 'categoria-producto',
		'tag_base'               => 'etiqueta-producto',
		'attribute_base'         => '',
		'use_verbose_page_rules' => false,
	),
	// Transferencia mientras no haya pasarela; sus textos los pone Checkout\PaymentTimes. Los datos
	// de la cuenta se ponen a mano en WooCommerce → Ajustes → Pagos.
	'woocommerce_bacs_settings'              => array_merge(
		(array) get_option( 'woocommerce_bacs_settings', array() ),
		array(
			'enabled' => 'yes',
			'title'   => 'Transferencia bancaria',
		)
	),
);
foreach ( $camisama_options as $camisama_name => $camisama_value ) {
	update_option( $camisama_name, $camisama_value );
}

// El contenido de ejemplo de WordPress ("¡Hola, mundo!", "Página de ejemplo"), si nadie lo tocó.
foreach ( array(
	'post' => 'hello-world',
	'page' => 'sample-page',
) as $camisama_type => $camisama_slug ) {
	$camisama_sample = get_page_by_path( $camisama_slug, OBJECT, $camisama_type );
	if ( $camisama_sample instanceof WP_Post && $camisama_sample->post_modified_gmt === $camisama_sample->post_date_gmt ) {
		wp_delete_post( $camisama_sample->ID, true );
		WP_CLI::log( "  borrado el contenido de ejemplo: {$camisama_slug}" );
	}
}

// "Próximamente" hasta abrir: solo se ve con sesión iniciada. Se abre en WooCommerce → Ajustes →
// Visibilidad del sitio. Si ya estaba abierta, no se cierra.
if ( false === get_option( 'camisama_configured_at', false ) ) {
	update_option( 'woocommerce_coming_soon', 'yes' );
	update_option( 'woocommerce_store_pages_only', 'no' );
}
update_option( 'camisama_configured_at', gmdate( 'c' ), false );

WP_CLI::success( 'Tienda configurada para Perú. Visibilidad: ' . ( 'yes' === get_option( 'woocommerce_coming_soon' ) ? 'Próximamente.' : 'abierta.' ) );

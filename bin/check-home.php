<?php
/**
 * Comprobaciones de integración de la portada, sin modificar la base de datos.
 *
 * Uso: php vendor/wp-cli/wp-cli/php/boot-fs.php eval-file bin/check-home.php --use-include
 *
 * @package Camisama
 */

declare( strict_types=1 );

if ( ! defined( 'WP_CLI' ) || ! function_exists( 'camisama_home_price_example' ) ) {
	throw new RuntimeException( 'Ejecuta con WP-CLI y el tema Camisama activo.' );
}

global $camisama_checks;
$camisama_checks = 0;

/**
 * Comprueba una condición sin depender de assert ni de su configuración en PHP.
 *
 * @param bool   $condition Resultado esperado.
 * @param string $message Explicación si falla.
 * @throws RuntimeException Si una comprobación falla.
 */
function camisama_home_check( bool $condition, string $message ): void {
	global $camisama_checks;
	if ( ! $condition ) {
		throw new RuntimeException( esc_html( $message ) );
	}
	++$camisama_checks;
}

$camisama_example = camisama_home_price_example();
camisama_home_check( null !== $camisama_example, 'Necesita al menos una prenda personalizable y comprable.' );
$camisama_variant = wc_get_product( $camisama_example['variant_id'] );
camisama_home_check( $camisama_variant && $camisama_variant->is_purchasable() && $camisama_variant->is_in_stock(), 'El ejemplo debe corresponder a una variante comprable.' );
camisama_home_check( abs( $camisama_example['base'] + $camisama_example['print'] - $camisama_example['total'] ) < 0.001, 'El desglose no suma el total.' );
camisama_home_check( str_contains( wp_strip_all_tags( camisama_render_home_price() ), wp_strip_all_tags( wc_price( $camisama_example['total'] ) ) ), 'El bloque no muestra el precio calculado.' );

// Cambia solo la lectura de las tarifas durante este proceso: no guarda opciones.
$camisama_rows = ( new Camisama\Customizer\Pricing\PriceTableRepository() )->get()->to_array();
foreach ( $camisama_rows as &$camisama_row ) {
	$camisama_row['amount'] += 1234;
}
unset( $camisama_row );
$camisama_rates_filter = static fn () => $camisama_rows;
add_filter( 'pre_option_camisama_customizer_price_tiers', $camisama_rates_filter );
try {
	$camisama_changed = camisama_home_price_example();
	$camisama_delta   = wc_get_price_to_display( $camisama_variant, array( 'price' => (float) $camisama_variant->get_price() + 12.34 ) ) - wc_get_price_to_display( $camisama_variant );
	camisama_home_check( null !== $camisama_changed && abs( $camisama_changed['total'] - $camisama_example['total'] - $camisama_delta ) < 0.02, 'El ejemplo no reacciona a las tarifas actuales.' );
} finally {
	remove_filter( 'pre_option_camisama_customizer_price_tiers', $camisama_rates_filter );
}

// Catálogo vacío: no debe inventar importes ni romper la portada.
$camisama_empty_filter = static function ( array $query ): array {
	$query['include'] = array( PHP_INT_MAX );
	return $query;
};
add_filter( 'woocommerce_product_object_query_args', $camisama_empty_filter );
try {
	camisama_home_check( null === camisama_home_price_example(), 'El catálogo vacío debe devolver una alternativa sin precios.' );
	$camisama_empty_html = camisama_render_home_price();
	camisama_home_check( ! str_contains( $camisama_empty_html, 'woocommerce-Price-amount' ), 'El catálogo vacío no debe mostrar un precio ficticio.' );
} finally {
	remove_filter( 'woocommerce_product_object_query_args', $camisama_empty_filter );
}

camisama_home_check( WP_Block_Type_Registry::get_instance()->is_registered( 'camisama/home-price' ), 'El bloque dinámico no está registrado.' );
echo esc_html( "OK: {$camisama_checks} comprobaciones de portada, sin escrituras en la base de datos.\n" );

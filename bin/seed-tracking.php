<?php
/**
 * Página "Sigue tu pedido": el formulario de seguimiento de WooCommerce (número de pedido y correo),
 * para quien compró sin cuenta. Los textos y la explicación de cada estado los pone el plugin
 * (Checkout\OrderTracking).
 *
 * Si la página ya está publicada no se toca.
 *
 * Uso: wp eval-file bin/seed-tracking.php --use-include --user=admin
 *
 * @package Camisama
 */

declare( strict_types=1 );

use Camisama\Customizer\Checkout\OrderTracking;

$camisama_slug  = class_exists( OrderTracking::class ) ? OrderTracking::SLUG : 'sigue-tu-pedido';
$camisama_found = get_page_by_path( $camisama_slug );

if ( $camisama_found instanceof WP_Post && 'publish' === $camisama_found->post_status ) {
	WP_CLI::log( '«Sigue tu pedido» ya está publicada: no se toca.' );
	return;
}

$camisama_id = wp_insert_post(
	array(
		'ID'           => $camisama_found instanceof WP_Post ? $camisama_found->ID : 0,
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Sigue tu pedido',
		'post_name'    => $camisama_slug,
		'post_content' => "<!-- wp:shortcode -->\n[woocommerce_order_tracking]\n<!-- /wp:shortcode -->",
	),
	true
);
if ( is_wp_error( $camisama_id ) ) {
	WP_CLI::error( $camisama_id );
}

WP_CLI::success( '«Sigue tu pedido» publicada.' );

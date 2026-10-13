<?php
/**
 * Funciones del tema Camisama.
 *
 * @package Camisama
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Soportes del tema.
 */
function camisama_setup(): void {
	add_theme_support( 'woocommerce' );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'camisama_setup' );

/**
 * Hoja de estilos del tema (los temas de bloques no la cargan automáticamente).
 */
function camisama_enqueue_styles(): void {
	wp_enqueue_style(
		'camisama-style',
		get_stylesheet_uri(),
		array(),
		(string) wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'camisama_enqueue_styles' );

/**
 * Variaciones de estilo de bloque del tema (el CSS está en style.css).
 */
function camisama_register_block_styles(): void {
	$styles = array(
		'camisama-plain'  => __( 'Sin viñetas', 'camisama' ),
		'camisama-checks' => __( 'Con checks', 'camisama' ),
		'camisama-zones'  => __( 'Zonas de impresión', 'camisama' ),
	);

	foreach ( $styles as $name => $label ) {
		register_block_style(
			'core/list',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', 'camisama_register_block_styles' );

/**
 * URL de una página de WooCommerce (shop, cart, checkout, myaccount, refund_returns) para los patrones.
 *
 * Si WooCommerce no está activo o la página no existe, devuelve la portada.
 *
 * @param string $page Identificador de la página en WooCommerce.
 */
function camisama_wc_page_url( string $page ): string {
	if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( $page ) > 0 ) {
		return (string) get_permalink( wc_get_page_id( $page ) );
	}

	return home_url( '/' );
}

/**
 * URL de una categoría de producto por su slug, o de la tienda si no existe.
 *
 * @param string $slug Slug de la categoría.
 */
function camisama_product_category_url( string $slug ): string {
	$link = get_term_link( $slug, 'product_cat' );

	return is_wp_error( $link ) ? camisama_wc_page_url( 'shop' ) : $link;
}

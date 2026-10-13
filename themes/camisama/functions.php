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

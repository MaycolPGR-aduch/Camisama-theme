<?php
/**
 * Bootstrap de PHPUnit: autoload de las herramientas (raíz) y del plugin.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

require_once dirname( __DIR__, 3 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Sin WordPress: las traducciones devuelven el texto tal cual.
if ( ! function_exists( '__' ) ) {
	/**
	 * Sustituto de la función de traducción de WordPress.
	 *
	 * @param string $text   Texto.
	 * @param string $domain Dominio de traducción.
	 * @return string
	 */
	function __( string $text, string $domain = 'default' ): string { // phpcs:ignore
		unset( $domain );
		return $text;
	}
}

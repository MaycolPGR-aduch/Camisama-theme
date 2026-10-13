<?php
/**
 * Plugin Name:       Camisama Customizer
 * Plugin URI:        https://github.com/MaycolPGR-aduch/Camisama-theme
 * Description:       Personalizador de prendas para WooCommerce: modelos, zonas de impresión, estampados del catálogo, texto e imágenes propias.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      8.2
 * Requires Plugins:  woocommerce
 * Author:            Camisama
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       camisama-customizer
 * Domain Path:       /languages
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer;

defined( 'ABSPATH' ) || exit;

if ( ! is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'Camisama Customizer: faltan las dependencias. Ejecuta "composer install" en la raíz del repositorio.', 'camisama-customizer' )
			);
		}
	);
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

Plugin::boot( __FILE__ );

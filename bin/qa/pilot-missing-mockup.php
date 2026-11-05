<?php
/**
 * Simula una maqueta ausente solo en el piloto local; el importador lo restaura.
 *
 * Uso: php vendor/wp-cli/wp-cli/php/boot-fs.php eval-file bin/qa/pilot-missing-mockup.php --use-include --user=admin
 * Restaurar: ejecutar bin/import-customizer-pilot.php con el mismo comando.
 *
 * @package Camisama
 */

declare( strict_types=1 );

use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Config\ProductConfigRepository;

if ( ! defined( 'WP_CLI' ) || ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.test' ) || ! current_user_can( 'manage_woocommerce' ) ) {
	return;
}
$camisama_pilot = wc_get_product( wc_get_product_id_by_sku( 'CAM-CLASICA-PILOTO-V1' ) );
if ( ! $camisama_pilot || 'v1' !== $camisama_pilot->get_meta( '_camisama_visual_pilot' ) ) {
	WP_CLI::error( 'Falta el piloto local.' );
}
$camisama_repo   = new ProductConfigRepository();
$camisama_config = $camisama_repo->get( $camisama_pilot->get_id() )->to_array();
foreach ( $camisama_config['views'] as &$camisama_view ) {
	if ( 'front' === $camisama_view['id'] ) {
		unset( $camisama_view['mockups']['negro'] );
	}
}
unset( $camisama_view );
$camisama_repo->save( $camisama_pilot->get_id(), ProductConfig::from_array( $camisama_config ) );
WP_CLI::success( 'Frente negro ausente SOLO en el piloto. Restaurar ejecutando el importador.' );

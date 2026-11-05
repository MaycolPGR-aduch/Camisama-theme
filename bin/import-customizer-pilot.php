<?php
/**
 * Importa un piloto local oculto sin modificar CAM-CLASICA.
 *
 * Uso: php vendor/wp-cli/wp-cli/php/boot-fs.php eval-file bin/import-customizer-pilot.php --use-include --user=admin
 *
 * @package Camisama
 */

declare( strict_types=1 );

use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Config\ProductConfigRepository;

if ( ! defined( 'WP_CLI' ) || ! class_exists( 'WooCommerce' ) ) {
	return;
}
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.test' ) || ! current_user_can( 'manage_woocommerce' ) ) {
	WP_CLI::error( 'Solo se permite importar el piloto en un sitio local .test con un administrador.' );
}

require_once ABSPATH . 'wp-admin/includes/image.php';
$camisama_root     = dirname( __DIR__ ) . '/assets/customizer/clasica/v1/';
$camisama_manifest = json_decode( (string) file_get_contents( $camisama_root . 'manifest.json' ), true, 512, JSON_THROW_ON_ERROR ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Archivo local versionado, no URL.
$camisama_source   = wc_get_product( wc_get_product_id_by_sku( $camisama_manifest['source_sku'] ) );
if ( ! $camisama_source instanceof WC_Product_Variable ) {
	WP_CLI::error( 'Falta el modelo clásico variable de referencia.' );
}
$camisama_pilot = wc_get_product( wc_get_product_id_by_sku( $camisama_manifest['pilot_sku'] ) );
if ( $camisama_pilot && ( ! $camisama_pilot instanceof WC_Product_Variable || 'v1' !== $camisama_pilot->get_meta( '_camisama_visual_pilot' ) ) ) {
	WP_CLI::error( 'El SKU del piloto ya pertenece a otro producto.' );
}
$camisama_color_ids = array();
foreach ( array( 'blanco', 'negro' ) as $camisama_color_slug ) {
	$camisama_term = get_term_by( 'slug', $camisama_color_slug, 'pa_color' );
	if ( ! $camisama_term instanceof WP_Term ) {
		WP_CLI::error( 'Faltan los atributos de color del modelo clásico.' );
	}
	$camisama_color_ids[] = $camisama_term->term_id;
}
if ( ! isset( $camisama_source->get_attributes()['pa_color'], $camisama_source->get_attributes()['pa_talla'] ) ) {
	WP_CLI::error( 'La clásica debe tener atributos globales de color y talla.' );
}

// Validar todos los archivos y zonas antes de escribir en WordPress.
foreach ( $camisama_manifest['images'] as $camisama_image ) {
	if ( hash_file( 'sha256', $camisama_root . $camisama_image['file'] ) !== $camisama_image['sha256'] ) {
		WP_CLI::error( 'La huella de una imagen no coincide con el manifiesto.' );
	}
}
ProductConfig::from_array(
	array(
		'enabled' => true,
		'views'   => array(
			array(
				'id'      => 'front',
				'label'   => 'Frente',
				'mockups' => array(),
			),
			array(
				'id'      => 'back',
				'label'   => 'Espalda',
				'mockups' => array(),
			),
		),
		'zones'   => $camisama_manifest['zones'],
	)
);
$camisama_backup_key = '_camisama_pilot_v1_source_backup';
if ( false === get_option( $camisama_backup_key, false ) ) {
	add_option(
		$camisama_backup_key,
		array(
			'product_id'  => $camisama_source->get_id(),
			'config'      => get_post_meta( $camisama_source->get_id(), ProductConfigRepository::META_KEY, true ),
			'recorded_at' => gmdate( 'c' ),
		),
		'',
		false
	);
}

$camisama_mockups = array();
foreach ( $camisama_manifest['images'] as $camisama_image ) {
	$camisama_key   = 'clasica-v1-' . $camisama_image['view'] . '-' . $camisama_image['color'];
	$camisama_found = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_key'       => '_camisama_pilot_asset', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Importación CLI de cuatro adjuntos, fuera del frontend.
			'meta_value'     => $camisama_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	$camisama_id    = (int) ( $camisama_found[0] ?? 0 );
	if ( $camisama_id && ( get_post_meta( $camisama_id, '_camisama_pilot_sha256', true ) !== $camisama_image['sha256'] || ! is_file( get_attached_file( $camisama_id ) ) ) ) {
		WP_CLI::error( 'Un adjunto del piloto cambió o falta. Revisa los medios antes de reimportar.' );
	}
	if ( ! $camisama_id ) {
		$camisama_upload = wp_upload_bits( $camisama_image['file'], null, (string) file_get_contents( $camisama_root . $camisama_image['file'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Imagen local verificada por SHA-256.
		if ( $camisama_upload['error'] ) {
			WP_CLI::error( $camisama_upload['error'] );
		}
		$camisama_id = wp_insert_attachment(
			array(
				'post_mime_type' => 'image/webp',
				'post_title'     => 'Piloto clásica ' . $camisama_image['color'] . ' ' . $camisama_image['view'],
				'post_status'    => 'inherit',
			),
			$camisama_upload['file'],
			0,
			true
		);
		if ( is_wp_error( $camisama_id ) ) {
			WP_CLI::error( $camisama_id->get_error_message() );
		}
		update_post_meta( $camisama_id, '_camisama_pilot_asset', $camisama_key );
		update_post_meta( $camisama_id, '_camisama_pilot_sha256', $camisama_image['sha256'] );
		update_post_meta( $camisama_id, '_wp_attachment_image_alt', 'Camiseta clásica ' . $camisama_image['color'] . ', vista ' . $camisama_image['view'] );
		wp_update_attachment_metadata( $camisama_id, wp_generate_attachment_metadata( $camisama_id, $camisama_upload['file'] ) );
	}
	$camisama_mockups[ $camisama_image['view'] ][ $camisama_image['color'] ] = $camisama_id;
}

if ( ! $camisama_pilot ) {
	$camisama_pilot = new WC_Product_Variable();
	$camisama_pilot->set_sku( $camisama_manifest['pilot_sku'] );
}
$camisama_attributes             = $camisama_source->get_attributes();
$camisama_attributes['pa_color'] = clone $camisama_attributes['pa_color'];
$camisama_attributes['pa_color']->set_options( $camisama_color_ids );
$camisama_pilot->set_name( 'Camiseta clásica — piloto visual' );
$camisama_pilot->set_status( 'publish' );
$camisama_pilot->set_catalog_visibility( 'hidden' );
$camisama_pilot->set_tax_status( $camisama_source->get_tax_status() );
$camisama_pilot->set_tax_class( $camisama_source->get_tax_class() );
$camisama_pilot->set_short_description( '<strong>Piloto local de personalización.</strong> Imágenes y áreas de referencia: las medidas aún no están confirmadas por el taller.' );
$camisama_pilot->set_description( 'Prueba de plantillas fotográficas en blanco y negro. La vista previa muestra la composición y no certifica el resultado de fabricación. Pendiente: recorte final del blanco y validación física de las cinco zonas.' );
$camisama_pilot->set_attributes( array_values( $camisama_attributes ) );
$camisama_pilot->set_default_attributes(
	array(
		'pa_color' => 'blanco',
		'pa_talla' => 'm',
	)
);
$camisama_pilot->set_image_id( $camisama_mockups['front']['blanco'] );
$camisama_pilot->set_gallery_image_ids( array( $camisama_mockups['front']['negro'], $camisama_mockups['back']['blanco'], $camisama_mockups['back']['negro'] ) );
$camisama_pilot->update_meta_data( '_camisama_visual_pilot', 'v1' );
$camisama_pilot_id = $camisama_pilot->save();

foreach ( $camisama_source->get_children() as $camisama_child_id ) {
	$camisama_child = wc_get_product( $camisama_child_id );
	$camisama_color = $camisama_child->get_attributes()['pa_color'] ?? '';
	if ( ! isset( $camisama_mockups['front'][ $camisama_color ] ) ) {
		continue;
	}
	$camisama_sku       = $camisama_manifest['pilot_sku'] . '-' . $camisama_child_id;
	$camisama_variation = wc_get_product( wc_get_product_id_by_sku( $camisama_sku ) );
	if ( $camisama_variation && ( ! $camisama_variation instanceof WC_Product_Variation || $camisama_variation->get_parent_id() !== $camisama_pilot_id ) ) {
		WP_CLI::error( 'Un SKU de variante del piloto pertenece a otro producto.' );
	}
	if ( ! $camisama_variation ) {
		$camisama_variation = new WC_Product_Variation();
		$camisama_variation->set_sku( $camisama_sku );
	}
	$camisama_variation->set_parent_id( $camisama_pilot_id );
	$camisama_variation->set_attributes( $camisama_child->get_attributes() );
	$camisama_variation->set_regular_price( $camisama_child->get_regular_price() );
	$camisama_variation->set_sale_price( $camisama_child->get_sale_price() );
	$camisama_variation->set_date_on_sale_from( $camisama_child->get_date_on_sale_from() );
	$camisama_variation->set_date_on_sale_to( $camisama_child->get_date_on_sale_to() );
	$camisama_variation->set_tax_class( $camisama_child->get_tax_class() );
	$camisama_variation->set_status( $camisama_child->get_status() );
	$camisama_variation->set_stock_status( $camisama_child->get_stock_status() );
	$camisama_variation->set_image_id( $camisama_mockups['front'][ $camisama_color ] );
	$camisama_variation->save();
}
$camisama_config = ProductConfig::from_array(
	array(
		'enabled' => true,
		'views'   => array(
			array(
				'id'      => 'front',
				'label'   => 'Frente',
				'mockups' => $camisama_mockups['front'],
			),
			array(
				'id'      => 'back',
				'label'   => 'Espalda',
				'mockups' => $camisama_mockups['back'],
			),
		),
		'zones'   => $camisama_manifest['zones'],
	)
);
( new ProductConfigRepository() )->save( $camisama_pilot_id, $camisama_config );
WC_Product_Variable::sync( $camisama_pilot_id );
WP_CLI::success( 'Piloto oculto listo: ' . get_permalink( $camisama_pilot_id ) );
WP_CLI::log(
	(string) wp_json_encode(
		array(
			'product_id'       => $camisama_pilot_id,
			'mockups'          => $camisama_mockups,
			'source_unchanged' => get_post_meta( $camisama_source->get_id(), ProductConfigRepository::META_KEY, true ) === get_option( $camisama_backup_key )['config'],
		)
	)
);

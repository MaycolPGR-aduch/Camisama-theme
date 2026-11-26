<?php
/**
 * Pone las plantillas fotográficas (assets/customizer/modelos/v1) en los 4 modelos de la tienda local.
 *
 * Uso (desde la raíz del repositorio):
 *   php vendor/wp-cli/wp-cli/php/boot-fs.php eval-file bin/import-fotos-modelos.php --use-include --user=admin
 *   ... eval-file bin/import-fotos-modelos.php restaurar --use-include --user=admin
 *
 * Fuera del sitio local (.test) hay que añadir "confirmar": lo hace bin/produccion/configurar.sh.
 *
 * Cambia las maquetas y las zonas del personalizador y las imágenes del producto y sus variaciones.
 * Antes guarda una copia de lo que había; "restaurar" la devuelve. No toca precios, tallas, colores ni
 * pedidos. Se puede ejecutar varias veces: reutiliza los adjuntos y solo importa los que cambian.
 *
 * Las imágenes se preparan con bin/plantillas/preparar.py.
 *
 * @package Camisama
 */

declare( strict_types=1 );

use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Config\ProductConfigRepository;

if ( ! defined( 'WP_CLI' ) || ! class_exists( 'WooCommerce' ) ) {
	return;
}
if ( ! current_user_can( 'manage_woocommerce' ) ) {
	WP_CLI::error( 'Ejecútalo con un administrador (--user=…).' );
}
if ( ! str_ends_with( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.test' ) && ! in_array( 'confirmar', $args, true ) ) {
	WP_CLI::error( 'Esto no es el sitio local: si es lo que quieres, añade "confirmar".' );
}

require_once ABSPATH . 'wp-admin/includes/image.php';

/** Copia de lo que había antes, por SKU. */
const CAMISAMA_PHOTOS_BACKUP = '_camisama_fotos_v1_copia';

/**
 * Producto variable por SKU.
 *
 * @param string $sku SKU.
 */
function camisama_photos_product( string $sku ): WC_Product_Variable {
	$product = wc_get_product( wc_get_product_id_by_sku( $sku ) );
	if ( ! $product instanceof WC_Product_Variable ) {
		WP_CLI::error( "Falta el producto variable {$sku}." );
	}

	return $product;
}

/**
 * Importa una imagen, o reutiliza su adjunto si no ha cambiado.
 *
 * @param string               $root  Carpeta de las imágenes.
 * @param array<string, mixed> $file  Archivo y huella del manifiesto.
 * @param string               $title Título y texto alternativo.
 */
function camisama_photos_attachment( string $root, array $file, string $title ): int {
	$found = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_key'       => '_camisama_photo_sha256', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Importación CLI, fuera de la tienda.
			'meta_value'     => $file['sha256'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	if ( $found && is_file( (string) get_attached_file( (int) $found[0] ) ) ) {
		return (int) $found[0];
	}

	$name   = str_replace( '/', '-', $file['file'] );
	$upload = wp_upload_bits( $name, null, (string) file_get_contents( $root . $file['file'] ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Imagen local verificada por SHA-256.
	if ( $upload['error'] ) {
		WP_CLI::error( $upload['error'] );
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/webp',
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$upload['file'],
		0,
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_camisama_photo_sha256', $file['sha256'] );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

	return $id;
}

/**
 * Imágenes y configuración actuales de un producto, para poder volver.
 *
 * @param WC_Product_Variable $product Producto.
 * @return array<string, mixed>
 */
function camisama_photos_state( WC_Product_Variable $product ): array {
	$variations = array();
	foreach ( $product->get_children() as $child ) {
		$variations[ $child ] = (int) wc_get_product( $child )->get_image_id( 'edit' );
	}

	return array(
		'config'     => (string) get_post_meta( $product->get_id(), ProductConfigRepository::META_KEY, true ),
		'image'      => (int) $product->get_image_id( 'edit' ),
		'gallery'    => $product->get_gallery_image_ids( 'edit' ),
		'variations' => $variations,
		'saved_at'   => gmdate( 'c' ),
	);
}

$camisama_root     = dirname( __DIR__ ) . '/assets/customizer/modelos/v1/';
$camisama_manifest = json_decode( (string) file_get_contents( $camisama_root . 'manifest.json' ), true, 512, JSON_THROW_ON_ERROR ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Archivo local versionado, no URL.
$camisama_backup   = get_option( CAMISAMA_PHOTOS_BACKUP, array() );
$camisama_configs  = new ProductConfigRepository();

if ( in_array( 'restaurar', $args, true ) ) {
	if ( array() === $camisama_backup ) {
		WP_CLI::error( 'No hay ninguna copia que restaurar.' );
	}
	foreach ( $camisama_backup as $camisama_sku => $camisama_state ) {
		$camisama_product = camisama_photos_product( $camisama_sku );
		update_post_meta( $camisama_product->get_id(), ProductConfigRepository::META_KEY, wp_slash( $camisama_state['config'] ) );
		$camisama_product->set_image_id( $camisama_state['image'] );
		$camisama_product->set_gallery_image_ids( $camisama_state['gallery'] );
		$camisama_product->save();
		foreach ( $camisama_state['variations'] as $camisama_child => $camisama_image ) {
			$camisama_variation = wc_get_product( $camisama_child );
			if ( $camisama_variation instanceof WC_Product_Variation ) {
				$camisama_variation->set_image_id( $camisama_image );
				$camisama_variation->save();
			}
		}
		WP_CLI::log( "  restaurado: {$camisama_product->get_name()}" );
	}
	delete_option( CAMISAMA_PHOTOS_BACKUP );
	WP_CLI::success( 'La tienda vuelve a las imágenes y zonas anteriores.' );
	return;
}

// Todo se comprueba antes de escribir nada: huellas, productos, colores y zonas.
foreach ( $camisama_manifest['models'] as $camisama_model ) {
	$camisama_product = camisama_photos_product( $camisama_model['sku'] );
	$camisama_colors  = array_map( 'strval', wc_get_product_terms( $camisama_product->get_id(), 'pa_color', array( 'fields' => 'slugs' ) ) );
	$camisama_have    = array();
	foreach ( $camisama_model['images'] as $camisama_image ) {
		$camisama_have[ $camisama_image['color'] ][ $camisama_image['view'] ] = true;
		foreach ( array( 'mockup', 'photo' ) as $camisama_kind ) {
			$camisama_file = $camisama_root . $camisama_image[ $camisama_kind ]['file'];
			if ( ! is_file( $camisama_file ) || hash_file( 'sha256', $camisama_file ) !== $camisama_image[ $camisama_kind ]['sha256'] ) {
				WP_CLI::error( "{$camisama_image[ $camisama_kind ]['file']} falta o no coincide con el manifiesto." );
			}
		}
	}
	foreach ( $camisama_colors as $camisama_color ) {
		if ( ! isset( $camisama_have[ $camisama_color ]['front'], $camisama_have[ $camisama_color ]['back'] ) ) {
			WP_CLI::error( "{$camisama_model['sku']}: faltan plantillas del color {$camisama_color}." );
		}
	}
	// Los identificadores y los centímetros no cambian: los diseños guardados se apoyan en ellos.
	$camisama_sizes = static fn ( array $zones ): array => array_column(
		array_map( static fn ( array $zone ): array => array( $zone['id'], (float) $zone['size_cm']['w'] . 'x' . (float) $zone['size_cm']['h'] ), $zones ),
		1,
		0
	);
	$camisama_now   = $camisama_configs->get( $camisama_product->get_id() )->to_array();
	if ( $camisama_sizes( $camisama_now['zones'] ) != $camisama_sizes( $camisama_model['zones'] ) ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual -- El orden de las zonas da igual.
		WP_CLI::error( "{$camisama_model['sku']}: las zonas del manifiesto no son las del producto." );
	}
}

foreach ( $camisama_manifest['models'] as $camisama_slug => $camisama_model ) {
	$camisama_product = camisama_photos_product( $camisama_model['sku'] );
	if ( ! isset( $camisama_backup[ $camisama_model['sku'] ] ) ) {
		$camisama_backup[ $camisama_model['sku'] ] = camisama_photos_state( $camisama_product );
		update_option( CAMISAMA_PHOTOS_BACKUP, $camisama_backup, false );
	}

	$camisama_mockups = array();
	$camisama_photos  = array();
	foreach ( $camisama_model['images'] as $camisama_image ) {
		$camisama_term  = get_term_by( 'slug', $camisama_image['color'], 'pa_color' );
		$camisama_title = sprintf(
			'%1$s, %2$s, %3$s',
			$camisama_product->get_name(),
			strtolower( $camisama_term instanceof WP_Term ? $camisama_term->name : $camisama_image['color'] ),
			'front' === $camisama_image['view'] ? 'frente' : 'espalda'
		);

		$camisama_mockups[ $camisama_image['view'] ][ $camisama_image['color'] ] = camisama_photos_attachment( $camisama_root, $camisama_image['mockup'], $camisama_title );
		$camisama_photos[ $camisama_image['color'] ][ $camisama_image['view'] ]  = camisama_photos_attachment( $camisama_root, $camisama_image['photo'], $camisama_title );
	}

	$camisama_configs->save(
		$camisama_product->get_id(),
		ProductConfig::from_array(
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
				'zones'   => $camisama_model['zones'],
			)
		)
	);

	// La foto principal, del color por defecto; la galería, los demás colores y su espalda.
	$camisama_default = (string) ( $camisama_product->get_default_attributes()['pa_color'] ?? array_key_first( $camisama_photos ) );
	if ( ! isset( $camisama_photos[ $camisama_default ] ) ) {
		$camisama_default = (string) array_key_first( $camisama_photos );
	}
	$camisama_gallery = array( $camisama_photos[ $camisama_default ]['back'] );
	foreach ( $camisama_photos as $camisama_color => $camisama_views ) {
		if ( $camisama_color !== $camisama_default ) {
			$camisama_gallery[] = $camisama_views['front'];
		}
	}
	$camisama_product->set_image_id( $camisama_photos[ $camisama_default ]['front'] );
	$camisama_product->set_gallery_image_ids( $camisama_gallery );
	$camisama_product->save();

	foreach ( $camisama_product->get_children() as $camisama_child ) {
		$camisama_variation = wc_get_product( $camisama_child );
		$camisama_color     = $camisama_variation->get_attributes()['pa_color'] ?? '';
		if ( isset( $camisama_photos[ $camisama_color ] ) ) {
			$camisama_variation->set_image_id( $camisama_photos[ $camisama_color ]['front'] );
			$camisama_variation->save();
		}
	}
	WP_CLI::log( "  {$camisama_product->get_name()}: " . count( $camisama_photos ) . ' colores' );
}
WP_CLI::success( 'Plantillas fotográficas activas. Para volver: el mismo comando con "restaurar".' );

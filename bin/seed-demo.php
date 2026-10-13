<?php
/**
 * Productos de demostración para el sitio local: 4 modelos con colores, tallas e imágenes.
 *
 * Uso (desde la raíz del repositorio):
 *   php vendor/wp-cli/wp-cli/php/boot-fs.php eval-file bin/seed-demo.php --use-include
 *
 * Se puede ejecutar varias veces: los productos se identifican por SKU y las imágenes por clave.
 * Las imágenes son ilustraciones planas generadas con GD, a la espera de las fotos reales.
 *
 * @package Camisama
 */

declare( strict_types=1 );

if ( ! defined( 'WP_CLI' ) || ! class_exists( 'WooCommerce' ) ) {
	echo "Ejecuta este archivo con WP-CLI (eval-file) y con WooCommerce activo.\n";
	return;
}

require_once ABSPATH . 'wp-admin/includes/image.php';

const CAMISAMA_DEMO_COLORS = array(
	'blanco'      => array( 'Blanco', '#FAFAF7' ),
	'negro'       => array( 'Negro', '#1C1C21' ),
	'rojo'        => array( 'Rojo', '#C62A22' ),
	'azul-marino' => array( 'Azul marino', '#1F2A4D' ),
	'arena'       => array( 'Arena', '#D8C7A8' ),
	'gris'        => array( 'Gris jaspeado', '#9A9AA0' ),
	'mostaza'     => array( 'Mostaza', '#E3A913' ),
);

const CAMISAMA_DEMO_PRODUCTS = array(
	array(
		'sku'        => 'CAM-CLASICA',
		'name'       => 'Camiseta clásica',
		'shape'      => 'classic',
		'price'      => '39.90',
		'colors'     => array( 'blanco', 'negro', 'rojo', 'azul-marino' ),
		'sizes'      => array( 'S', 'M', 'L', 'XL', 'XXL' ),
		'categories' => array( 'camisetas' ),
		'short'      => 'Corte recto de algodón peinado 100 %, 180 g/m². La base perfecta para cualquier diseño.',
		'long'       => 'Nuestra camiseta de siempre: cuello redondo reforzado, costuras dobles y un algodón suave que mantiene el color lavado tras lavado. Personaliza el frente, la espalda, las mangas y la nuca.',
	),
	array(
		'sku'        => 'CAM-OVERSIZE',
		'name'       => 'Camiseta oversize',
		'shape'      => 'oversize',
		'price'      => '49.90',
		'colors'     => array( 'negro', 'blanco', 'arena' ),
		'sizes'      => array( 'S', 'M', 'L', 'XL' ),
		'categories' => array( 'camisetas' ),
		'short'      => 'Hombro caído y caída amplia, algodón 100 % de 220 g/m². Estilo urbano con mucho espacio para estampar.',
		'long'       => 'Tela más gruesa y estructurada, ideal para diseños grandes en la espalda. Corte holgado unisex: si prefieres un calce más ajustado, elige una talla menos.',
	),
	array(
		'sku'        => 'CAM-MANGA-LARGA',
		'name'       => 'Camiseta manga larga',
		'shape'      => 'long',
		'price'      => '54.90',
		'colors'     => array( 'blanco', 'negro', 'gris' ),
		'sizes'      => array( 'S', 'M', 'L', 'XL' ),
		'categories' => array( 'camisetas' ),
		'short'      => 'Manga larga con puño, algodón 100 % de 180 g/m². Para los días frescos sin renunciar a tu diseño.',
		'long'       => 'Las mangas largas permiten estampados verticales a lo largo del brazo, además del frente y la espalda.',
	),
	array(
		'sku'        => 'CAM-NINOS',
		'name'       => 'Camiseta para niños',
		'shape'      => 'kids',
		'price'      => '32.90',
		'colors'     => array( 'blanco', 'rojo', 'mostaza' ),
		'sizes'      => array( '4', '6', '8', '10', '12' ),
		'categories' => array( 'camisetas', 'ninos' ),
		'short'      => 'Algodón 100 % suave y resistente, en tallas de 4 a 12 años.',
		'long'       => 'Tintas DTF sin olor y aptas para prendas infantiles. Las zonas de impresión son proporcionales a la talla.',
	),
);

/** Recargo de las tallas grandes, en soles. */
const CAMISAMA_DEMO_XXL_SURCHARGE = 5.0;

/**
 * Contornos de las prendas en una cuadrícula de 48 × 48 (como el isotipo).
 * El primer y el segundo punto son los extremos del cuello; el resto, el contorno en sentido horario.
 *
 * @return array{neck: array{0: array{float, float}, 1: array{float, float}, 2: array{float, float}}, outline: list<array{float, float}>, scale: float}
 */
function camisama_demo_shape( string $shape ): array {
	$classic_neck = array( array( 18, 11 ), array( 24, 16 ), array( 30, 11 ) );

	return match ( $shape ) {
		'oversize' => array(
			'neck'    => array( array( 17.5, 11 ), array( 24, 15.5 ), array( 30.5, 11 ) ),
			'outline' => array( array( 39.5, 14.5 ), array( 44.5, 24.5 ), array( 37.5, 26.5 ), array( 38, 38 ), array( 10, 38 ), array( 10.5, 26.5 ), array( 3.5, 24.5 ), array( 8.5, 14.5 ) ),
			'scale'   => 1.0,
		),
		'long'     => array(
			'neck'    => $classic_neck,
			'outline' => array( array( 38, 14 ), array( 45.5, 35 ), array( 41, 36.5 ), array( 36, 22 ), array( 36, 37 ), array( 12, 37 ), array( 12, 22 ), array( 7, 36.5 ), array( 2.5, 35 ), array( 10, 14 ) ),
			'scale'   => 1.0,
		),
		default    => array(
			'neck'    => $classic_neck,
			'outline' => array( array( 38, 14 ), array( 42, 21 ), array( 36, 23.5 ), array( 36, 37 ), array( 12, 37 ), array( 12, 23.5 ), array( 6, 21 ), array( 10, 14 ) ),
			'scale'   => 'kids' === $shape ? 0.8 : 1.0,
		),
	};
}

/**
 * Asigna un color de GD a partir de un hexadecimal, opcionalmente oscurecido.
 *
 * @param \GdImage $image  Imagen.
 * @param string   $hex    Color #RRGGBB.
 * @param float    $darken Fracción a oscurecer (0–1).
 */
function camisama_demo_color( \GdImage $image, string $hex, float $darken = 0.0 ): int {
	$rgb = array_map( static fn ( string $c ): int => (int) round( hexdec( $c ) * ( 1 - $darken ) ), str_split( ltrim( $hex, '#' ), 2 ) );

	return (int) imagecolorallocate( $image, $rgb[0], $rgb[1], $rgb[2] );
}

/**
 * Dibuja la prenda y devuelve el PNG (800 × 800). Se dibuja al doble y se reduce para suavizar bordes.
 */
function camisama_demo_render( string $shape, string $hex ): string {
	$size   = 1600;
	$image  = imagecreatetruecolor( $size, $size );
	$spec   = camisama_demo_shape( $shape );
	$unit   = $size * 0.8 / 48 * $spec['scale'];
	$center = $size / 2;
	$map    = static fn ( array $p ): array => array( $center + ( $p[0] - 24 ) * $unit, $center + ( $p[1] - 25 ) * $unit );

	imagefill( $image, 0, 0, camisama_demo_color( $image, '#F1EDE6' ) );

	// Sombra en el suelo.
	$bottom = max( array_column( $spec['outline'], 1 ) );
	imagefilledellipse( $image, (int) $center, (int) $map( array( 24, $bottom + 2 ) )[1], (int) ( 30 * $unit ), (int) ( 2.4 * $unit ), camisama_demo_color( $image, '#E2DCD2' ) );

	// Cuello como curva cuadrática y luego el contorno.
	list( $start, $control, $end ) = $spec['neck'];
	$neck                          = array();
	for ( $t = 0.0; $t <= 1.0001; $t += 0.1 ) {
		$neck[] = array(
			( 1 - $t ) ** 2 * $start[0] + 2 * ( 1 - $t ) * $t * $control[0] + $t ** 2 * $end[0],
			( 1 - $t ) ** 2 * $start[1] + 2 * ( 1 - $t ) * $t * $control[1] + $t ** 2 * $end[1],
		);
	}

	$points = array();
	foreach ( array_merge( $neck, $spec['outline'] ) as $point ) {
		array_push( $points, ...array_map( 'intval', $map( $point ) ) );
	}

	imagefilledpolygon( $image, $points, camisama_demo_color( $image, $hex ) );
	imagesetthickness( $image, 8 );
	imagepolygon( $image, $points, camisama_demo_color( $image, $hex, 0.18 ) );

	// Ribete del cuello.
	imagesetthickness( $image, 26 );
	$rib = camisama_demo_color( $image, $hex, 0.12 );
	for ( $i = 1, $count = count( $neck ); $i < $count; $i++ ) {
		$a = $map( $neck[ $i - 1 ] );
		$b = $map( $neck[ $i ] );
		imageline( $image, (int) $a[0], (int) $a[1] + 10, (int) $b[0], (int) $b[1] + 10, $rib );
	}

	$output = imagecreatetruecolor( 800, 800 );
	imagecopyresampled( $output, $image, 0, 0, 0, 0, 800, 800, $size, $size );

	ob_start();
	imagepng( $output, null, 9 );

	return (string) ob_get_clean();
}

/**
 * Crea (o reutiliza) la imagen de una prenda en un color y devuelve el ID del adjunto.
 */
function camisama_demo_image( string $sku, string $shape, string $color ): int {
	$key      = strtolower( "{$sku}-{$color}" );
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'meta_key'       => '_camisama_demo_image', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}

	$upload = wp_upload_bits( "{$key}.png", null, camisama_demo_render( $shape, CAMISAMA_DEMO_COLORS[ $color ][1] ) );
	if ( $upload['error'] ) {
		WP_CLI::error( $upload['error'] );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/png',
			'post_title'     => $key,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_post_meta( $attachment_id, '_camisama_demo_image', $key );

	return $attachment_id;
}

/**
 * Crea el atributo global (pa_*) si no existe y devuelve los IDs de sus términos.
 *
 * @param string                $slug  Slug del atributo sin el prefijo pa_.
 * @param string                $label Nombre visible.
 * @param array<string, string> $terms slug => nombre.
 * @return array<string, int> slug => ID del término.
 */
function camisama_demo_attribute( string $slug, string $label, array $terms ): array {
	$taxonomy = wc_attribute_taxonomy_name( $slug );

	if ( ! wc_attribute_taxonomy_id_by_name( $taxonomy ) ) {
		wc_create_attribute(
			array(
				'name' => $label,
				'slug' => $slug,
				'type' => 'select',
			)
		);
	}
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy( $taxonomy, 'product', array( 'hierarchical' => false ) );
	}

	$ids = array();
	foreach ( $terms as $term_slug => $name ) {
		$term = term_exists( (string) $term_slug, $taxonomy );
		if ( ! $term ) {
			$term = wp_insert_term( $name, $taxonomy, array( 'slug' => (string) $term_slug ) );
		}
		$ids[ (string) $term_slug ] = (int) $term['term_id'];
	}

	return $ids;
}

/**
 * Crea la categoría de producto si no existe y devuelve su ID.
 */
function camisama_demo_category( string $slug, string $name ): int {
	$term = term_exists( $slug, 'product_cat' );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
	}

	return (int) $term['term_id'];
}

$camisama_categories = array(
	'camisetas' => camisama_demo_category( 'camisetas', 'Camisetas' ),
	'ninos'     => camisama_demo_category( 'ninos', 'Niños' ),
);

$camisama_all_sizes = array();
foreach ( CAMISAMA_DEMO_PRODUCTS as $camisama_spec ) {
	foreach ( $camisama_spec['sizes'] as $camisama_size ) {
		$camisama_all_sizes[ strtolower( $camisama_size ) ] = $camisama_size;
	}
}
$camisama_color_ids = camisama_demo_attribute( 'color', 'Color', array_map( static fn ( array $c ): string => $c[0], CAMISAMA_DEMO_COLORS ) );
$camisama_size_ids  = camisama_demo_attribute( 'talla', 'Talla', $camisama_all_sizes );

foreach ( CAMISAMA_DEMO_PRODUCTS as $camisama_order => $camisama_spec ) {
	if ( wc_get_product_id_by_sku( $camisama_spec['sku'] ) ) {
		WP_CLI::log( "Ya existe: {$camisama_spec['name']}" );
		continue;
	}

	$camisama_images = array();
	foreach ( $camisama_spec['colors'] as $camisama_color ) {
		$camisama_images[ $camisama_color ] = camisama_demo_image( $camisama_spec['sku'], $camisama_spec['shape'], $camisama_color );
	}

	$camisama_color_attribute = new WC_Product_Attribute();
	$camisama_color_attribute->set_id( wc_attribute_taxonomy_id_by_name( 'pa_color' ) );
	$camisama_color_attribute->set_name( 'pa_color' );
	$camisama_color_attribute->set_options( array_map( static fn ( string $c ): int => $camisama_color_ids[ $c ], $camisama_spec['colors'] ) );
	$camisama_color_attribute->set_visible( true );
	$camisama_color_attribute->set_variation( true );

	$camisama_size_slugs     = array_map( 'strtolower', $camisama_spec['sizes'] );
	$camisama_size_attribute = new WC_Product_Attribute();
	$camisama_size_attribute->set_id( wc_attribute_taxonomy_id_by_name( 'pa_talla' ) );
	$camisama_size_attribute->set_name( 'pa_talla' );
	$camisama_size_attribute->set_options( array_map( static fn ( string $s ): int => $camisama_size_ids[ $s ], $camisama_size_slugs ) );
	$camisama_size_attribute->set_visible( true );
	$camisama_size_attribute->set_variation( true );

	$camisama_product = new WC_Product_Variable();
	$camisama_product->set_name( $camisama_spec['name'] );
	$camisama_product->set_sku( $camisama_spec['sku'] );
	$camisama_product->set_status( 'publish' );
	$camisama_product->set_menu_order( $camisama_order );
	$camisama_product->set_short_description( $camisama_spec['short'] );
	$camisama_product->set_description( $camisama_spec['long'] );
	$camisama_product->set_category_ids( array_map( static fn ( string $c ): int => $camisama_categories[ $c ], $camisama_spec['categories'] ) );
	$camisama_product->set_image_id( reset( $camisama_images ) );
	$camisama_product->set_gallery_image_ids( array_slice( array_values( $camisama_images ), 1 ) );
	$camisama_product->set_attributes( array( $camisama_color_attribute, $camisama_size_attribute ) );
	$camisama_product->set_default_attributes(
		array(
			'pa_color' => $camisama_spec['colors'][0],
			'pa_talla' => in_array( 'm', $camisama_size_slugs, true ) ? 'm' : $camisama_size_slugs[0],
		)
	);
	$camisama_product_id = $camisama_product->save();

	foreach ( $camisama_spec['colors'] as $camisama_color ) {
		foreach ( $camisama_size_slugs as $camisama_size ) {
			$camisama_price = (float) $camisama_spec['price'] + ( 'xxl' === $camisama_size ? CAMISAMA_DEMO_XXL_SURCHARGE : 0.0 );

			$camisama_variation = new WC_Product_Variation();
			$camisama_variation->set_parent_id( $camisama_product_id );
			$camisama_variation->set_attributes(
				array(
					'pa_color' => $camisama_color,
					'pa_talla' => $camisama_size,
				)
			);
			$camisama_variation->set_sku( strtoupper( "{$camisama_spec['sku']}-{$camisama_color}-{$camisama_size}" ) );
			$camisama_variation->set_regular_price( number_format( $camisama_price, 2, '.', '' ) );
			$camisama_variation->set_image_id( $camisama_images[ $camisama_color ] );
			$camisama_variation->save();
		}
	}

	WC_Product_Variable::sync( $camisama_product_id );
	WP_CLI::success( "Creado: {$camisama_spec['name']} (#{$camisama_product_id})" );
}

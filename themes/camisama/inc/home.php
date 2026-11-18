<?php
/**
 * Datos y bloque de precio de la portada. Las tarifas pertenecen al plugin.
 *
 * @package Camisama
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Ejemplo comprable con texto propio: variante real e impresión de hasta 10 cm.
 *
 * @return array<string, mixed>|null Datos del ejemplo, o null si no está disponible.
 */
function camisama_home_price_example(): ?array {
	if ( ! function_exists( 'wc_get_products' ) || ! class_exists( \Camisama\Customizer\Pricing\DesignPricer::class ) ) {
		return null;
	}

	$configs = new \Camisama\Customizer\Config\ProductConfigRepository();
	$prices  = new \Camisama\Customizer\Pricing\PriceTableRepository();

	foreach ( wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'visible',
			'limit'      => 8,
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
		)
	) as $product ) {
		$config = $configs->get( $product->get_id() );
		$zone   = $config->zone( 'front' );
		if ( ! $config->enabled || null === $zone ) {
			continue;
		}
		$variants = $product->is_type( 'variable' ) ? $product->get_children() : array( $product->get_id() );
		foreach ( $variants as $id ) {
			$variant = wc_get_product( $id );
			if ( ! $variant || ! $variant->is_purchasable() || ! $variant->is_in_stock() || '' === $variant->get_price() ) {
				continue;
			}
			$side = min( 10.0, $zone->width_cm, $zone->height_cm );
			try {
				$quote = ( new \Camisama\Customizer\Pricing\DesignPricer( $prices->get() ) )->quote(
					$config,
					array( new \Camisama\Customizer\Design\Element( 'front', 'text', $side / 2, $side / 2, $side, $side ) ),
					array()
				);
			} catch ( \InvalidArgumentException ) {
				continue;
			}
			$base  = wc_get_price_to_display( $variant );
			$total = wc_get_price_to_display( $variant, array( 'price' => (float) $variant->get_price() + $quote['total'] / 100 ) );

			return array(
				'product_id' => $product->get_id(),
				'variant_id' => $variant->get_id(),
				'name'       => $product->get_name(),
				'attributes' => $variant instanceof \WC_Product_Variation ? wc_get_formatted_variation( $variant, true, false, false ) : '',
				'base'       => $base,
				'print'      => $total - $base,
				'total'      => $total,
				'side'       => $side,
				'url'        => $variant->get_permalink(),
			);
		}
	}

	return null;
}

/**
 * Tarifas del ejemplo para el simulador de zonas y el hero (assets/js/home.js).
 *
 * Los importes van en céntimos y ya convertidos a precio mostrado (con IGV si corresponde), con
 * el mismo cálculo que el desglose del servidor, para que el navegador solo tenga que sumar.
 *
 * @param array<string, mixed>|null $example Datos de camisama_home_price_example().
 * @return array<string, mixed>|null Datos serializables, o null sin ejemplo.
 */
function camisama_home_price_data( ?array $example ): ?array {
	if ( null === $example ) {
		return null;
	}
	$variant = wc_get_product( $example['variant_id'] );
	if ( ! $variant ) {
		return null;
	}

	$table  = ( new \Camisama\Customizer\Pricing\PriceTableRepository() )->get();
	$config = ( new \Camisama\Customizer\Config\ProductConfigRepository() )->get( $example['product_id'] );
	$raw    = (float) $variant->get_price();
	$base   = wc_get_price_to_display( $variant );

	$tiers = array();
	foreach ( $table->tiers() as $tier ) {
		$shown   = wc_get_price_to_display( $variant, array( 'price' => $raw + $tier->amount / 100 ) );
		$tiers[] = array(
			'label'  => $tier->label,
			'max_cm' => $tier->max_cm,
			'amount' => (int) round( ( $shown - $base ) * 100 ),
		);
	}

	$zones = array();
	foreach ( $config->zones as $zone ) {
		$zones[] = array(
			'id'    => $zone->id,
			'label' => $zone->label,
			'tiers' => count( $table->tiers_within( $zone->longest_side_cm() ) ),
		);
	}

	return array(
		'base'  => (int) round( $base * 100 ),
		'tiers' => $tiers,
		'zones' => $zones,
		'text'  => array(
			'combo'     => __( 'Tu combinación', 'camisama' ),
			'garment'   => __( 'Solo la prenda', 'camisama' ),
			/* translators: %s: centímetros. */
			'upTo'      => __( 'hasta %s cm', 'camisama' ),
			/* translators: 1: total, 2: zonas y tamaños elegidos. */
			'total'     => __( 'Total %1$s, con %2$s.', 'camisama' ),
			/* translators: %s: total. */
			'totalNone' => __( 'Total %s, sin impresión.', 'camisama' ),
		),
		'money' => array(
			'symbol'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
			'format'   => html_entity_decode( get_woocommerce_price_format(), ENT_QUOTES, 'UTF-8' ),
			'decimals' => wc_get_price_decimals(),
			'decimal'  => wc_get_price_decimal_separator(),
			'thousand' => wc_get_price_thousand_separator(),
		),
	);
}

/**
 * Render del bloque: el precio se actualiza incluso si la plantilla se guarda en el editor.
 *
 * @param array<string, mixed>|null $example Datos calculados para la sección.
 * @return string Desglose con precios actuales o explicación sin importes.
 */
function camisama_render_home_price_example( ?array $example ): string {
	ob_start();
	?>
	<div class="camisama-price-example" data-camisama-price-card>
		<?php if ( null !== $example ) : ?>
			<h3 data-price-title><?php esc_html_e( 'Una frase en el frente', 'camisama' ); ?></h3>
			<p class="camisama-price-example__model"><?php echo esc_html( $example['name'] ); ?> · <?php echo esc_html( $example['attributes'] ); ?></p>
			<dl>
				<div><dt><?php esc_html_e( 'Prenda sin impresión', 'camisama' ); ?></dt><dd><?php echo wp_kses_post( wc_price( $example['base'] ) ); ?></dd></div>
				<div data-price-line><dt><?php /* translators: %s: tamaño en centímetros. */ echo esc_html( sprintf( __( 'Impresión de hasta %s cm', 'camisama' ), number_format_i18n( $example['side'] ) ) ); ?></dt><dd><?php echo wp_kses_post( wc_price( $example['print'] ) ); ?></dd></div>
				<div class="camisama-price-example__total"><dt><?php esc_html_e( 'Total por una camiseta', 'camisama' ); ?></dt><dd data-price-total><?php echo wp_kses_post( wc_price( $example['total'] ) ); ?></dd></div>
			</dl>
			<p class="screen-reader-text" aria-live="polite" data-price-announce></p>
			<p class="camisama-home-note"><?php esc_html_e( 'Con texto o fotos propias, sin estampados premium. No incluye entrega. El total final depende de tu talla y tu diseño.', 'camisama' ); ?></p>
			<a class="camisama-text-link" href="<?php echo esc_url( $example['url'] ); ?>"><?php esc_html_e( 'Personalizar este modelo', 'camisama' ); ?></a>
		<?php else : ?>
			<h3><?php esc_html_e( 'Prenda + impresión = tu total', 'camisama' ); ?></h3>
			<p><?php esc_html_e( 'Elige un modelo para consultar sus precios. Verás el importe de cada zona mientras diseñas.', 'camisama' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Registra un bloque dinámico con vista previa en el editor, sin JS en la tienda.
 */
function camisama_register_home_price_block(): void {
	wp_register_script(
		'camisama-home-blocks',
		get_theme_file_uri( 'assets/js/home-blocks.js' ),
		array( 'wp-blocks', 'wp-block-editor', 'wp-element', 'wp-server-side-render', 'wp-i18n' ),
		(string) filemtime( __DIR__ . '/../assets/js/home-blocks.js' ),
		true
	);
	register_block_type(
		__DIR__ . '/../blocks/home-price',
		array(
			'editor_script'   => 'camisama-home-blocks',
			'render_callback' => 'camisama_render_home_price',
			'supports'        => array( 'html' => false ),
		)
	);
	register_block_type(
		__DIR__ . '/../blocks/home-designs',
		array(
			'editor_script'   => 'camisama-home-blocks',
			'render_callback' => 'camisama_render_home_designs',
			'supports'        => array( 'html' => false ),
		)
	);
}

/**
 * Estampados publicados del catálogo del personalizador, en el orden del administrador.
 *
 * @return list<array<string, mixed>> Estampados con archivo, o lista vacía sin el plugin.
 */
function camisama_home_designs(): array {
	if ( ! class_exists( \Camisama\Customizer\Designs\DesignRepository::class ) ) {
		return array();
	}

	return ( new \Camisama\Customizer\Designs\DesignRepository() )->query( null, 1, 12 )['items'];
}

/**
 * Franja de estampados: dinámica para que siga al catálogo aunque se guarde la plantilla.
 *
 * @return string HTML de la franja, o vacío si no hay estampados.
 */
function camisama_render_home_designs(): string {
	ob_start();
	require __DIR__ . '/../template-parts/home-designs.php';
	return (string) ob_get_clean();
}
add_action( 'init', 'camisama_register_home_price_block' );

/**
 * En la portada, el botón describe la entrada al personalizador.
 *
 * @param string      $text Texto de WooCommerce.
 * @param \WC_Product $product Producto de la tarjeta.
 * @return string Texto de la acción.
 */
function camisama_home_product_button( string $text, \WC_Product $product ): string {
	if ( is_front_page() && class_exists( \Camisama\Customizer\Config\ProductConfigRepository::class ) ) {
		$config = ( new \Camisama\Customizer\Config\ProductConfigRepository() )->get( $product->get_id() );
		if ( $config->enabled && $product->is_type( 'variable' ) && $product->is_purchasable() && $product->is_in_stock() ) {
			return __( 'Personalizar', 'camisama' );
		}
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'camisama_home_product_button', 10, 2 );

/**
 * Sección dinámica: conserva tarifas y ejemplo actuales en plantillas guardadas.
 *
 * @return string HTML de la sección.
 */
function camisama_render_home_price(): string {
	ob_start();
	require __DIR__ . '/../template-parts/home-pricing.php';
	return (string) ob_get_clean();
}

/**
 * Vista previa al compartir la portada (WhatsApp, Facebook, X) y descripción para buscadores.
 *
 * No hay plugin de SEO; si se instala uno, quitar esto para no duplicar etiquetas. La imagen
 * se genera desde bin/og-image/og-home.html con bin/og-image/render.ps1.
 */
function camisama_home_social_meta(): void {
	if ( ! is_front_page() ) {
		return;
	}

	$title       = get_bloginfo( 'name' ) . ' – ' . __( 'Tu diseño, tu ley.', 'camisama' );
	$description = __( 'Personaliza tu camiseta con estampados, texto o tus fotos, desde una unidad. Impresión DTF y entrega en Lima y Callao.', 'camisama' );
	$image       = get_theme_file_uri( 'assets/images/og-home.png' );
	$tags        = array(
		array( 'name', 'description', $description ),
		array( 'property', 'og:type', 'website' ),
		// La tienda vende en Perú aunque WordPress use es_MX (más traducciones completas).
		array( 'property', 'og:locale', 'es_PE' ),
		array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
		array( 'property', 'og:title', $title ),
		array( 'property', 'og:description', $description ),
		array( 'property', 'og:url', home_url( '/' ) ),
		array( 'property', 'og:image', $image ),
		array( 'property', 'og:image:width', '1200' ),
		array( 'property', 'og:image:height', '630' ),
		array( 'property', 'og:image:alt', __( 'Camisama: Tu diseño, tu ley. Camiseta negra con la frase A mi manera.', 'camisama' ) ),
		array( 'name', 'twitter:card', 'summary_large_image' ),
	);

	foreach ( $tags as list( $attribute, $key, $value ) ) {
		printf( '<meta %1$s="%2$s" content="%3$s">' . "\n", esc_attr( $attribute ), esc_attr( $key ), esc_attr( $value ) );
	}
}
add_action( 'wp_head', 'camisama_home_social_meta', 5 );

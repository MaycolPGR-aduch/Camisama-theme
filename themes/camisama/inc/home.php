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
 * Render del bloque: el precio se actualiza incluso si la plantilla se guarda en el editor.
 *
 * @param array<string, mixed>|null $example Datos calculados para la sección.
 * @return string Desglose con precios actuales o explicación sin importes.
 */
function camisama_render_home_price_example( ?array $example ): string {
	ob_start();
	?>
	<div class="camisama-price-example">
		<?php if ( null !== $example ) : ?>
			<h3><?php esc_html_e( 'Una frase en el frente', 'camisama' ); ?></h3>
			<p class="camisama-price-example__model"><?php echo esc_html( $example['name'] ); ?> · <?php echo esc_html( $example['attributes'] ); ?></p>
			<dl>
				<div><dt><?php esc_html_e( 'Prenda sin impresión', 'camisama' ); ?></dt><dd><?php echo wp_kses_post( wc_price( $example['base'] ) ); ?></dd></div>
				<div><dt><?php /* translators: %s: tamaño en centímetros. */ echo esc_html( sprintf( __( 'Impresión de hasta %s cm', 'camisama' ), number_format_i18n( $example['side'] ) ) ); ?></dt><dd><?php echo wp_kses_post( wc_price( $example['print'] ) ); ?></dd></div>
				<div class="camisama-price-example__total"><dt><?php esc_html_e( 'Total por una camiseta', 'camisama' ); ?></dt><dd><?php echo wp_kses_post( wc_price( $example['total'] ) ); ?></dd></div>
			</dl>
			<p class="camisama-home-note"><?php esc_html_e( 'Ejemplo con texto propio, sin estampados premium. No incluye entrega. El total cambia con tu talla y diseño.', 'camisama' ); ?></p>
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

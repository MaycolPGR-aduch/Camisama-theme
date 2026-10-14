<?php
/**
 * Render del bloque "Personalizador de prendas".
 *
 * Si el producto es personalizable, incrusta sus datos y un contenedor que monta view.js; el
 * contenido interior (la galería del producto) queda como respaldo hasta que carga el editor y
 * para quien navegue sin JavaScript. Si no es personalizable, solo muestra ese contenido.
 *
 * @package Camisama\Customizer
 */

use Camisama\Customizer\Products\CustomizerData;

defined( 'ABSPATH' ) || exit;

/**
 * Instancia del bloque que WordPress pasa a esta plantilla.
 *
 * @var WP_Block $block
 */

/**
 * HTML de los bloques interiores (la galería del producto).
 *
 * @var string $content
 */
$camisama_product_id = (int) ( $block->context['postId'] ?? get_the_ID() );
$camisama_data       = CustomizerData::create()->for_product( $camisama_product_id );

if ( null === $camisama_data ) {
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bloques interiores ya renderizados por WordPress.
	return;
}
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'data-product-id' => (string) $camisama_product_id ) ) ); ?>>
	<script type="application/json" class="wp-block-camisama-customizer__data"><?php echo wp_json_encode( $camisama_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON con < y & escapados: no puede cerrar la etiqueta script. ?></script>
	<div class="wp-block-camisama-customizer__fallback">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bloques interiores ya renderizados por WordPress. ?>
	</div>
</div>

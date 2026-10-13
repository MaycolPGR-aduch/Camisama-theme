<?php
/**
 * Render del bloque "Personalizador de prendas" (el contenido lo monta view.js).
 *
 * @package Camisama\Customizer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Instancia del bloque que WordPress pasa a esta plantilla.
 *
 * @var WP_Block $block
 */
$camisama_product_id = (int) ( $block->context['postId'] ?? get_the_ID() );
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'data-product-id' => (string) $camisama_product_id ) ) ); ?>>
	<p class="wp-block-camisama-customizer__status"><?php esc_html_e( 'Cargando el personalizador…', 'camisama-customizer' ); ?></p>
</div>

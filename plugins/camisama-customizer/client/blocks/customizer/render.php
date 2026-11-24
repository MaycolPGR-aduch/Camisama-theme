<?php
/**
 * Render del bloque "Personalizador de prendas".
 *
 * Si el producto es personalizable, incrusta sus datos y un contenedor que monta view.js; el
 * contenido interior (la galería del producto) queda como respaldo para quien navegue sin JavaScript
 * o si el editor no carga. Si no es personalizable, solo muestra ese contenido.
 *
 * @package Camisama\Customizer
 */

use Camisama\Customizer\Cart\CartEdit;
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

// Editar desde el carrito (?camisama_edit=): el diseño de esa línea, su talla y su cantidad.
$camisama_edit = CartEdit::current( $camisama_product_id );
if ( null !== $camisama_edit ) {
	$camisama_data['edit'] = $camisama_edit;
}

// La prenda, en el color de partida, hasta que llega el editor: ocupa el mismo sitio que su lienzo.
$camisama_mockups = (array) ( $camisama_data['views'][0]['mockups'] ?? array() );
$camisama_product = wc_get_product( $camisama_product_id );
$camisama_color   = $camisama_product instanceof WC_Product ? (string) ( $camisama_product->get_default_attributes()['pa_color'] ?? '' ) : '';
$camisama_preview = $camisama_mockups[ $camisama_color ] ?? reset( $camisama_mockups );
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes( array( 'data-product-id' => (string) $camisama_product_id ) ) ); ?>>
	<?php
	// Antes de pintar la columna de compra: con JavaScript, la ficha nace con la forma del editor. Si su
	// script no llega a arrancar, a los 12 s vuelve la ficha de WooCommerce (galería y formulario).
	wp_print_inline_script_tag(
		sprintf(
			"(function(d){d.classList.add('camisama-flow');try{if(localStorage.getItem('camisama:draft:%d'))d.classList.add('camisama-has-draft')}catch(e){}setTimeout(function(){if(!d.dataset.camisamaEditor){d.classList.remove('camisama-flow');dispatchEvent(new Event('resize'))}},12000)})(document.documentElement);",
			$camisama_product_id
		)
	);
	?>
	<script type="application/json" class="wp-block-camisama-customizer__data"><?php echo wp_json_encode( $camisama_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON con < y & escapados: no puede cerrar la etiqueta script. ?></script>
	<?php if ( is_array( $camisama_preview ) ) : ?>
	<div class="wp-block-camisama-customizer__preview"><img src="<?php echo esc_url( $camisama_preview['url'] ); ?>" alt="<?php echo esc_attr( get_the_title( $camisama_product_id ) ); ?>" width="<?php echo esc_attr( (string) $camisama_preview['width'] ); ?>" height="<?php echo esc_attr( (string) $camisama_preview['height'] ); ?>" fetchpriority="high"></div>
	<?php endif; ?>
	<div class="wp-block-camisama-customizer__fallback">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bloques interiores ya renderizados por WordPress. ?>
	</div>
</div>

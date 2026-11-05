<?php
/**
 * Title: Volver al carrito
 * Slug: camisama/checkout-back
 * Categories: text
 * Inserter: no
 * Description: Salida del pago hacia el carrito, encima del título.
 *
 * @package Camisama
 */

?>
<!-- wp:paragraph {"align":"wide","className":"camisama-checkout-back","fontSize":"small"} -->
<p class="alignwide camisama-checkout-back has-small-font-size"><a href="<?php echo esc_url( camisama_wc_page_url( 'cart' ) ); ?>"><?php esc_html_e( 'Volver al carrito', 'camisama' ); ?></a></p>
<!-- /wp:paragraph -->

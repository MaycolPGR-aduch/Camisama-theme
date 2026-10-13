<?php
/**
 * Title: Cabecera del checkout
 * Slug: camisama/checkout-header
 * Categories: header
 * Block Types: core/template-part/checkout-header
 * Inserter: no
 * Description: Cabecera mínima para el pago: marca y aviso de pago seguro, sin menú.
 *
 * @package Camisama
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40"}},"border":{"bottom":{"color":"var:preset|color|border","width":"1px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="border-bottom-color:var(--wp--preset--color--border);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40)"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:image {"width":"40px","height":"40px","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-mark.svg' ) ); ?>" alt="" style="width:40px;height:40px"/></figure>
<!-- /wp:image -->

<!-- wp:site-title {"level":0} /--></div>
<!-- /wp:group -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
<p class="has-muted-color has-text-color has-small-font-size"><?php esc_html_e( '🔒 Pago seguro', 'camisama' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

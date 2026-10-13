<?php
/**
 * Title: Portada: modelos
 * Slug: camisama/featured-products
 * Categories: woo-commerce
 * Description: Los modelos de prenda disponibles con enlace a la tienda.
 *
 * @package Camisama
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:group {"align":"wide","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group alignwide"><!-- wp:group {"layout":{"type":"constrained","contentSize":"560px","justifyContent":"left"}} -->
<div class="wp-block-group"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Elige tu modelo', 'camisama' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted"} -->
<p class="has-muted-color has-text-color"><?php esc_html_e( 'Prendas de algodón listas para tu diseño. El precio final depende de las zonas que estampes.', 'camisama' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( camisama_wc_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'Ver toda la tienda', 'camisama' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-collection {"queryId":10,"query":{"perPage":4,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"menu_order","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[]},"tagName":"div","displayLayout":{"type":"flex","columns":4,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"queryContextIncludes":["collection"],"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}}} -->
<div class="wp-block-woocommerce-product-collection alignwide" style="margin-top:var(--wp--preset--spacing--60)"><!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"thumbnail","isDescendentOfQueryLoop":true} /-->

<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"large","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->

<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection --></div>
<!-- /wp:group -->

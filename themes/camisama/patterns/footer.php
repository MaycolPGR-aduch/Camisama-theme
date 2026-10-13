<?php
/**
 * Title: Pie de página
 * Slug: camisama/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * @package Camisama
 */

$camisama_columns = array(
	__( 'Tienda', 'camisama' )    => array(
		__( 'Todos los modelos', 'camisama' ) => camisama_wc_page_url( 'shop' ),
		__( 'Camisetas', 'camisama' )         => camisama_product_category_url( 'camisetas' ),
		__( 'Niños', 'camisama' )             => camisama_product_category_url( 'ninos' ),
	),
	__( 'Tu cuenta', 'camisama' ) => array(
		__( 'Mi cuenta', 'camisama' ) => camisama_wc_page_url( 'myaccount' ),
		__( 'Carrito', 'camisama' )   => camisama_wc_page_url( 'cart' ),
	),
	__( 'Ayuda', 'camisama' )     => array(
		__( 'Cómo funciona', 'camisama' )          => home_url( '/#como-funciona' ),
		__( 'Cambios y devoluciones', 'camisama' ) => camisama_wc_page_url( 'refund_returns' ),
		__( 'Privacidad', 'camisama' )             => get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/' ),
	),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|50"}},"elements":{"link":{"color":{"text":"var:preset|color|base"},":hover":{"color":{"text":"var:preset|color|accent"}}}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group"><!-- wp:image {"width":"36px","height":"36px","sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo-mark.svg' ) ); ?>" alt="" style="width:36px;height:36px"/></figure>
<!-- /wp:image -->

<!-- wp:site-title {"level":0,"style":{"elements":{"link":{"color":{"text":"var:preset|color|base"}}}}} /--></div>
<!-- /wp:group -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size"><?php esc_html_e( 'Prendas personalizadas e impresas en DTF. Diseñadas por ti, estampadas en Perú.', 'camisama' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<?php foreach ( $camisama_columns as $camisama_title => $camisama_links ) : ?>
<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.08em"}},"textColor":"accent","fontSize":"small"} -->
<h3 class="wp-block-heading has-accent-color has-text-color has-small-font-size" style="letter-spacing:0.08em;text-transform:uppercase"><?php echo esc_html( $camisama_title ); ?></h3>
<!-- /wp:heading -->

<!-- wp:list {"className":"is-style-camisama-plain","fontSize":"small"} -->
<ul class="wp-block-list is-style-camisama-plain has-small-font-size">
	<?php foreach ( $camisama_links as $camisama_label => $camisama_url ) : ?>
<!-- wp:list-item -->
<li><a href="<?php echo esc_url( $camisama_url ); ?>"><?php echo esc_html( $camisama_label ); ?></a></li>
<!-- /wp:list-item -->
	<?php endforeach; ?>
</ul>
<!-- /wp:list --></div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns -->

<!-- wp:group {"align":"wide","style":{"border":{"top":{"color":"var:preset|color|muted","width":"1px"}},"spacing":{"margin":{"top":"var:preset|spacing|60"},"padding":{"top":"var:preset|spacing|40"}}},"fontSize":"small","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide has-small-font-size" style="border-top-color:var(--wp--preset--color--muted);border-top-width:1px;margin-top:var(--wp--preset--spacing--60);padding-top:var(--wp--preset--spacing--40)"><!-- wp:paragraph -->
<p>
	<?php
	/* translators: %s: año actual. */
	echo esc_html( sprintf( __( '© %s Camisama · Tu diseño, tu ley.', 'camisama' ), gmdate( 'Y' ) ) );
	?>
</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Precios en soles (S/) · Pago seguro', 'camisama' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

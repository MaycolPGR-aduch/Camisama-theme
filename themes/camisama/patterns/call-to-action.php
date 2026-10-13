<?php
/**
 * Title: Llamada a la acción
 * Slug: camisama/call-to-action
 * Categories: call-to-action
 * Description: Banda roja con invitación a diseñar.
 *
 * @package Camisama
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"backgroundColor":"primary","textColor":"surface","layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group alignfull has-surface-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:heading {"textAlign":"center","fontSize":"xx-large"} -->
<h2 class="wp-block-heading has-text-align-center has-xx-large-font-size"><?php esc_html_e( '¿Te animas a crear la tuya?', 'camisama' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"><?php esc_html_e( 'Desde una sola prenda. Pagas en soles y de forma segura.', 'camisama' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--50)"><!-- wp:button {"backgroundColor":"contrast","textColor":"base","className":"is-style-fill"} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-base-color has-contrast-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( camisama_wc_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'Empezar a diseñar', 'camisama' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->

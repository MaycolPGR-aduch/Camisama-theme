<?php
/**
 * Title: Portada: zonas de impresión
 * Slug: camisama/print-zones
 * Categories: text
 * Description: Las zonas de la prenda que se pueden personalizar y su tamaño máximo.
 *
 * @package Camisama
 */

$camisama_zones = array(
	array( __( 'Frente', 'camisama' ), __( 'hasta 30 × 40 cm', 'camisama' ) ),
	array( __( 'Espalda', 'camisama' ), __( 'hasta 30 × 40 cm', 'camisama' ) ),
	array( __( 'Pecho (logo)', 'camisama' ), __( 'hasta 10 × 10 cm', 'camisama' ) ),
	array( __( 'Mangas', 'camisama' ), __( 'hasta 9 × 9 cm cada una', 'camisama' ) ),
	array( __( 'Nuca', 'camisama' ), __( 'hasta 8 × 5 cm', 'camisama' ) ),
);
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}},"elements":{"link":{"color":{"text":"var:preset|color|base"}}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:columns {"verticalAlignment":"center","align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|70"}}}} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Cada zona, a tu manera', 'camisama' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Combina estampados, textos e imágenes en todas las zonas que quieras. Solo pagas por las que imprimes, según el tamaño de tu diseño.', 'camisama' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"textColor":"accent","fontSize":"small"} -->
<p class="has-accent-color has-text-color has-small-font-size"><?php esc_html_e( 'Impresión DTF: colores vivos, degradados y fotos, en prendas claras u oscuras.', 'camisama' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:list {"className":"is-style-camisama-zones"} -->
<ul class="wp-block-list is-style-camisama-zones">
<?php foreach ( $camisama_zones as $camisama_zone ) : ?>
<!-- wp:list-item -->
<li><strong><?php echo esc_html( $camisama_zone[0] ); ?></strong> <?php echo esc_html( $camisama_zone[1] ); ?></li>
<!-- /wp:list-item -->
<?php endforeach; ?>
</ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<?php
/**
 * Title: Portada: cómo funciona
 * Slug: camisama/how-it-works
 * Categories: text
 * Description: Los tres pasos para pedir una prenda personalizada.
 *
 * @package Camisama
 */

$camisama_steps = array(
	array(
		'title' => __( 'Elige tu modelo', 'camisama' ),
		'text'  => __( 'Clásica, oversize, manga larga o para niños. Escoge el color y la talla.', 'camisama' ),
	),
	array(
		'title' => __( 'Diseña cada zona', 'camisama' ),
		'text'  => __( 'Frente, espalda, mangas y nuca. Usa estampados del catálogo, escribe tu texto o sube tu imagen.', 'camisama' ),
	),
	array(
		'title' => __( 'Lo estampamos y te llega', 'camisama' ),
		'text'  => __( 'Imprimimos tu diseño en DTF a todo color, lo planchamos sobre la prenda y te lo enviamos.', 'camisama' ),
	),
);
?>
<!-- wp:group {"anchor":"como-funciona","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div id="como-funciona" class="wp-block-group alignfull has-surface-background-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Así de fácil', 'camisama' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"},"blockGap":{"left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--60)">
<?php foreach ( $camisama_steps as $camisama_index => $camisama_step ) : ?>
<!-- wp:column {"style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"radius":"24px"}},"backgroundColor":"base"} -->
<div class="wp-block-column has-base-background-color has-background" style="border-radius:24px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"800","lineHeight":"1"}},"textColor":"primary","fontSize":"xx-large","fontFamily":"display"} -->
<p class="has-primary-color has-text-color has-display-font-family has-xx-large-font-size" style="font-weight:800;line-height:1"><?php echo esc_html( (string) ( $camisama_index + 1 ) ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $camisama_step['title'] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted"} -->
<p class="has-muted-color has-text-color"><?php echo esc_html( $camisama_step['text'] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->
<?php endforeach; ?>
</div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

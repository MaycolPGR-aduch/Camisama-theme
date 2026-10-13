<?php
/**
 * Title: Portada: presentación
 * Slug: camisama/hero
 * Categories: banner
 * Description: Titular principal con llamada a la acción e ilustración de la camiseta en el editor.
 *
 * @package Camisama
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)"><!-- wp:columns {"verticalAlignment":"center","align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|70"}}}} -->
<div class="wp-block-columns alignwide are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%"><!-- wp:paragraph {"style":{"typography":{"fontWeight":"600","textTransform":"uppercase","letterSpacing":"0.08em"}},"textColor":"primary","fontSize":"small"} -->
<p class="has-primary-color has-text-color has-small-font-size" style="font-weight:600;letter-spacing:0.08em;text-transform:uppercase"><?php esc_html_e( 'Camisetas personalizadas desde una unidad', 'camisama' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"fontSize":"display"} -->
<h1 class="wp-block-heading has-display-font-size"><?php esc_html_e( 'Tu diseño, tu ley.', 'camisama' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"muted","fontSize":"large"} -->
<p class="has-muted-color has-text-color has-large-font-size"><?php esc_html_e( 'Elige el modelo y el color, y diseña el frente, la espalda y las mangas con nuestros estampados, tus textos o tus propias imágenes. Lo imprimimos a todo color y te lo enviamos.', 'camisama' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--50)"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( camisama_wc_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'Diseña la tuya', 'camisama' ); ?></a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#como-funciona"><?php esc_html_e( 'Cómo funciona', 'camisama' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%"><!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero-tee.svg' ) ); ?>" alt="<?php esc_attr_e( 'Camiseta con una zona de impresión seleccionada en el editor', 'camisama' ); ?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

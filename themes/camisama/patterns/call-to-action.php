<?php
/**
 * Title: Portada: empezar a crear
 * Slug: camisama/call-to-action
 * Categories: call-to-action
 *
 * Al llegar a la sección, el sello "Tu diseño, tu ley." cae sobre la camiseta (assets/js/home.js
 * pone is-armed y luego is-stamped). Sin JS o con movimiento reducido el sello ya está puesto.
 *
 * @package Camisama
 */

?>
<!-- wp:html -->
<section class="camisama-home-section camisama-home-close alignfull" aria-labelledby="camisama-close-title" data-camisama-close>
<div class="camisama-home-wrap camisama-home-close__layout">
<div class="camisama-home-close__copy"><h2 id="camisama-close-title"><?php esc_html_e( 'Que tu próxima camiseta diga algo de ti.', 'camisama' ); ?></h2><p><?php esc_html_e( 'Empieza por el modelo. Lo demás lo pones tú.', 'camisama' ); ?></p><a class="camisama-home-button camisama-home-button--paper" href="#modelos"><?php esc_html_e( 'Elegir mi camiseta', 'camisama' ); ?></a></div>
<figure class="camisama-home-close__visual" aria-hidden="true">
<div class="camisama-tee camisama-home-close__tee"><img class="camisama-tee__base" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example-tee-white.png' ) ); ?>" alt="" width="800" height="800" loading="lazy"><span class="camisama-stamp"><span><?php esc_html_e( 'TU DISEÑO,', 'camisama' ); ?></span><span><?php esc_html_e( 'TU LEY.', 'camisama' ); ?></span></span></div>
</figure>
</div>
</section>
<!-- /wp:html -->

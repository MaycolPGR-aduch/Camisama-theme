<?php
/**
 * Title: Portada: presentación
 * Slug: camisama/hero
 * Categories: banner
 *
 * @package Camisama
 */

?>
<!-- wp:html -->
<section class="camisama-hero alignfull" aria-labelledby="camisama-hero-title">
<div class="camisama-home-wrap camisama-hero__layout">
<div class="camisama-hero__copy">
<h1 id="camisama-hero-title"><?php esc_html_e( 'Tu diseño,', 'camisama' ); ?><br><?php esc_html_e( 'tu ley.', 'camisama' ); ?></h1>
<p class="camisama-hero__intro"><?php esc_html_e( 'Esa frase. Esa foto. Eso tan tuyo.', 'camisama' ); ?></p>
<p><?php esc_html_e( 'Ponlo en una camiseta. Elige tu modelo y crea con estampados, texto o tus propias imágenes. Desde una unidad.', 'camisama' ); ?></p>
<div class="camisama-home-actions"><a class="camisama-home-button" href="#modelos"><?php esc_html_e( 'Elegir mi camiseta', 'camisama' ); ?></a><a class="camisama-text-link" href="#como-funciona"><?php esc_html_e( 'Cómo funciona', 'camisama' ); ?></a></div>
<p class="camisama-home-note camisama-hero__delivery"><?php esc_html_e( 'Hecha a tu manera · Entrega en Lima y Callao, o recojo en taller', 'camisama' ); ?></p>
</div>
<figure class="camisama-hero__visual">
<div class="camisama-tee camisama-tee--hero" role="img" aria-label="Ejemplo digital: camiseta negra con un sol y la frase A mi manera."><img class="camisama-tee__base" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example-tee-black.png' ) ); ?>" alt="" width="800" height="800" fetchpriority="high"><div class="camisama-tee__print camisama-tee__print--hero" aria-hidden="true"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example-sun.svg' ) ); ?>" alt="" width="100" height="100"><span>A MI<br>MANERA.</span></div></div>
<figcaption><?php esc_html_e( 'Tú pones la idea. Nosotros la estampamos.', 'camisama' ); ?><small><?php esc_html_e( 'Ejemplo digital de personalización', 'camisama' ); ?></small></figcaption>
</figure>
</div>
</section>
<!-- /wp:html -->

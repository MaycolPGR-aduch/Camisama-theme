<?php
/**
 * Title: Portada: cómo funciona
 * Slug: camisama/how-it-works
 * Categories: text
 *
 * Mientras se baja por los pasos, la prenda de la izquierda se va armando: color, diseño y talla
 * (assets/js/home.js pone data-step). Sin JS se ve la prenda terminada y los tres pasos iguales.
 *
 * @package Camisama
 */

$camisama_steps = array(
	array( __( 'Elige tu base', 'camisama' ), __( 'Encuentra tu modelo y el color que va contigo.', 'camisama' ) ),
	array( __( 'Hazla tuya', 'camisama' ), __( 'Añade estampados, texto o fotos. Mira cómo queda y cómo cambia el precio.', 'camisama' ) ),
	array( __( 'Revisa y pide', 'camisama' ), __( 'Elige talla y cantidad, revisa tu diseño y continúa con la compra.', 'camisama' ) ),
);
?>
<!-- wp:html -->
<section id="como-funciona" class="camisama-home-section camisama-steps alignfull" aria-labelledby="camisama-steps-title" data-camisama-steps data-step="2">
<div class="camisama-home-wrap camisama-steps__layout">
<h2 id="camisama-steps-title"><?php esc_html_e( 'De tu cabeza a tu camiseta.', 'camisama' ); ?></h2>
<div class="camisama-steps__stage" aria-hidden="true">
<div class="camisama-tee camisama-steps__tee">
<img class="camisama-tee__base camisama-steps__base camisama-steps__base--white" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example-tee-white.png' ) ); ?>" alt="" width="800" height="800" loading="lazy">
<img class="camisama-tee__base camisama-steps__base camisama-steps__base--navy" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example-tee-navy.png' ) ); ?>" alt="" width="800" height="800" loading="lazy">
<span class="camisama-steps__swatches"><span style="--swatch: #FAFAF7"></span><span style="--swatch: #1C1C21"></span><span style="--swatch: #C62A22"></span><span class="is-chosen" style="--swatch: #1F2A4D"></span></span>
<span class="camisama-steps__zone"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example-sun.svg' ) ); ?>" alt="" width="160" height="160" loading="lazy"><span>TU LEY.</span></span>
<span class="camisama-steps__order"><span class="camisama-steps__sizes"><span>S</span><span class="is-chosen">M</span><span>L</span></span><span class="camisama-steps__ready"><?php esc_html_e( 'Lista para pedir', 'camisama' ); ?></span></span>
</div>
</div>
<ol class="camisama-steps__list">
<?php foreach ( $camisama_steps as $camisama_index => $camisama_step ) : ?>
<li data-step-index="<?php echo esc_attr( (string) $camisama_index ); ?>"><h3><?php echo esc_html( $camisama_step[0] ); ?></h3><p><?php echo esc_html( $camisama_step[1] ); ?></p></li>
<?php endforeach; ?>
</ol>
</div>
</section>
<!-- /wp:html -->

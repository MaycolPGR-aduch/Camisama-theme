<?php
/**
 * Title: Portada: presentación
 * Slug: camisama/hero
 * Categories: banner
 *
 * La prenda de la derecha muestra tres ejemplos que se turnan solos (con pausa y selección
 * manual en assets/js/home.js). Sin JavaScript se queda en el primero. El precio de cada ejemplo
 * se lee de las tarifas reales que publica la sección de zonas y precio.
 *
 * @package Camisama
 */

$camisama_sun   = get_theme_file_uri( 'assets/images/example-sun.svg' );
$camisama_photo = get_theme_file_uri( 'assets/images/example-photo.webp' );
$camisama_live  = array(
	array(
		'tee'   => 'black',
		'size'  => 20,
		'tab'   => __( 'Una frase', 'camisama' ),
		'label' => __( 'Ejemplo digital: camiseta negra con la frase A mi manera en el frente.', 'camisama' ),
	),
	array(
		'tee'   => 'white',
		'size'  => 10,
		'tab'   => __( 'Un estampado', 'camisama' ),
		'label' => __( 'Ejemplo digital: camiseta blanca con el estampado Sol andino pequeño en el pecho.', 'camisama' ),
	),
	array(
		'tee'   => 'black',
		'size'  => 30,
		'tab'   => __( 'Tu foto', 'camisama' ),
		'label' => __( 'Ejemplo digital: camiseta negra con una foto grande de un lago de montaña en el frente.', 'camisama' ),
	),
);
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
<figure class="camisama-hero__visual camisama-live" data-camisama-live data-state="0" data-tee="black">
<div class="camisama-tee camisama-live__stage" role="img" aria-label="<?php echo esc_attr( $camisama_live[0]['label'] ); ?>">
<img class="camisama-tee__base camisama-live__base camisama-live__base--black" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example-tee-black.png' ) ); ?>" alt="" width="800" height="800" fetchpriority="high">
<img class="camisama-tee__base camisama-live__base camisama-live__base--white" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example-tee-white.png' ) ); ?>" alt="" width="800" height="800" loading="lazy">
<div class="camisama-live__zone" aria-hidden="true">
<span class="camisama-live__print camisama-live__print--text" data-print="0">A MI<br>MANERA.</span>
<img class="camisama-live__print camisama-live__print--logo" data-print="1" src="<?php echo esc_url( $camisama_sun ); ?>" alt="" width="160" height="160" loading="lazy">
<img class="camisama-live__print camisama-live__print--photo" data-print="2" src="<?php echo esc_url( $camisama_photo ); ?>" alt="" width="520" height="520" loading="lazy">
</div>
<p class="camisama-live__chip" aria-hidden="true" hidden></p>
</div>
<div class="camisama-live__controls" hidden>
<div class="camisama-live__tabs" role="group" aria-label="<?php esc_attr_e( 'Ejemplos de personalización', 'camisama' ); ?>">
<?php foreach ( $camisama_live as $camisama_index => $camisama_item ) : ?>
<button type="button" class="camisama-live__tab" data-index="<?php echo esc_attr( (string) $camisama_index ); ?>" data-tee="<?php echo esc_attr( $camisama_item['tee'] ); ?>" data-size="<?php echo esc_attr( (string) $camisama_item['size'] ); ?>" data-label="<?php echo esc_attr( $camisama_item['label'] ); ?>" aria-pressed="<?php echo 0 === $camisama_index ? 'true' : 'false'; ?>"><?php echo esc_html( $camisama_item['tab'] ); ?><span class="camisama-live__progress" aria-hidden="true"></span></button>
<?php endforeach; ?>
</div>
<button type="button" class="camisama-live__pause" aria-pressed="false" aria-label="<?php esc_attr_e( 'Pausar los ejemplos', 'camisama' ); ?>" data-play-label="<?php esc_attr_e( 'Reproducir los ejemplos', 'camisama' ); ?>"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path class="camisama-live__icon-pause" d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z"/><path class="camisama-live__icon-play" d="M8 5.5v13l10.5-6.5z"/></svg></button>
</div>
<div class="camisama-seal" role="img" aria-label="<?php esc_attr_e( 'Desde una unidad, estampado en Perú.', 'camisama' ); ?>">
<svg class="camisama-seal__ring" viewBox="0 0 120 120" aria-hidden="true" focusable="false"><defs><path id="camisama-seal-path" d="M60 60m-45 0a45 45 0 1 1 90 0a45 45 0 1 1-90 0"/></defs><g class="camisama-seal__spin"><circle cx="60" cy="60" r="59"/><text textLength="282" lengthAdjust="spacing"><textPath href="#camisama-seal-path"><?php esc_html_e( 'DESDE 1 UNIDAD ✦ ESTAMPADO EN PERÚ ✦', 'camisama' ); ?></textPath></text></g></svg>
<svg class="camisama-seal__mark" viewBox="0 0 48 48" aria-hidden="true" focusable="false"><path d="M18 11Q24 16 30 11L38 14L42 21L36 23.5V37H12V23.5L6 21L10 14Z"/><rect x="26.5" y="20" width="5" height="5" rx="1"/></svg>
</div>
<figcaption><?php esc_html_e( 'Tú pones la idea. Nosotros la estampamos.', 'camisama' ); ?><small><?php esc_html_e( 'Ejemplos digitales de personalización', 'camisama' ); ?></small></figcaption>
</figure>
</div>
</section>
<!-- /wp:html -->

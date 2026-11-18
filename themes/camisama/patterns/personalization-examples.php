<?php
/**
 * Title: Portada: ideas de personalización
 * Slug: camisama/personalization-examples
 * Categories: gallery
 *
 * Cada ejemplo lleva los colores de la camiseta clásica (sus maquetas reales están copiadas en
 * assets/images). assets/js/home.js muestra las muestras y cambia la prenda; sin JS se ve el
 * color de partida.
 *
 * @package Camisama
 */

$camisama_colors = array(
	'white' => array( __( 'Blanco', 'camisama' ), '#FAFAF7', 'light', 'example-tee-white.png' ),
	'black' => array( __( 'Negro', 'camisama' ), '#1C1C21', 'dark', 'example-tee-black.png' ),
	'red'   => array( __( 'Rojo', 'camisama' ), '#C62A22', 'dark', 'example-tee-red.png' ),
	'navy'  => array( __( 'Azul marino', 'camisama' ), '#1F2A4D', 'dark', 'example-tee-navy.png' ),
);

$camisama_examples = array(
	array(
		'color' => 'white',
		'label' => __( 'Ejemplo digital de camiseta con el estampado Sol andino.', 'camisama' ),
		'print' => '<img class="camisama-tee__print camisama-tee__print--catalog" src="' . esc_url( get_theme_file_uri( 'assets/images/example-sun.svg' ) ) . '" alt="" width="160" height="160" loading="lazy">',
		'title' => __( 'Un estampado que va contigo', 'camisama' ),
		'text'  => __( 'Elige del catálogo y dale tu lugar en la prenda.', 'camisama' ),
	),
	array(
		'color' => 'black',
		'label' => __( 'Ejemplo digital de camiseta con la frase Hoy se sale a vivir.', 'camisama' ),
		'print' => '<span class="camisama-tee__print camisama-tee__print--text" aria-hidden="true">HOY SE SALE<br>A VIVIR.</span>',
		'title' => __( 'Eso que solo tú dirías', 'camisama' ),
		'text'  => __( 'Escribe tu frase y elige la letra y el color.', 'camisama' ),
	),
	array(
		'color' => 'white',
		'label' => __( 'Ejemplo digital de camiseta con foto ilustrativa de un lago de montaña.', 'camisama' ),
		'print' => '<img class="camisama-tee__print camisama-tee__print--photo" src="' . esc_url( get_theme_file_uri( 'assets/images/example-photo.webp' ) ) . '" alt="" width="520" height="520" loading="lazy">',
		'title' => __( 'Un recuerdo para llevar', 'camisama' ),
		'text'  => __( 'Sube tu foto y encuentra su tamaño ideal.', 'camisama' ),
	),
);
?>
<!-- wp:html -->
<section id="ideas" class="camisama-home-section camisama-examples alignfull" aria-labelledby="camisama-ideas-title">
<div class="camisama-home-wrap">
<div class="camisama-section-heading"><h2 id="camisama-ideas-title"><?php esc_html_e( 'Una camiseta. Mil formas de hacerla tuya.', 'camisama' ); ?></h2><p><?php esc_html_e( 'Empieza con lo que tienes en mente. También puedes mezclarlo todo.', 'camisama' ); ?></p></div>
<div class="camisama-examples__grid">
<?php foreach ( $camisama_examples as $camisama_example ) : ?>
	<?php $camisama_start = $camisama_colors[ $camisama_example['color'] ]; ?>
<figure>
<div class="camisama-tee" role="img" aria-label="<?php echo esc_attr( $camisama_example['label'] ); ?>" data-tone="<?php echo esc_attr( $camisama_start[2] ); ?>"><img class="camisama-tee__base" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $camisama_start[3] ) ); ?>" alt="" width="800" height="800" loading="lazy"><?php echo $camisama_example['print']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- marcado fijo con URL escapada arriba. ?></div>
<div class="camisama-swatches" role="group" aria-label="<?php esc_attr_e( 'Color de la prenda', 'camisama' ); ?>" hidden>
	<?php foreach ( $camisama_colors as $camisama_key => $camisama_color ) : ?>
<button type="button" class="camisama-swatch" style="--swatch: <?php echo esc_attr( $camisama_color[1] ); ?>" aria-label="<?php echo esc_attr( $camisama_color[0] ); ?>" aria-pressed="<?php echo $camisama_key === $camisama_example['color'] ? 'true' : 'false'; ?>" data-src="<?php echo esc_url( get_theme_file_uri( 'assets/images/' . $camisama_color[3] ) ); ?>" data-tone="<?php echo esc_attr( $camisama_color[2] ); ?>"></button>
	<?php endforeach; ?>
</div>
<figcaption><h3><?php echo esc_html( $camisama_example['title'] ); ?></h3><p><?php echo esc_html( $camisama_example['text'] ); ?></p></figcaption>
</figure>
<?php endforeach; ?>
</div>
<div class="camisama-examples__foot"><p class="camisama-home-note"><?php esc_html_e( 'Ejemplos digitales, no pedidos reales, en colores de la camiseta clásica. Foto de muestra generada con IA.', 'camisama' ); ?></p><a class="camisama-text-link" href="#modelos"><?php esc_html_e( 'Elegir un modelo para empezar', 'camisama' ); ?></a></div>
</div>
</section>
<!-- /wp:html -->

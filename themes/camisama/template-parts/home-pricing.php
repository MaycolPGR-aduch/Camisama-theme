<?php
/**
 * Zonas y ejemplo de precio calculado en cada render.
 *
 * @package Camisama
 */

defined( 'ABSPATH' ) || exit;

$camisama_example = camisama_home_price_example();
$camisama_zones   = null !== $camisama_example ? ( new \Camisama\Customizer\Config\ProductConfigRepository() )->get( $camisama_example['product_id'] )->zones : array();
?>
<section id="zonas-y-precio" class="camisama-home-section camisama-print alignfull" aria-labelledby="camisama-print-title">
<div class="camisama-home-wrap">
<div class="camisama-section-heading"><h2 id="camisama-print-title"><?php esc_html_e( 'Tu diseño tiene su lugar. Y su precio.', 'camisama' ); ?></h2><p><?php esc_html_e( 'Frente, espalda, mangas o nuca. Tú eliges dónde; solo suman las zonas que imprimes.', 'camisama' ); ?></p></div>
<div class="camisama-print__layout">
<div class="camisama-print__zones">
<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/print-zones.svg' ) ); ?>" alt="<?php esc_attr_e( 'Mapa de zonas: frente y mangas en la vista frontal; espalda y nuca en la vista posterior.', 'camisama' ); ?>" width="680" height="360" loading="lazy">
<details><summary><?php esc_html_e( 'Ver medidas de referencia', 'camisama' ); ?></summary>
<?php if ( null !== $camisama_example && array() !== $camisama_zones ) : ?>
<p><?php echo esc_html( $camisama_example['name'] ); ?>:</p>
<ul class="camisama-zone-sizes">
	<?php foreach ( $camisama_zones as $camisama_zone ) : ?>
<li><?php echo esc_html( $camisama_zone->label ); ?>: <?php echo esc_html( number_format_i18n( $camisama_zone->width_cm, 1 ) . ' × ' . number_format_i18n( $camisama_zone->height_cm, 1 ) ); ?> cm</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<p><?php esc_html_e( 'Las zonas y medidas dependen del modelo; las prendas infantiles tienen áreas más pequeñas. Consulta las de tu prenda en el personalizador.', 'camisama' ); ?></p></details>
<div class="camisama-print__rules"><h3><?php esc_html_e( 'Así se forma tu total', 'camisama' ); ?></h3><p><?php esc_html_e( 'La prenda que elijas + la impresión según el tamaño en cada zona + los estampados premium que uses.', 'camisama' ); ?></p><p><?php esc_html_e( 'Tu texto y tus fotos no tienen recargo de catálogo. El precio se actualiza mientras diseñas, antes de añadir al carrito.', 'camisama' ); ?></p></div>
</div>
<?php echo camisama_render_home_price_example( $camisama_example ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado en la función. ?>
</div>
</div>
</section>

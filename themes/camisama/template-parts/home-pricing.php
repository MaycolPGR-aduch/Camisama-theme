<?php
/**
 * Zonas y ejemplo de precio calculado en cada render.
 *
 * Con JavaScript (assets/js/home.js) el mapa y las fichas se convierten en un simulador: tocas
 * zonas y tamaños y el desglose de la derecha se recalcula con las tarifas reales que van en el
 * JSON de esta sección. Sin JavaScript queda el ejemplo fijo de una frase en el frente.
 *
 * @package Camisama
 */

defined( 'ABSPATH' ) || exit;

$camisama_example = camisama_home_price_example();
$camisama_zones   = null !== $camisama_example ? ( new \Camisama\Customizer\Config\ProductConfigRepository() )->get( $camisama_example['product_id'] )->zones : array();
$camisama_prices  = camisama_home_price_data( $camisama_example );

// Formas de cada zona en el dibujo (viewBox 680 × 360). Las mangas se ven cruzadas: la izquierda
// de quien la lleva queda a la derecha de la vista frontal.
$camisama_shapes = array(
	'front'        => '<rect x="127" y="130" width="86" height="115" rx="3"/>',
	'right-sleeve' => '<g transform="rotate(-27 65 131)"><rect x="48" y="114" width="34" height="34" rx="3"/></g>',
	'left-sleeve'  => '<g transform="rotate(27 275 131)"><rect x="258" y="114" width="34" height="34" rx="3"/></g>',
	'back'         => '<rect x="467" y="130" width="86" height="115" rx="3"/>',
	'nape'         => '<rect x="488" y="91" width="44" height="26" rx="3"/>',
);
?>
<section id="zonas-y-precio" class="camisama-home-section camisama-print alignfull" aria-labelledby="camisama-print-title">
<div class="camisama-home-wrap">
<div class="camisama-section-heading"><h2 id="camisama-print-title"><?php esc_html_e( 'Tu diseño tiene su lugar. Y su precio.', 'camisama' ); ?></h2><p><?php esc_html_e( 'Frente, espalda, mangas o nuca. Tú eliges dónde; solo suman las zonas que imprimes.', 'camisama' ); ?></p></div>
<div class="camisama-print__layout">
<div class="camisama-print__zones">
<svg class="camisama-zone-map" viewBox="0 0 680 360" role="img" aria-label="<?php esc_attr_e( 'Mapa de zonas: frente y mangas en la vista frontal; espalda y nuca en la vista posterior.', 'camisama' ); ?>" data-camisama-zone-map>
<g class="camisama-zone-map__tee"><path d="M120 64Q170 99 220 64L280 86L318 150L271 176L249 144V288H91V144L69 176L22 150L60 86Z"/><path d="M460 64Q510 86 560 64L620 86L658 150L611 176L589 144V288H431V144L409 176L362 150L400 86Z"/></g>
<?php foreach ( $camisama_shapes as $camisama_id => $camisama_shape ) : ?>
<g class="camisama-zone-map__zone" data-zone="<?php echo esc_attr( $camisama_id ); ?>"><?php echo $camisama_shape; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG fijo de esta plantilla. ?></g>
<?php endforeach; ?>
<g class="camisama-zone-map__labels"><text class="is-front" x="170" y="193"><?php esc_html_e( 'Frente', 'camisama' ); ?></text><text class="is-back" x="510" y="193"><?php esc_html_e( 'Espalda', 'camisama' ); ?></text><text x="170" y="331"><?php esc_html_e( 'Vista frontal', 'camisama' ); ?></text><text x="510" y="331"><?php esc_html_e( 'Vista posterior', 'camisama' ); ?></text><text x="510" y="46"><?php esc_html_e( 'Nuca', 'camisama' ); ?></text><text x="65" y="46"><?php esc_html_e( 'Mangas', 'camisama' ); ?></text></g>
<g class="camisama-zone-map__leaders"><path d="M510 53V85"/><path d="M65 53V104"/></g>
</svg>
<?php if ( null !== $camisama_prices ) : ?>
<div class="camisama-zone-picker" data-camisama-zone-picker hidden>
<p class="camisama-zone-picker__hint" id="camisama-zone-hint"><?php esc_html_e( 'Toca dónde imprimir y mira cómo cambia el total.', 'camisama' ); ?></p>
<div class="camisama-zone-picker__zones" role="group" aria-labelledby="camisama-zone-hint">
	<?php foreach ( $camisama_prices['zones'] as $camisama_zone ) : ?>
<button type="button" class="camisama-zone-chip" data-zone="<?php echo esc_attr( $camisama_zone['id'] ); ?>" aria-pressed="<?php echo 'front' === $camisama_zone['id'] ? 'true' : 'false'; ?>"><?php echo esc_html( $camisama_zone['label'] ); ?></button>
<?php endforeach; ?>
</div>
	<?php foreach ( $camisama_prices['zones'] as $camisama_zone ) : ?>
		<?php
		if ( $camisama_zone['tiers'] < 2 ) {
			continue;
		}
		?>
<fieldset class="camisama-zone-picker__sizes" data-sizes-for="<?php echo esc_attr( $camisama_zone['id'] ); ?>"<?php echo 'front' === $camisama_zone['id'] ? '' : ' hidden'; ?>>
<legend><?php /* translators: %s: nombre de la zona, por ejemplo "Frente". */ echo esc_html( sprintf( __( 'Tamaño en %s', 'camisama' ), mb_strtolower( $camisama_zone['label'] ) ) ); ?></legend>
		<?php foreach ( array_slice( $camisama_prices['tiers'], 0, $camisama_zone['tiers'] ) as $camisama_index => $camisama_tier ) : ?>
<label class="camisama-zone-chip"><input type="radio" name="camisama-size-<?php echo esc_attr( $camisama_zone['id'] ); ?>" value="<?php echo esc_attr( (string) $camisama_index ); ?>"<?php checked( 0, $camisama_index ); ?>><?php echo esc_html( $camisama_tier['label'] ); ?> <small><?php /* translators: %s: centímetros. */ echo esc_html( sprintf( __( '%s cm', 'camisama' ), number_format_i18n( $camisama_tier['max_cm'] ) ) ); ?></small></label>
	<?php endforeach; ?>
</fieldset>
	<?php endforeach; ?>
</div>
<script type="application/json" id="camisama-home-prices"><?php echo wp_json_encode( $camisama_prices, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
<?php endif; ?>
</div>
<?php echo camisama_render_home_price_example( $camisama_example ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML escapado en la función. ?>
<div class="camisama-print__more">
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
</div>
</div>
</section>

<?php
/**
 * Franja de estampados del catálogo que se desplaza sola (assets/js/home.js añade la pausa).
 *
 * La lista se repite para llenar pantallas anchas y se duplica una vez más para que el bucle no
 * tenga costura; las copias van ocultas a los lectores de pantalla. Con movimiento reducido se
 * queda quieta y se recorre deslizando.
 *
 * @package Camisama
 */

defined( 'ABSPATH' ) || exit;

$camisama_designs = camisama_home_designs();
if ( array() === $camisama_designs ) {
	return;
}

$camisama_category_names = array();
foreach ( ( new \Camisama\Customizer\Designs\DesignRepository() )->categories() as $camisama_category ) {
	$camisama_category_names[ $camisama_category['slug'] ] = $camisama_category['name'];
}

// Al menos 8 por vuelta, para que no quede un hueco en pantallas de 1920 px.
$camisama_count = count( $camisama_designs );
$camisama_loop  = array_merge( ...array_fill( 0, (int) ceil( 8 / $camisama_count ), $camisama_designs ) );
?>
<section id="estampados" class="camisama-designs alignfull" aria-labelledby="camisama-designs-title" data-camisama-designs>
<div class="camisama-home-wrap camisama-designs__head">
<h2 id="camisama-designs-title"><?php esc_html_e( 'Estampados listos para tu camiseta.', 'camisama' ); ?></h2>
<p><?php echo esc_html( sprintf( /* translators: %d: número de estampados. */ _n( '%d estampado del catálogo, con temas peruanos. Los eliges en el editor.', '%d estampados del catálogo, con temas peruanos. Los eliges en el editor.', $camisama_count, 'camisama' ), $camisama_count ) ); ?></p>
<button type="button" class="camisama-designs__pause" aria-pressed="false" aria-label="<?php esc_attr_e( 'Pausar los estampados', 'camisama' ); ?>" data-play-label="<?php esc_attr_e( 'Mover los estampados', 'camisama' ); ?>" hidden><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path class="camisama-live__icon-pause" d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z"/><path class="camisama-live__icon-play" d="M8 5.5v13l10.5-6.5z"/></svg></button>
</div>
<div class="camisama-designs__viewport">
<div class="camisama-designs__track">
<?php foreach ( array( false, true ) as $camisama_copy ) : ?>
<ul class="camisama-designs__list"<?php echo $camisama_copy ? ' aria-hidden="true"' : ''; ?>>
	<?php foreach ( $camisama_loop as $camisama_index => $camisama_design ) : ?>
		<?php
		$camisama_repeat   = $camisama_copy || $camisama_index >= $camisama_count;
		$camisama_category = $camisama_category_names[ $camisama_design['categories'][0] ?? '' ] ?? '';
		?>
<li class="camisama-designs__item"<?php echo $camisama_repeat && ! $camisama_copy ? ' aria-hidden="true"' : ''; ?>>
<span class="camisama-designs__art"><img src="<?php echo esc_url( $camisama_design['file']['url'] ); ?>" alt="" width="<?php echo esc_attr( (string) $camisama_design['file']['width'] ); ?>" height="<?php echo esc_attr( (string) $camisama_design['file']['height'] ); ?>" loading="lazy" decoding="async"></span>
<span class="camisama-designs__name"><?php echo esc_html( $camisama_design['title'] ); ?></span>
		<?php if ( $camisama_design['premium'] > 0 ) : ?>
<span class="camisama-designs__meta camisama-designs__meta--premium"><?php echo esc_html( sprintf( /* translators: %s: recargo premium. */ __( 'Premium + %s', 'camisama' ), html_entity_decode( wp_strip_all_tags( wc_price( $camisama_design['premium'] / 100 ) ), ENT_QUOTES, 'UTF-8' ) ) ); ?></span>
		<?php elseif ( '' !== $camisama_category ) : ?>
<span class="camisama-designs__meta"><?php echo esc_html( $camisama_category ); ?></span>
		<?php endif; ?>
</li>
	<?php endforeach; ?>
</ul>
<?php endforeach; ?>
</div>
</div>
</section>

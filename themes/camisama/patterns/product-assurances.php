<?php
/**
 * Title: Producto: garantías
 * Slug: camisama/product-assurances
 * Categories: woo-commerce
 * Inserter: no
 * Description: Puntos clave del servicio debajo del botón de compra.
 *
 * @package Camisama
 */

$camisama_assurances = array(
	__( 'Impresión DTF a todo color, también en prendas oscuras', 'camisama' ),
	__( 'Desde una sola unidad', 'camisama' ),
	__( 'Precios en soles, pago seguro', 'camisama' ),
);
?>
<!-- wp:list {"className":"is-style-camisama-checks","fontSize":"small"} -->
<ul class="wp-block-list is-style-camisama-checks has-small-font-size">
<?php foreach ( $camisama_assurances as $camisama_assurance ) : ?>
<!-- wp:list-item -->
<li><?php echo esc_html( $camisama_assurance ); ?></li>
<!-- /wp:list-item -->
<?php endforeach; ?>
</ul>
<!-- /wp:list -->

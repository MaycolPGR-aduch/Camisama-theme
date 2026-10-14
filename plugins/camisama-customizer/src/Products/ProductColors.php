<?php
/**
 * Colores de un producto.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Products;

/**
 * Los colores salen del atributo global "Color" (pa_color) del producto. Un producto sin colores
 * usa una sola maqueta por vista, con la clave "default".
 */
final class ProductColors {

	public const TAXONOMY = 'pa_color';
	public const DEFAULT  = 'default';

	/**
	 * Colores del producto, en el orden del atributo (Productos → Atributos → Color), como en la tienda.
	 *
	 * @param int $product_id ID del producto.
	 * @return list<array{slug: string, name: string, hex: string|null}>
	 */
	public static function for_product( int $product_id ): array {
		$terms = $product_id > 0 && taxonomy_exists( self::TAXONOMY ) ? wc_get_product_terms( $product_id, self::TAXONOMY ) : array();

		$colors = array();
		foreach ( $terms as $term ) {
			if ( $term instanceof \WP_Term ) {
				$colors[] = array(
					'slug' => $term->slug,
					'name' => $term->name,
					'hex'  => ColorSwatchField::hex( $term->term_id ),
				);
			}
		}

		if ( array() === $colors ) {
			$colors[] = array(
				'slug' => self::DEFAULT,
				'name' => __( 'Todos los colores', 'camisama-customizer' ),
				'hex'  => null,
			);
		}

		return $colors;
	}
}

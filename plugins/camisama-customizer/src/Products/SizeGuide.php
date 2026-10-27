<?php
/**
 * Guía de tallas de un producto: medidas de la prenda en cm.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Products;

/**
 * Medidas por talla (ancho de pecho de axila a axila y largo, con la prenda extendida).
 *
 * Las de DEFAULTS son de referencia, a la espera de las reales de cada modelo: un producto puede
 * sustituirlas con su meta _camisama_size_guide (JSON { "m": [ 50, 72 ], ... }) y toda la tienda
 * con el filtro camisama_size_guide. Solo se muestran las tallas que el producto tiene.
 */
final class SizeGuide {

	public const META = '_camisama_size_guide';

	private const TAXONOMY = 'pa_talla';

	/**
	 * Medidas de referencia: talla => [ ancho, largo ] en cm (las tallas numéricas de niños son
	 * claves enteras para PHP; se buscan igual con el slug en texto).
	 *
	 * @var array<int|string, array{int, int}>
	 */
	private const DEFAULTS = array(
		// Adulto, corte regular.
		's'   => array( 47, 69 ),
		'm'   => array( 50, 72 ),
		'l'   => array( 53, 74 ),
		'xl'  => array( 56, 76 ),
		'xxl' => array( 59, 78 ),
		// Niños (años).
		'4'   => array( 32, 45 ),
		'6'   => array( 35, 49 ),
		'8'   => array( 38, 53 ),
		'10'  => array( 41, 57 ),
		'12'  => array( 44, 61 ),
	);

	/**
	 * Guía de un producto, en el orden de sus tallas.
	 *
	 * @param int $product_id Producto.
	 * @return list<array{size: string, width_cm: float, length_cm: float}>
	 */
	public static function for_product( int $product_id ): array {
		$slugs = wc_get_product_terms( $product_id, self::TAXONOMY, array( 'fields' => 'slugs' ) );
		$meta  = json_decode( (string) get_post_meta( $product_id, self::META, true ), true );

		$rows = self::rows( array_map( 'strval', $slugs ), is_array( $meta ) ? $meta : array() );

		/**
		 * Medidas de la guía de tallas de un producto.
		 *
		 * @param list<array{size: string, width_cm: float, length_cm: float}> $rows       Filas.
		 * @param int                                                          $product_id Producto.
		 */
		return (array) apply_filters( 'camisama_size_guide', $rows, $product_id );
	}

	/**
	 * Filas de la guía: las medidas propias del producto mandan sobre las de referencia; una talla
	 * sin medidas válidas no aparece.
	 *
	 * @param list<string>         $slugs     Tallas del producto, en su orden.
	 * @param array<string, mixed> $overrides Medidas propias: talla => [ ancho, largo ].
	 * @return list<array{size: string, width_cm: float, length_cm: float}>
	 */
	public static function rows( array $slugs, array $overrides = array() ): array {
		$rows = array();
		foreach ( $slugs as $slug ) {
			$size = $overrides[ $slug ] ?? self::DEFAULTS[ $slug ] ?? null;
			if ( ! is_array( $size ) || ! isset( $size[0], $size[1] ) || ! is_numeric( $size[0] ) || ! is_numeric( $size[1] ) ) {
				continue;
			}
			$width  = (float) $size[0];
			$length = (float) $size[1];
			if ( $width <= 0 || $length <= 0 ) {
				continue;
			}
			$rows[] = array(
				'size'      => $slug,
				'width_cm'  => $width,
				'length_cm' => $length,
			);
		}

		return $rows;
	}
}

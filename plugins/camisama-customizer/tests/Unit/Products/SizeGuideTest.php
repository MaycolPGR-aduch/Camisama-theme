<?php
/**
 * Pruebas de SizeGuide.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Products;

use Camisama\Customizer\Products\SizeGuide;
use PHPUnit\Framework\TestCase;

/**
 * Filas de la guía de tallas.
 */
final class SizeGuideTest extends TestCase {

	/**
	 * Las tallas del producto, en su orden, con las medidas de referencia.
	 */
	public function test_uses_reference_sizes_in_product_order(): void {
		$rows = SizeGuide::rows( array( 'xl', 's' ) );

		$this->assertSame( array( 'xl', 's' ), array_column( $rows, 'size' ) );
		$this->assertSame( 56.0, $rows[0]['width_cm'] );
		$this->assertSame( 76.0, $rows[0]['length_cm'] );
	}

	/**
	 * Las medidas propias del producto mandan sobre las de referencia.
	 */
	public function test_product_overrides_win(): void {
		$rows = SizeGuide::rows( array( 'm' ), array( 'm' => array( 55, '74.5' ) ) );

		$this->assertSame(
			array(
				array(
					'size'      => 'm',
					'width_cm'  => 55.0,
					'length_cm' => 74.5,
				),
			),
			$rows
		);
	}

	/**
	 * Una talla sin medidas (o con medidas no válidas) no aparece.
	 */
	public function test_skips_sizes_without_valid_measurements(): void {
		$rows = SizeGuide::rows(
			array( 'm', 'unica', 'l', 'xl' ),
			array(
				'l'  => array( 0, 74 ),
				'xl' => 'mucho',
			)
		);

		$this->assertSame( array( 'm' ), array_column( $rows, 'size' ) );
	}

	/**
	 * Tallas de niños por edad.
	 */
	public function test_includes_kids_sizes(): void {
		$this->assertSame( array( '4', '12' ), array_column( SizeGuide::rows( array( '4', '12' ) ), 'size' ) );
	}
}

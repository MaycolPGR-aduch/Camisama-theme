<?php
/**
 * Pruebas de PngDpi y PrintPlan.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Production;

use Camisama\Customizer\Production\PngDpi;
use Camisama\Customizer\Production\PrintPlan;
use PHPUnit\Framework\TestCase;

/**
 * Archivos de impresión: resolución en el PNG y plan de impresión por zona.
 */
final class ProductionTest extends TestCase {

	private static function png( int $width = 40, int $height = 20 ): string {
		$image = imagecreatetruecolor( $width, $height );
		ob_start();
		imagepng( $image );

		return (string) ob_get_clean();
	}

	public function test_png_has_300_dpi_and_stays_valid(): void {
		$png = PngDpi::set( self::png(), 300 );

		self::assertSame( 300, PngDpi::read( $png ) );
		$info = getimagesizefromstring( $png );
		self::assertNotFalse( $info );
		self::assertSame( array( 40, 20 ), array( $info[0], $info[1] ) );
		self::assertNotFalse( imagecreatefromstring( $png ), 'las CRC del PNG siguen siendo correctas' );
	}

	public function test_replaces_an_existing_resolution(): void {
		$png = PngDpi::set( PngDpi::set( self::png(), 72 ), 300 );

		self::assertSame( 300, PngDpi::read( $png ) );
		self::assertSame( 1, substr_count( $png, 'pHYs' ), 'un solo bloque pHYs' );
	}

	/**
	 * GD guarda 96 DPI por defecto: sin corregirlo, el RIP imprimiría el diseño 3 veces más grande.
	 */
	public function test_replaces_gd_default_resolution(): void {
		$gd = self::png();

		self::assertNotSame( 300, PngDpi::read( $gd ) );
		self::assertSame( 300, PngDpi::read( PngDpi::set( $gd, 300 ) ) );
	}

	public function test_rejects_non_png(): void {
		$this->expectException( \InvalidArgumentException::class );
		PngDpi::set( 'GIF89a…', 300 );
	}

	public function test_print_plan_per_zone(): void {
		$plan = PrintPlan::for_document(
			array(
				'zones'    => array(
					array(
						'id'      => 'front',
						'label'   => 'Frente',
						'size_cm' => array(
							'w' => 30,
							'h' => 40,
						),
					),
					array(
						'id'      => 'back',
						'label'   => 'Espalda',
						'size_cm' => array(
							'w' => 30,
							'h' => 40,
						),
					),
				),
				'elements' => array(
					array(
						'zone'      => 'front',
						'cx'        => 7,
						'cy'        => 5,
						'width_cm'  => 6,
						'height_cm' => 6,
						'angle'     => 0,
					),
					array(
						'zone'      => 'front',
						'cx'        => 22,
						'cy'        => 15,
						'width_cm'  => 8,
						'height_cm' => 4,
						'angle'     => 0,
					),
				),
			)
		);

		self::assertCount( 1, $plan, 'la espalda vacía no se imprime' );
		self::assertSame( 'front', $plan[0]['zone'] );
		self::assertEquals( 22.0, $plan[0]['width_cm'] );
		self::assertEquals( 15.0, $plan[0]['height_cm'] );
		self::assertEquals( 4.0, $plan[0]['left_cm'] );
		self::assertEquals( 2.0, $plan[0]['top_cm'] );
		self::assertSame( 2599, $plan[0]['width_px'], '22 cm a 300 DPI' );
	}
}

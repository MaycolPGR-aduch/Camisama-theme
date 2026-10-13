<?php
/**
 * Pruebas de PrintResolution.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Production;

use Camisama\Customizer\Production\PrintResolution;
use Camisama\Customizer\Production\ResolutionQuality;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Conversión cm ↔ px y clasificación de calidad.
 */
final class PrintResolutionTest extends TestCase {

	/**
	 * Medidas de las zonas estándar a 300 DPI.
	 *
	 * @return array<string, array{float, int}>
	 */
	public static function zone_sizes(): array {
		return array(
			'frente/espalda ancho 30 cm' => array( 30.0, 3544 ),
			'frente/espalda alto 40 cm'  => array( 40.0, 4725 ),
			'manga 9 cm'                 => array( 9.0, 1063 ),
			'una pulgada'                => array( 2.54, 300 ),
		);
	}

	/**
	 * Convierte centímetros a píxeles a la resolución de producción.
	 *
	 * @param float $cm       Medida en centímetros.
	 * @param int   $expected Píxeles esperados.
	 */
	#[DataProvider( 'zone_sizes' )]
	public function test_cm_to_px_at_target_dpi( float $cm, int $expected ): void {
		self::assertSame( $expected, PrintResolution::cm_to_px( $cm ) );
	}

	public function test_effective_dpi(): void {
		self::assertEqualsWithDelta( 150.0, PrintResolution::effective_dpi( 1500, 25.4 ), 0.001 );
	}

	public function test_effective_dpi_rejects_non_positive_size(): void {
		$this->expectException( \InvalidArgumentException::class );
		PrintResolution::effective_dpi( 1000, 0.0 );
	}

	public function test_generated_size_is_always_optimal(): void {
		foreach ( array( 9.0, 10.0, 28.5, 30.0, 40.0 ) as $cm ) {
			self::assertSame( ResolutionQuality::Optimal, PrintResolution::quality( PrintResolution::cm_to_px( $cm ), $cm ), "{$cm} cm" );
		}
	}

	public function test_quality_thresholds(): void {
		self::assertSame( ResolutionQuality::Optimal, PrintResolution::quality( 3600, 30.0 ) );
		self::assertSame( ResolutionQuality::Acceptable, PrintResolution::quality( 1772, 30.0 ) );
		self::assertSame( ResolutionQuality::Low, PrintResolution::quality( 1700, 30.0 ) );
	}
}

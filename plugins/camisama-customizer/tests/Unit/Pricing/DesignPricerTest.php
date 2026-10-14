<?php
/**
 * Pruebas de DesignPricer con los casos compartidos con el editor JS.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Pricing;

use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Config\Rect;
use Camisama\Customizer\Config\View;
use Camisama\Customizer\Config\Zone;
use Camisama\Customizer\Design\Element;
use Camisama\Customizer\Pricing\DesignPricer;
use Camisama\Customizer\Pricing\PriceTable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * El mismo archivo de casos prueba también client/customizer/pricing.js.
 */
final class DesignPricerTest extends TestCase {

	/**
	 * Casos de tests/fixtures/pricing-cases.json.
	 *
	 * @return array<string, array{array<string, mixed>}>
	 */
	public static function cases(): array {
		$cases = array();
		foreach ( self::fixture()['cases'] as $case ) {
			$cases[ $case['name'] ] = array( $case );
		}

		return $cases;
	}

	/**
	 * Calcula el desglose esperado.
	 *
	 * @param array<string, mixed> $test_case Caso del archivo compartido.
	 */
	#[DataProvider( 'cases' )]
	public function test_shared_case( array $test_case ): void {
		$fixture  = self::fixture();
		$pricer   = new DesignPricer( PriceTable::from_array( $fixture['tiers'] ) );
		$elements = array_map( static fn ( array $data ): Element => Element::from_array( $data ), $test_case['elements'] );
		$premiums = array();
		foreach ( $fixture['premiums'] as $id => $amount ) {
			$premiums[ (int) $id ] = (int) $amount;
		}

		if ( ! empty( $test_case['error'] ) ) {
			$this->expectException( \InvalidArgumentException::class );
		}

		$quote = $pricer->quote( self::config( $fixture['zones'] ), $elements, $premiums );

		self::assertSame(
			$test_case['expected']['zones'],
			array_map(
				static fn ( array $zone ): array => array(
					'zone'   => $zone['zone'],
					'tier'   => $zone['tier'],
					'amount' => $zone['amount'],
				),
				$quote['zones']
			)
		);
		self::assertSame( $test_case['expected']['premiums'] ?? array(), $quote['premiums'] );
		self::assertSame( $test_case['expected']['total'], $quote['total'] );
	}

	public function test_bounds_of_rotated_element(): void {
		$bounds = ( new Element( 'front', 'text', 10, 10, 4, 2, 90 ) )->bounds();

		self::assertEqualsWithDelta( 9.0, $bounds['left'], 0.0001 );
		self::assertEqualsWithDelta( 8.0, $bounds['top'], 0.0001 );
		self::assertEqualsWithDelta( 11.0, $bounds['right'], 0.0001 );
		self::assertEqualsWithDelta( 12.0, $bounds['bottom'], 0.0001 );
	}

	public function test_design_element_needs_a_catalog_design(): void {
		$this->expectException( \InvalidArgumentException::class );
		new Element( 'front', 'design', 1, 1, 1, 1 );
	}

	/**
	 * Configuración con las zonas del archivo de casos.
	 *
	 * @param list<array<string, mixed>> $zones Zonas.
	 */
	private static function config( array $zones ): ProductConfig {
		return new ProductConfig(
			true,
			array( new View( 'front', 'Frente' ), new View( 'back', 'Espalda' ) ),
			array_map(
				static fn ( array $zone ): Zone => new Zone( $zone['id'], $zone['label'], $zone['view'], new Rect( 0.1, 0.1, 0.2, 0.2 ), (float) $zone['size_cm']['w'], (float) $zone['size_cm']['h'] ),
				$zones
			)
		);
	}

	/**
	 * Contenido del archivo de casos.
	 *
	 * @return array<string, mixed>
	 */
	private static function fixture(): array {
		return json_decode( (string) file_get_contents( dirname( __DIR__, 2 ) . '/fixtures/pricing-cases.json' ), true, 512, JSON_THROW_ON_ERROR ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
}

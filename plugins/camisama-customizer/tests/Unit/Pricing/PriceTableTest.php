<?php
/**
 * Pruebas de PriceTable.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Pricing;

use Camisama\Customizer\Pricing\PriceTable;
use Camisama\Customizer\Pricing\PriceTier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Escalones de precio por tamaño.
 */
final class PriceTableTest extends TestCase {

	/**
	 * Lado mayor del diseño => escalón esperado (null si supera el máximo).
	 *
	 * @return array<string, array{float, ?string}>
	 */
	public static function sizes(): array {
		return array(
			'logo de 4 cm'                => array( 4.0, 'Pequeño' ),
			'justo 10 cm'                 => array( 10.0, 'Pequeño' ),
			'10 cm con error de redondeo' => array( 10.0000004, 'Pequeño' ),
			'10.5 cm'                     => array( 10.5, 'Mediano' ),
			'frente completo 40 cm'       => array( 40.0, 'Máximo' ),
			'más grande que la tabla'     => array( 41.0, null ),
			'vacío'                       => array( 0.0, null ),
		);
	}

	/**
	 * Elige el escalón según el lado mayor.
	 *
	 * @param float       $cm       Lado mayor del diseño.
	 * @param string|null $expected Escalón esperado.
	 */
	#[DataProvider( 'sizes' )]
	public function test_tier_for( float $cm, ?string $expected ): void {
		self::assertSame( $expected, PriceTable::defaults()->tier_for( $cm )?->label );
	}

	public function test_tiers_are_sorted_whatever_the_input_order(): void {
		$table = new PriceTable(
			array(
				new PriceTier( 'Grande', 30.0, 2000 ),
				new PriceTier( 'Pequeño', 10.0, 800 ),
			)
		);

		self::assertSame( array( 'Pequeño', 'Grande' ), array_map( static fn ( PriceTier $t ): string => $t->label, $table->tiers() ) );
	}

	public function test_sleeve_only_reaches_the_small_tier(): void {
		$labels = array_map( static fn ( PriceTier $t ): string => $t->label, PriceTable::defaults()->tiers_within( 9.0 ) );

		self::assertSame( array( 'Pequeño' ), $labels );
	}

	public function test_front_reaches_every_tier(): void {
		self::assertCount( 4, PriceTable::defaults()->tiers_within( 40.0 ) );
	}

	public function test_zone_just_over_a_tier_reaches_the_next_one(): void {
		self::assertCount( 2, PriceTable::defaults()->tiers_within( 10.5 ) );
	}

	public function test_round_trip_through_array(): void {
		$table = PriceTable::defaults();

		self::assertEquals( $table, PriceTable::from_array( $table->to_array() ) );
	}

	public function test_rejects_empty_table(): void {
		$this->expectException( \InvalidArgumentException::class );
		new PriceTable( array() );
	}

	public function test_rejects_duplicated_sizes(): void {
		$this->expectException( \InvalidArgumentException::class );
		new PriceTable( array( new PriceTier( 'A', 10.0, 100 ), new PriceTier( 'B', 10.0, 200 ) ) );
	}

	public function test_rejects_negative_amount(): void {
		$this->expectException( \InvalidArgumentException::class );
		new PriceTier( 'A', 10.0, -1 );
	}
}

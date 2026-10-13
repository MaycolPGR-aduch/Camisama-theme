<?php
/**
 * Pruebas de Money.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Pricing;

use Camisama\Customizer\Pricing\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Importes de formulario a céntimos y vuelta.
 */
final class MoneyTest extends TestCase {

	/**
	 * Texto escrito => céntimos esperados.
	 *
	 * @return array<string, array{string, ?int}>
	 */
	public static function amounts(): array {
		return array(
			'entero'             => array( '8', 800 ),
			'un decimal'         => array( '12.5', 1250 ),
			'dos decimales'      => array( '39.90', 3990 ),
			'coma decimal'       => array( '5,50', 550 ),
			'espacios'           => array( ' 20 ', 2000 ),
			'cero'               => array( '0', 0 ),
			'tres decimales'     => array( '1.999', null ),
			'negativo'           => array( '-5', null ),
			'texto'              => array( 'cinco', null ),
			'vacío'              => array( '', null ),
			'separador de miles' => array( '1,000.00', null ),
		);
	}

	/**
	 * Convierte importes válidos y rechaza el resto.
	 *
	 * @param string   $input    Importe escrito.
	 * @param int|null $expected Céntimos esperados.
	 */
	#[DataProvider( 'amounts' )]
	public function test_to_cents( string $input, ?int $expected ): void {
		self::assertSame( $expected, Money::to_cents( $input ) );
	}

	public function test_to_decimal(): void {
		self::assertSame( '39.90', Money::to_decimal( 3990 ) );
		self::assertSame( '0.05', Money::to_decimal( 5 ) );
	}
}

<?php
/**
 * Pruebas de DeliveryRates.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Checkout;

use Camisama\Customizer\Checkout\DeliveryRates;
use Camisama\Customizer\Checkout\Ubigeo;
use PHPUnit\Framework\TestCase;

/**
 * Tarifas de envío por distrito.
 */
final class DeliveryRatesTest extends TestCase {

	/**
	 * Todos los distritos con envío tienen tarifa, y ninguno más.
	 */
	public function test_every_delivery_district_has_a_rate(): void {
		$tree = json_decode( (string) file_get_contents( Ubigeo::file() ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Archivo local.
		$with = 0;

		foreach ( $tree as $department ) {
			foreach ( $department[2] as $province ) {
				foreach ( $province[2] as $district ) {
					$rate = DeliveryRates::cents( $district[0] );
					if ( Ubigeo::delivers( $district[0] ) ) {
						$this->assertNotNull( $rate, "Distrito {$district[0]}" );
						++$with;
					} else {
						$this->assertNull( $rate, "Distrito {$district[0]}" );
					}
				}
			}
		}

		$this->assertSame( 51, $with );
	}

	/**
	 * Cada franja cobra su tarifa.
	 */
	public function test_bands(): void {
		$this->assertSame( 1000, DeliveryRates::cents( '150122' ) );
		$this->assertSame( 1400, DeliveryRates::cents( '070102' ) );
		$this->assertSame( 2000, DeliveryRates::cents( '070106' ) );
		$this->assertSame( 2500, DeliveryRates::cents( '150129' ) );
		$this->assertNull( DeliveryRates::cents( '040101' ) );
		$this->assertSame( 1000, DeliveryRates::lowest() );
	}

	/**
	 * Del nombre y la región del destino sale el distrito, sin confundirlo con otro de fuera de la zona.
	 */
	public function test_finds_the_district_of_a_destination(): void {
		$this->assertSame( '150122', Ubigeo::delivery_code( 'Miraflores', 'LMA' ) );
		$this->assertSame( '070106', Ubigeo::delivery_code( 'Ventanilla', 'CAL' ) );
		$this->assertNull( Ubigeo::delivery_code( 'Miraflores', 'ARE' ) );
		$this->assertNull( Ubigeo::delivery_code( 'Bellavista', 'SAM' ) );
		$this->assertNull( Ubigeo::delivery_code( '', '' ) );
	}
}

<?php
/**
 * Pruebas de Delivery.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Checkout;

use Camisama\Customizer\Checkout\Delivery;
use PHPUnit\Framework\TestCase;

/**
 * Campos de la dirección en Perú.
 */
final class DeliveryTest extends TestCase {

	/**
	 * Perú pide distrito y referencia, y no región, ciudad ni código postal.
	 */
	public function test_peru_asks_for_district_and_reference(): void {
		$peru = Delivery::peru_fields( array() )['PE'];

		$this->assertArrayHasKey( Delivery::FIELD, $peru );
		$this->assertSame( 'Referencia', $peru['address_2']['label'] );
		foreach ( array( 'city', 'state', 'postcode' ) as $field ) {
			$this->assertTrue( $peru[ $field ]['hidden'] );
			$this->assertFalse( $peru[ $field ]['required'] );
		}
	}

	/**
	 * Los demás países no se tocan.
	 */
	public function test_leaves_other_countries_alone(): void {
		$other = array( 'CL' => array( 'state' => array( 'label' => 'Región' ) ) );

		$this->assertSame( $other['CL'], Delivery::peru_fields( $other )['CL'] );
	}

	/**
	 * Del distrito salen la ciudad y la región, también fuera de la zona de envíos (facturación).
	 */
	public function test_place_comes_from_the_district(): void {
		$this->assertSame(
			array(
				'city'  => 'Miraflores',
				'state' => 'LMA',
			),
			Delivery::place( array( Delivery::FIELD => '150122' ) )
		);
		$this->assertSame(
			array(
				'city'  => 'Ventanilla',
				'state' => 'CAL',
			),
			Delivery::place( array( Delivery::FIELD => '070106' ) )
		);
		$this->assertSame(
			array(
				'city'  => 'Arequipa',
				'state' => 'ARE',
			),
			Delivery::place( array( Delivery::FIELD => '040101' ) )
		);
	}

	/**
	 * Sin distrito, o con un código que no lo es, no hay nada que aplicar.
	 */
	public function test_no_place_without_a_known_district(): void {
		$this->assertNull( Delivery::place( array() ) );
		$this->assertNull( Delivery::place( null ) );
		$this->assertNull( Delivery::place( array( Delivery::FIELD => '999999' ) ) );
	}

	/**
	 * El servidor solo acepta distritos reales; el vacío lo rechaza WooCommerce por obligatorio.
	 */
	public function test_validate_accepts_real_districts_only(): void {
		$this->assertTrue( Delivery::validate( '040101' ) );
		$this->assertTrue( Delivery::validate( '' ) );
	}
}

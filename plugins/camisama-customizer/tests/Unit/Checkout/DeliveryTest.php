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
	 * Perú pide distrito y referencia, y no región ni código postal.
	 */
	public function test_peru_asks_for_district_and_reference(): void {
		$peru = Delivery::peru_fields( array() )['PE'];

		$this->assertSame( 'Distrito', $peru['city']['label'] );
		$this->assertSame( 'Referencia', $peru['address_2']['label'] );
		$this->assertTrue( $peru['state']['hidden'] );
		$this->assertFalse( $peru['state']['required'] );
		$this->assertTrue( $peru['postcode']['hidden'] );
		$this->assertFalse( $peru['postcode']['required'] );
	}

	/**
	 * Los demás países no se tocan.
	 */
	public function test_leaves_other_countries_alone(): void {
		$other = array( 'CL' => array( 'state' => array( 'label' => 'Región' ) ) );

		$this->assertSame( $other['CL'], Delivery::peru_fields( $other )['CL'] );
	}
}

<?php
/**
 * Pruebas de Ubigeo.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Checkout;

use Camisama\Customizer\Checkout\Ubigeo;
use PHPUnit\Framework\TestCase;

/**
 * Distritos del Perú y zona de envíos.
 */
final class UbigeoTest extends TestCase {

	/**
	 * El archivo tiene los 25 departamentos, 196 provincias y 1892 distritos, cada uno con su código.
	 */
	public function test_data_file_is_complete(): void {
		$tree = json_decode( (string) file_get_contents( Ubigeo::file() ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Archivo local.

		$provinces = array_merge( ...array_column( $tree, 2 ) );
		$districts = array_merge( ...array_column( $provinces, 2 ) );

		$this->assertCount( 25, $tree );
		$this->assertCount( 196, $provinces );
		$this->assertCount( 1892, $districts );
		$this->assertCount( 1892, array_unique( array_column( $districts, 0 ) ) );
		foreach ( $tree as $department ) {
			foreach ( $department[2] as $province ) {
				$this->assertStringStartsWith( $department[0], $province[0] );
				foreach ( $province[2] as $district ) {
					$this->assertStringStartsWith( $province[0], $district[0] );
				}
			}
		}
	}

	/**
	 * Del código salen el nombre y la región; Lima Metropolitana y el resto de Lima son distintas.
	 */
	public function test_resolves_name_and_state(): void {
		$this->assertSame( 'Miraflores', Ubigeo::name( '150122' ) );
		$this->assertSame( 'LMA', Ubigeo::state( '150122' ) );
		$this->assertSame( 'CAL', Ubigeo::state( '070106' ) );
		$this->assertSame( 'ARE', Ubigeo::state( '040101' ) );
		$this->assertSame( 'LIM', Ubigeo::state( '150501' ) );
	}

	/**
	 * Un código que no es de un distrito no tiene nombre.
	 */
	public function test_unknown_code_has_no_name(): void {
		$this->assertNull( Ubigeo::name( '999999' ) );
		$this->assertNull( Ubigeo::name( '1501' ) );
		$this->assertNull( Ubigeo::name( '' ) );
	}

	/**
	 * Los envíos llegan a Lima Metropolitana y el Callao, no al resto del departamento de Lima.
	 */
	public function test_delivery_zone(): void {
		$this->assertTrue( Ubigeo::delivers( '150122' ) );
		$this->assertTrue( Ubigeo::delivers( '070106' ) );
		$this->assertFalse( Ubigeo::delivers( '150501' ) );
		$this->assertFalse( Ubigeo::delivers( '040101' ) );
		$this->assertFalse( Ubigeo::delivers( '1501' ) );
		$this->assertTrue( Ubigeo::state_delivers( Ubigeo::state( '150122' ) ) );
		$this->assertTrue( Ubigeo::state_delivers( Ubigeo::state( '070106' ) ) );
		$this->assertFalse( Ubigeo::state_delivers( Ubigeo::state( '150501' ) ) );
		$this->assertFalse( Ubigeo::state_delivers( Ubigeo::state( '040101' ) ) );
	}
}

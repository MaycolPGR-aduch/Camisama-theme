<?php
/**
 * Pruebas de la configuración de producto.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Config;

use Camisama\Customizer\Config\Presets;
use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Config\Rect;
use Camisama\Customizer\Config\View;
use Camisama\Customizer\Config\Zone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Validación y serialización de vistas y zonas.
 */
final class ProductConfigTest extends TestCase {

	public function test_presets_are_valid_and_round_trip(): void {
		foreach ( array_keys( Presets::available() ) as $id ) {
			$config = Presets::get( $id );

			self::assertTrue( $config->enabled );
			self::assertCount( 2, $config->views );
			self::assertCount( 5, $config->zones );
			self::assertEquals( $config->to_array(), ProductConfig::from_array( $config->to_array() )->to_array() );
		}
	}

	public function test_zones_by_view(): void {
		$config = Presets::adult();

		self::assertSame( array( 'front', 'left-sleeve', 'right-sleeve' ), array_map( static fn ( Zone $z ): string => $z->id, $config->zones_in( 'front' ) ) );
		self::assertSame( 'Nuca', $config->zone( 'nape' )?->label );
		self::assertNull( $config->zone( 'pocket' ) );
	}

	public function test_mockups_survive_round_trip(): void {
		$data                        = Presets::adult()->to_array();
		$data['views'][0]['mockups'] = array(
			'blanco' => 10,
			'negro'  => '11',
		);
		$config                      = ProductConfig::from_array( $data );

		self::assertSame(
			array(
				'blanco' => 10,
				'negro'  => 11,
			),
			$config->views[0]->mockups
		);
	}

	public function test_missing_data_means_disabled_config(): void {
		$config = ProductConfig::from_array( array() );

		self::assertFalse( $config->enabled );
		self::assertSame( array(), $config->zones );
	}

	/**
	 * Configuraciones que deben rechazarse.
	 *
	 * @return array<string, array{callable(): mixed}>
	 */
	public static function invalid(): array {
		$front = new View( 'front', 'Frente' );
		$rect  = new Rect( 0.1, 0.1, 0.2, 0.2 );

		return array(
			'zona en vista inexistente'    => array( static fn () => new ProductConfig( true, array( $front ), array( new Zone( 'z', 'Z', 'back', $rect, 10, 10 ) ) ) ),
			'vistas repetidas'             => array( static fn () => new ProductConfig( false, array( $front, $front ), array() ) ),
			'zonas repetidas'              => array( static fn () => new ProductConfig( true, array( $front ), array( new Zone( 'z', 'Z', 'front', $rect, 10, 10 ), new Zone( 'z', 'Z2', 'front', $rect, 10, 10 ) ) ) ),
			'activado sin zonas'           => array( static fn () => new ProductConfig( true, array( $front ), array() ) ),
			'zona fuera de la imagen'      => array( static fn () => new Rect( 0.9, 0.1, 0.2, 0.2 ) ),
			'zona sin tamaño'              => array( static fn () => new Rect( 0.1, 0.1, 0.0, 0.2 ) ),
			'zona de más de 1 m'           => array( static fn () => new Zone( 'z', 'Z', 'front', $rect, 120, 10 ) ),
			'identificador con espacios'   => array( static fn () => new Zone( 'mi zona', 'Z', 'front', $rect, 10, 10 ) ),
			'identificador con mayúsculas' => array( static fn () => new View( 'Front', 'Frente' ) ),
			'maqueta con ID no válido'     => array( static fn () => new View( 'front', 'Frente', array( 'blanco' => 0 ) ) ),
			'zona sin nombre'              => array( static fn () => Zone::from_array( array( 'id' => 'z' ) ) ),
		);
	}

	/**
	 * Rechaza configuraciones incoherentes con un mensaje claro.
	 *
	 * @param callable(): mixed $build Construye el objeto inválido.
	 */
	#[DataProvider( 'invalid' )]
	public function test_rejects_invalid_config( callable $build ): void {
		$this->expectException( \InvalidArgumentException::class );
		$build();
	}
}

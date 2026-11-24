<?php
/**
 * Pruebas de las plantillas fotográficas (assets/customizer/modelos/v1).
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Config;

use Camisama\Customizer\Config\Presets;
use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Config\Zone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * El manifiesto que importa bin/import-fotos-modelos.php.
 */
final class PhotoTemplatesTest extends TestCase {

	/**
	 * Carpeta de las plantillas.
	 */
	private const ROOT = __DIR__ . '/../../../../../assets/customizer/modelos/v1/';

	/**
	 * Modelos del manifiesto.
	 *
	 * @return array<string, array{array<string, mixed>, string}>
	 */
	public static function models(): array {
		$manifest = json_decode( (string) file_get_contents( self::ROOT . 'manifest.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Archivo local versionado.
		$models   = array();
		foreach ( $manifest['models'] as $slug => $model ) {
			$models[ $slug ] = array( $model, 'ninos' === $slug ? 'kids' : 'adult' );
		}

		return $models;
	}

	/**
	 * Cada color tiene frente y espalda, y los archivos son los del manifiesto.
	 *
	 * @param array<string, mixed> $model Modelo.
	 */
	#[DataProvider( 'models' )]
	public function test_images_match_the_manifest( array $model ): void {
		$views = array();
		foreach ( $model['images'] as $image ) {
			$views[ $image['color'] ][] = $image['view'];
			foreach ( array( 'mockup', 'photo' ) as $kind ) {
				$this->assertSame( $image[ $kind ]['sha256'], hash_file( 'sha256', self::ROOT . $image[ $kind ]['file'] ), $image[ $kind ]['file'] );
			}
		}
		foreach ( $views as $color => $list ) {
			sort( $list );
			$this->assertSame( array( 'back', 'front' ), $list, (string) $color );
		}
	}

	/**
	 * Las zonas son las de la plantilla (los diseños guardados se apoyan en sus centímetros), con la
	 * proporción de sus medidas y la misma escala en toda la prenda.
	 *
	 * @param array<string, mixed> $model  Modelo.
	 * @param string               $preset Plantilla.
	 */
	#[DataProvider( 'models' )]
	public function test_zones_keep_sizes_and_scale( array $model, string $preset ): void {
		$config = ProductConfig::from_array(
			array(
				'enabled' => true,
				'views'   => array(
					array(
						'id'      => 'front',
						'label'   => 'Frente',
						'mockups' => array(),
					),
					array(
						'id'      => 'back',
						'label'   => 'Espalda',
						'mockups' => array(),
					),
				),
				'zones'   => $model['zones'],
			)
		);
		$sizes  = static fn ( Zone $zone ): string => "{$zone->id} {$zone->width_cm}x{$zone->height_cm}";
		$this->assertSame( array_map( $sizes, Presets::get( $preset )->zones ), array_map( $sizes, $config->zones ) );

		$scale = $config->zones[0]->rect->w / $config->zones[0]->width_cm;
		foreach ( $config->zones as $zone ) {
			$this->assertEqualsWithDelta( $scale, $zone->rect->w / $zone->width_cm, 0.00002, $zone->id );
			$this->assertEqualsWithDelta( $scale, $zone->rect->h / $zone->height_cm, 0.00002, $zone->id );
		}
	}
}

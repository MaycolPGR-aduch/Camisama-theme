<?php
/**
 * Plantillas de vistas y zonas.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Config;

/**
 * Configuraciones de partida para camisetas, con las medidas de docs/produccion-e-impresion.md.
 *
 * Las posiciones están calculadas para una maqueta cuadrada con la prenda centrada; con fotos reales
 * se ajustan en el editor visual del producto.
 */
final class Presets {

	/**
	 * Plantillas disponibles: id => nombre visible.
	 *
	 * @return array<string, string>
	 */
	public static function available(): array {
		return array(
			'adult' => 'Camiseta adulto',
			'kids'  => 'Camiseta niños',
		);
	}

	/**
	 * Plantilla por identificador.
	 *
	 * @param string $id Identificador de available().
	 *
	 * @throws \InvalidArgumentException Si no existe.
	 */
	public static function get( string $id ): ProductConfig {
		return match ( $id ) {
			'adult' => self::adult(),
			'kids'  => self::kids(),
			default => throw new \InvalidArgumentException( "No existe la plantilla \"{$id}\"." ),
		};
	}

	/**
	 * Camiseta adulto: frente y espalda 30 × 40 cm, mangas 9 × 9 cm, nuca 8 × 5 cm.
	 */
	public static function adult(): ProductConfig {
		return self::build(
			array(
				'front'        => array( new Rect( 0.3798, 0.3333, 0.2404, 0.3205 ), 30.0, 40.0 ),
				'left-sleeve'  => array( new Rect( 0.7056, 0.3556, 0.0721, 0.0721 ), 9.0, 9.0 ),
				'right-sleeve' => array( new Rect( 0.2222, 0.3556, 0.0721, 0.0721 ), 9.0, 9.0 ),
				'back'         => array( new Rect( 0.3798, 0.3333, 0.2404, 0.3205 ), 30.0, 40.0 ),
				'nape'         => array( new Rect( 0.4679, 0.2883, 0.0641, 0.0401 ), 8.0, 5.0 ),
			)
		);
	}

	/**
	 * Camiseta niños: frente y espalda 22 × 28 cm, mangas 7 × 7 cm, nuca 6 × 4 cm.
	 */
	public static function kids(): ProductConfig {
		return self::build(
			array(
				'front'        => array( new Rect( 0.4072, 0.3667, 0.1856, 0.2363 ), 22.0, 28.0 ),
				'left-sleeve'  => array( new Rect( 0.6638, 0.3838, 0.0591, 0.0591 ), 7.0, 7.0 ),
				'right-sleeve' => array( new Rect( 0.2771, 0.3838, 0.0591, 0.0591 ), 7.0, 7.0 ),
				'back'         => array( new Rect( 0.4072, 0.3667, 0.1856, 0.2363 ), 22.0, 28.0 ),
				'nape'         => array( new Rect( 0.4747, 0.3307, 0.0507, 0.0338 ), 6.0, 4.0 ),
			)
		);
	}

	/**
	 * Construye la configuración con las vistas Frente/Espalda y las zonas estándar.
	 *
	 * @param array<string, array{Rect, float, float}> $zones Zona => [rectángulo, ancho cm, alto cm].
	 */
	private static function build( array $zones ): ProductConfig {
		$meta = array(
			'front'        => array( 'Frente', 'front' ),
			'left-sleeve'  => array( 'Manga izquierda', 'front' ),
			'right-sleeve' => array( 'Manga derecha', 'front' ),
			'back'         => array( 'Espalda', 'back' ),
			'nape'         => array( 'Nuca', 'back' ),
		);

		$list = array();
		foreach ( $zones as $id => list( $rect, $width_cm, $height_cm ) ) {
			$list[] = new Zone( $id, $meta[ $id ][0], $meta[ $id ][1], $rect, $width_cm, $height_cm );
		}

		return new ProductConfig(
			true,
			array( new View( 'front', 'Frente' ), new View( 'back', 'Espalda' ) ),
			$list
		);
	}
}

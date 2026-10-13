<?php
/**
 * Rectángulo relativo a la imagen de una vista.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Config;

/**
 * Posición y tamaño como fracción (0–1) del ancho y alto de la maqueta, para que no dependa de su resolución.
 */
final class Rect {

	private const EPSILON = 0.0001;

	/**
	 * Constructor.
	 *
	 * @param float $x Borde izquierdo (fracción del ancho).
	 * @param float $y Borde superior (fracción del alto).
	 * @param float $w Ancho (fracción del ancho).
	 * @param float $h Alto (fracción del alto).
	 *
	 * @throws \InvalidArgumentException Si el rectángulo sale de la imagen o no tiene tamaño.
	 */
	public function __construct(
		public readonly float $x,
		public readonly float $y,
		public readonly float $w,
		public readonly float $h,
	) {
		if ( $w <= 0.0 || $h <= 0.0 ) {
			throw new \InvalidArgumentException( 'La zona debe tener ancho y alto.' );
		}
		if ( $x < -self::EPSILON || $y < -self::EPSILON || $x + $w > 1 + self::EPSILON || $y + $h > 1 + self::EPSILON ) {
			throw new \InvalidArgumentException( 'La zona se sale de la imagen.' );
		}
	}

	/**
	 * Crea el rectángulo desde un array.
	 *
	 * @param array<string, mixed> $data Claves x, y, w, h.
	 *
	 * @throws \InvalidArgumentException Si faltan valores o no son válidos.
	 */
	public static function from_array( array $data ): self {
		foreach ( array( 'x', 'y', 'w', 'h' ) as $key ) {
			if ( ! isset( $data[ $key ] ) || ! is_numeric( $data[ $key ] ) ) {
				throw new \InvalidArgumentException( 'Posición de la zona incompleta.' );
			}
		}

		return new self( (float) $data['x'], (float) $data['y'], (float) $data['w'], (float) $data['h'] );
	}

	/**
	 * Representación serializable, redondeada a 4 decimales (0,01 % de la imagen).
	 *
	 * @return array{x: float, y: float, w: float, h: float}
	 */
	public function to_array(): array {
		return array(
			'x' => round( $this->x, 4 ),
			'y' => round( $this->y, 4 ),
			'w' => round( $this->w, 4 ),
			'h' => round( $this->h, 4 ),
		);
	}
}

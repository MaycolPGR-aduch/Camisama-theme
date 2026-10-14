<?php
/**
 * Elemento colocado por el cliente en una zona.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Design;

/**
 * Estampado, texto o imagen dentro de una zona. Las medidas van en cm y en el sistema de
 * coordenadas de la zona (origen en su esquina superior izquierda), así no dependen del tamaño
 * con que se dibuja el editor.
 */
final class Element {

	public const KINDS = array( 'design', 'text', 'image' );

	/**
	 * Constructor.
	 *
	 * @param string   $zone      Zona en la que está.
	 * @param string   $kind      design, text o image.
	 * @param float    $cx        Centro horizontal (cm).
	 * @param float    $cy        Centro vertical (cm).
	 * @param float    $width_cm  Ancho sin rotar (cm).
	 * @param float    $height_cm Alto sin rotar (cm).
	 * @param float    $angle     Rotación en grados.
	 * @param int|null $design_id Estampado del catálogo, si es de tipo design.
	 *
	 * @throws \InvalidArgumentException Si algún valor no es válido.
	 */
	public function __construct(
		public readonly string $zone,
		public readonly string $kind,
		public readonly float $cx,
		public readonly float $cy,
		public readonly float $width_cm,
		public readonly float $height_cm,
		public readonly float $angle = 0.0,
		public readonly ?int $design_id = null,
	) {
		if ( ! in_array( $kind, self::KINDS, true ) ) {
			throw new \InvalidArgumentException( "Tipo de elemento no válido: \"{$kind}\"." );
		}
		if ( $width_cm <= 0.0 || $height_cm <= 0.0 ) {
			throw new \InvalidArgumentException( 'Cada elemento del diseño debe tener ancho y alto.' );
		}
		if ( 'design' === $kind && ( null === $design_id || $design_id <= 0 ) ) {
			throw new \InvalidArgumentException( 'Falta el estampado del catálogo.' );
		}
	}

	/**
	 * Crea el elemento desde un array (JSON del editor).
	 *
	 * @param array<string, mixed> $data Datos del elemento.
	 *
	 * @throws \InvalidArgumentException Si faltan datos o no son válidos.
	 */
	public static function from_array( array $data ): self {
		foreach ( array( 'cx', 'cy', 'width_cm', 'height_cm' ) as $key ) {
			if ( ! isset( $data[ $key ] ) || ! is_numeric( $data[ $key ] ) ) {
				throw new \InvalidArgumentException( 'Elemento del diseño incompleto.' );
			}
		}

		return new self(
			(string) ( $data['zone'] ?? '' ),
			(string) ( $data['kind'] ?? '' ),
			(float) $data['cx'],
			(float) $data['cy'],
			(float) $data['width_cm'],
			(float) $data['height_cm'],
			is_numeric( $data['angle'] ?? null ) ? (float) $data['angle'] : 0.0,
			isset( $data['design_id'] ) && is_numeric( $data['design_id'] ) ? (int) $data['design_id'] : null,
		);
	}

	/**
	 * Rectángulo alineado con los ejes que ocupa el elemento rotado, en cm de la zona.
	 *
	 * @return array{left: float, top: float, right: float, bottom: float}
	 */
	public function bounds(): array {
		$radians = deg2rad( $this->angle );
		$cos     = abs( cos( $radians ) );
		$sin     = abs( sin( $radians ) );
		$half_w  = ( $this->width_cm * $cos + $this->height_cm * $sin ) / 2;
		$half_h  = ( $this->width_cm * $sin + $this->height_cm * $cos ) / 2;

		return array(
			'left'   => $this->cx - $half_w,
			'top'    => $this->cy - $half_h,
			'right'  => $this->cx + $half_w,
			'bottom' => $this->cy + $half_h,
		);
	}
}

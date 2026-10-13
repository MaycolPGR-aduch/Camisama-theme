<?php
/**
 * Zona de impresión de una prenda.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Config;

/**
 * Área donde el cliente puede colocar diseños: su posición en la maqueta y su tamaño real en cm.
 *
 * El rectángulo en la maqueta representa exactamente $width_cm × $height_cm, así el editor sabe a
 * qué escala dibujar cada diseño.
 */
final class Zone {

	public const MAX_CM = 100.0;

	/**
	 * Constructor.
	 *
	 * @param string $id        Identificador (slug) único en el producto.
	 * @param string $label     Nombre visible ("Frente").
	 * @param string $view      Vista en la que aparece.
	 * @param Rect   $rect      Posición en la maqueta.
	 * @param float  $width_cm  Ancho máximo imprimible.
	 * @param float  $height_cm Alto máximo imprimible.
	 *
	 * @throws \InvalidArgumentException Si algún valor no es válido.
	 */
	public function __construct(
		public readonly string $id,
		public readonly string $label,
		public readonly string $view,
		public readonly Rect $rect,
		public readonly float $width_cm,
		public readonly float $height_cm,
	) {
		Slug::assert( $id, 'zona' );
		Slug::assert( $view, 'vista' );
		if ( '' === trim( $label ) ) {
			throw new \InvalidArgumentException( "La zona \"{$id}\" necesita un nombre." );
		}
		foreach ( array( $width_cm, $height_cm ) as $cm ) {
			if ( $cm <= 0.0 || $cm > self::MAX_CM ) {
				throw new \InvalidArgumentException( "El tamaño de la zona \"{$label}\" debe estar entre 0 y 100 cm." );
			}
		}
	}

	/**
	 * Crea la zona desde un array.
	 *
	 * @param array<string, mixed> $data Claves id, label, view, rect y size_cm {w, h}.
	 *
	 * @throws \InvalidArgumentException Si faltan datos o no son válidos.
	 */
	public static function from_array( array $data ): self {
		if (
			! isset( $data['id'], $data['label'], $data['view'], $data['rect'], $data['size_cm']['w'], $data['size_cm']['h'] )
			|| ! is_array( $data['rect'] )
			|| ! is_numeric( $data['size_cm']['w'] )
			|| ! is_numeric( $data['size_cm']['h'] )
		) {
			throw new \InvalidArgumentException( 'Zona de impresión incompleta.' );
		}

		return new self(
			(string) $data['id'],
			(string) $data['label'],
			(string) $data['view'],
			Rect::from_array( $data['rect'] ),
			(float) $data['size_cm']['w'],
			(float) $data['size_cm']['h'],
		);
	}

	/**
	 * Lado mayor de la zona, en cm.
	 */
	public function longest_side_cm(): float {
		return max( $this->width_cm, $this->height_cm );
	}

	/**
	 * Representación serializable.
	 *
	 * @return array{id: string, label: string, view: string, rect: array{x: float, y: float, w: float, h: float}, size_cm: array{w: float, h: float}}
	 */
	public function to_array(): array {
		return array(
			'id'      => $this->id,
			'label'   => $this->label,
			'view'    => $this->view,
			'rect'    => $this->rect->to_array(),
			'size_cm' => array(
				'w' => $this->width_cm,
				'h' => $this->height_cm,
			),
		);
	}
}

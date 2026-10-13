<?php
/**
 * Vista de una prenda (frente, espalda…).
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Config;

/**
 * Una cara de la prenda con su imagen de maqueta para cada color.
 */
final class View {

	/**
	 * Constructor.
	 *
	 * @param string             $id      Identificador (slug) único en el producto.
	 * @param string             $label   Nombre visible ("Espalda").
	 * @param array<string, int> $mockups Slug del color => ID del adjunto con la maqueta.
	 *
	 * @throws \InvalidArgumentException Si algún valor no es válido.
	 */
	public function __construct(
		public readonly string $id,
		public readonly string $label,
		public readonly array $mockups = array(),
	) {
		Slug::assert( $id, 'vista' );
		if ( '' === trim( $label ) ) {
			throw new \InvalidArgumentException( "La vista \"{$id}\" necesita un nombre." );
		}
		foreach ( $mockups as $color => $attachment_id ) {
			Slug::assert( (string) $color, 'color' );
			if ( $attachment_id <= 0 ) {
				throw new \InvalidArgumentException( "Imagen no válida para el color \"{$color}\" en la vista \"{$label}\"." );
			}
		}
	}

	/**
	 * Crea la vista desde un array.
	 *
	 * @param array<string, mixed> $data Claves id, label y mockups.
	 *
	 * @throws \InvalidArgumentException Si faltan datos o no son válidos.
	 */
	public static function from_array( array $data ): self {
		if ( ! isset( $data['id'], $data['label'] ) ) {
			throw new \InvalidArgumentException( 'Vista incompleta.' );
		}

		$mockups = array();
		foreach ( (array) ( $data['mockups'] ?? array() ) as $color => $attachment_id ) {
			if ( ! is_numeric( $attachment_id ) ) {
				throw new \InvalidArgumentException( "Imagen no válida para el color \"{$color}\"." );
			}
			$mockups[ (string) $color ] = (int) $attachment_id;
		}

		return new self( (string) $data['id'], (string) $data['label'], $mockups );
	}

	/**
	 * Representación serializable.
	 *
	 * @return array{id: string, label: string, mockups: array<string, int>}
	 */
	public function to_array(): array {
		return array(
			'id'      => $this->id,
			'label'   => $this->label,
			'mockups' => $this->mockups,
		);
	}
}

<?php
/**
 * Escalón de precio por tamaño de estampado.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Pricing;

/**
 * "Hasta X cm de lado mayor cuesta Y". Los importes van en céntimos para no arrastrar errores de coma flotante.
 */
final class PriceTier {

	/**
	 * Constructor.
	 *
	 * @param string $label  Nombre visible ("Pequeño").
	 * @param float  $max_cm Lado mayor máximo del estampado, en cm.
	 * @param int    $amount Recargo en céntimos.
	 *
	 * @throws \InvalidArgumentException Si algún valor no es válido.
	 */
	public function __construct(
		public readonly string $label,
		public readonly float $max_cm,
		public readonly int $amount,
	) {
		if ( '' === trim( $label ) ) {
			throw new \InvalidArgumentException( 'Cada escalón de precio necesita un nombre.' );
		}
		if ( $max_cm <= 0.0 || $max_cm > 100.0 ) {
			throw new \InvalidArgumentException( "El tamaño máximo del escalón \"{$label}\" debe estar entre 0 y 100 cm." );
		}
		if ( $amount < 0 ) {
			throw new \InvalidArgumentException( "El precio del escalón \"{$label}\" no puede ser negativo." );
		}
	}

	/**
	 * Crea el escalón desde un array (opción guardada o API).
	 *
	 * @param array<string, mixed> $data Claves label, max_cm y amount.
	 *
	 * @throws \InvalidArgumentException Si faltan datos o no son válidos.
	 */
	public static function from_array( array $data ): self {
		if ( ! isset( $data['label'], $data['max_cm'], $data['amount'] ) || ! is_numeric( $data['max_cm'] ) || ! is_numeric( $data['amount'] ) ) {
			throw new \InvalidArgumentException( 'Escalón de precio incompleto.' );
		}

		return new self( (string) $data['label'], (float) $data['max_cm'], (int) $data['amount'] );
	}

	/**
	 * Representación serializable.
	 *
	 * @return array{label: string, max_cm: float, amount: int}
	 */
	public function to_array(): array {
		return array(
			'label'  => $this->label,
			'max_cm' => $this->max_cm,
			'amount' => $this->amount,
		);
	}
}

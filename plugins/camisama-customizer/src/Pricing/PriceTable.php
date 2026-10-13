<?php
/**
 * Tabla de recargos por tamaño de estampado.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Pricing;

/**
 * Escalones ordenados de menor a mayor. Ver docs/modelo-de-precios.md.
 */
final class PriceTable {

	/** Tolerancia para comparar medidas en cm (un estampado de 10.0000001 cm sigue siendo "hasta 10"). */
	private const EPSILON = 0.001;

	/**
	 * Escalones ordenados por tamaño.
	 *
	 * @var list<PriceTier>
	 */
	private array $tiers;

	/**
	 * Constructor.
	 *
	 * @param list<PriceTier> $tiers Escalones en cualquier orden.
	 *
	 * @throws \InvalidArgumentException Si está vacía o hay dos escalones con el mismo tamaño.
	 */
	public function __construct( array $tiers ) {
		if ( array() === $tiers ) {
			throw new \InvalidArgumentException( 'La tabla de precios necesita al menos un escalón.' );
		}

		usort( $tiers, static fn ( PriceTier $a, PriceTier $b ): int => $a->max_cm <=> $b->max_cm );

		for ( $i = 1, $count = count( $tiers ); $i < $count; $i++ ) {
			if ( abs( $tiers[ $i ]->max_cm - $tiers[ $i - 1 ]->max_cm ) < self::EPSILON ) {
				throw new \InvalidArgumentException( "Hay dos escalones con el mismo tamaño ({$tiers[ $i ]->max_cm} cm)." );
			}
		}

		$this->tiers = $tiers;
	}

	/**
	 * Tabla propuesta en docs/modelo-de-precios.md.
	 */
	public static function defaults(): self {
		return new self(
			array(
				new PriceTier( 'Pequeño', 10.0, 800 ),
				new PriceTier( 'Mediano', 20.0, 1400 ),
				new PriceTier( 'Grande', 30.0, 2000 ),
				new PriceTier( 'Máximo', 40.0, 2500 ),
			)
		);
	}

	/**
	 * Crea la tabla desde una lista de arrays.
	 *
	 * @param array<mixed> $rows Filas con label, max_cm y amount.
	 *
	 * @throws \InvalidArgumentException Si alguna fila no es válida.
	 */
	public static function from_array( array $rows ): self {
		$tiers = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				throw new \InvalidArgumentException( 'Escalón de precio incompleto.' );
			}
			$tiers[] = PriceTier::from_array( $row );
		}

		return new self( $tiers );
	}

	/**
	 * Escalones ordenados de menor a mayor.
	 *
	 * @return list<PriceTier>
	 */
	public function tiers(): array {
		return $this->tiers;
	}

	/**
	 * Escalón que corresponde a un estampado según su lado mayor, o null si supera el mayor escalón.
	 *
	 * @param float $longest_side_cm Lado mayor del rectángulo que ocupa el diseño en la zona.
	 */
	public function tier_for( float $longest_side_cm ): ?PriceTier {
		if ( $longest_side_cm <= 0.0 ) {
			return null;
		}

		foreach ( $this->tiers as $tier ) {
			if ( $longest_side_cm <= $tier->max_cm + self::EPSILON ) {
				return $tier;
			}
		}

		return null;
	}

	/**
	 * Escalones alcanzables en una zona cuyo lado mayor es $zone_longest_cm.
	 *
	 * Un escalón es alcanzable si un diseño que cabe en la zona puede caer en él; por ejemplo una
	 * manga de 9 cm solo alcanza "Pequeño" (hasta 10 cm).
	 *
	 * @param float $zone_longest_cm Lado mayor de la zona.
	 * @return list<PriceTier>
	 */
	public function tiers_within( float $zone_longest_cm ): array {
		$reachable = array();
		$previous  = 0.0;

		foreach ( $this->tiers as $tier ) {
			if ( $zone_longest_cm <= $previous + self::EPSILON ) {
				break;
			}
			$reachable[] = $tier;
			$previous    = $tier->max_cm;
		}

		return $reachable;
	}

	/**
	 * Representación serializable.
	 *
	 * @return list<array{label: string, max_cm: float, amount: int}>
	 */
	public function to_array(): array {
		return array_map( static fn ( PriceTier $tier ): array => $tier->to_array(), $this->tiers );
	}
}

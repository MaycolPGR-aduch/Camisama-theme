<?php
/**
 * Recargos de un diseño.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Pricing;

use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Design\Element;

/**
 * Calcula lo que el diseño suma al precio de la prenda (docs/modelo-de-precios.md):
 *
 * - por cada zona con algo impreso, el escalón del lado mayor del rectángulo que ocupa todo lo
 *   que hay en ella (recortado a la zona, que es lo máximo que se puede imprimir);
 * - el recargo premium de cada estampado distinto del catálogo, una sola vez por prenda.
 *
 * El editor de la tienda replica este cálculo en client/customizer/pricing.js; ambos se prueban
 * con los mismos casos (tests/fixtures/pricing-cases.json).
 */
final class DesignPricer {

	/**
	 * Constructor.
	 *
	 * @param PriceTable $prices Escalones por tamaño.
	 */
	public function __construct( private readonly PriceTable $prices ) {}

	/**
	 * Desglose del recargo.
	 *
	 * @param ProductConfig   $config   Zonas del producto.
	 * @param list<Element>   $elements Elementos del diseño.
	 * @param array<int, int> $premiums ID de estampado => recargo premium en céntimos.
	 * @return array{zones: list<array{zone: string, label: string, longest_cm: float, tier: string, amount: int}>, premiums: list<array{design_id: int, amount: int}>, total: int}
	 *
	 * @throws \InvalidArgumentException Si un elemento está en una zona que no existe o el diseño supera todos los escalones.
	 */
	public function quote( ProductConfig $config, array $elements, array $premiums ): array {
		$bounds = array();
		foreach ( $elements as $element ) {
			$zone = $config->zone( $element->zone );
			if ( null === $zone ) {
				throw new \InvalidArgumentException( "El diseño usa una zona que este producto no tiene: \"{$element->zone}\"." );
			}

			$box = $element->bounds();
			$box = array(
				'left'   => max( 0.0, $box['left'] ),
				'top'    => max( 0.0, $box['top'] ),
				'right'  => min( $zone->width_cm, $box['right'] ),
				'bottom' => min( $zone->height_cm, $box['bottom'] ),
			);
			if ( $box['right'] <= $box['left'] || $box['bottom'] <= $box['top'] ) {
				continue; // Totalmente fuera de la zona: no se imprime.
			}

			$current             = $bounds[ $zone->id ] ?? $box;
			$bounds[ $zone->id ] = array(
				'left'   => min( $current['left'], $box['left'] ),
				'top'    => min( $current['top'], $box['top'] ),
				'right'  => max( $current['right'], $box['right'] ),
				'bottom' => max( $current['bottom'], $box['bottom'] ),
			);
		}

		$zones = array();
		$total = 0;
		foreach ( $config->zones as $zone ) {
			if ( ! isset( $bounds[ $zone->id ] ) ) {
				continue;
			}

			$box     = $bounds[ $zone->id ];
			$longest = round( max( $box['right'] - $box['left'], $box['bottom'] - $box['top'] ), 2 );
			$tier    = $this->prices->tier_for( $longest );
			if ( null === $tier ) {
				throw new \InvalidArgumentException( "El diseño de la zona \"{$zone->label}\" es más grande que el mayor tamaño a la venta." );
			}

			$zones[] = array(
				'zone'       => $zone->id,
				'label'      => $zone->label,
				'longest_cm' => $longest,
				'tier'       => $tier->label,
				'amount'     => $tier->amount,
			);
			$total  += $tier->amount;
		}

		$charged = array();
		foreach ( $elements as $element ) {
			$id = $element->design_id;
			if ( 'design' !== $element->kind || null === $id || isset( $charged[ $id ] ) || ( $premiums[ $id ] ?? 0 ) <= 0 ) {
				continue;
			}
			$charged[ $id ] = array(
				'design_id' => $id,
				'amount'    => $premiums[ $id ],
			);
			$total         += $premiums[ $id ];
		}

		return array(
			'zones'    => $zones,
			'premiums' => array_values( $charged ),
			'total'    => $total,
		);
	}
}

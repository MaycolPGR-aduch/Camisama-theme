<?php
/**
 * Persistencia de la tabla de precios.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Pricing;

/**
 * Guarda la tabla en una opción de WordPress.
 */
final class PriceTableRepository {

	public const OPTION = 'camisama_customizer_price_tiers';

	/**
	 * Tabla guardada, o la propuesta por defecto si no hay ninguna válida.
	 */
	public function get(): PriceTable {
		$rows = get_option( self::OPTION );

		if ( ! is_array( $rows ) ) {
			return PriceTable::defaults();
		}

		try {
			return PriceTable::from_array( $rows );
		} catch ( \InvalidArgumentException ) {
			return PriceTable::defaults();
		}
	}

	/**
	 * Guarda la tabla.
	 *
	 * @param PriceTable $table Tabla validada.
	 */
	public function save( PriceTable $table ): void {
		update_option( self::OPTION, $table->to_array(), false );
	}
}

<?php
/**
 * Conversión de importes escritos en formularios.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Pricing;

/**
 * Los importes se guardan en céntimos (int); en los formularios se escriben en soles ("12.50").
 */
final class Money {

	/**
	 * Céntimos a partir de un importe escrito, o null si no es un importe válido no negativo.
	 * Acepta punto o coma decimal ("12.5", "12,50") y como máximo dos decimales.
	 *
	 * @param string $amount Importe en soles.
	 */
	public static function to_cents( string $amount ): ?int {
		$amount = str_replace( ',', '.', trim( $amount ) );

		if ( 1 !== preg_match( '/^(\d{1,7})(?:\.(\d{1,2}))?$/', $amount, $parts ) ) {
			return null;
		}

		return (int) $parts[1] * 100 + (int) str_pad( $parts[2] ?? '0', 2, '0' );
	}

	/**
	 * Importe en soles con dos decimales ("12.50") para mostrar en un campo de formulario.
	 *
	 * @param int $cents Céntimos.
	 */
	public static function to_decimal( int $cents ): string {
		return number_format( $cents / 100, 2, '.', '' );
	}
}

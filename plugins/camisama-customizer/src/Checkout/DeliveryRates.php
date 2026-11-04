<?php
/**
 * Tarifa de envío de cada distrito.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Checkout;

/**
 * PRECIOS PROVISIONALES, por franjas de distancia: hay que cambiarlos por los reales antes de abrir
 * al público. Importes en céntimos por ubigeo; un distrito se mueve de franja moviendo su código.
 */
final class DeliveryRates {

	/**
	 * Céntimos => distritos de esa franja.
	 */
	private const BANDS = array(
		// Lima centro y moderna.
		1000 => array(
			'150101', // Lima (Cercado).
			'150104', // Barranco.
			'150105', // Breña.
			'150113', // Jesús María.
			'150115', // La Victoria.
			'150116', // Lince.
			'150120', // Magdalena del Mar.
			'150121', // Pueblo Libre.
			'150122', // Miraflores.
			'150128', // Rímac.
			'150130', // San Borja.
			'150131', // San Isidro.
			'150134', // San Luis.
			'150136', // San Miguel.
			'150140', // Santiago de Surco.
			'150141', // Surquillo.
		),
		// Conos cercanos y Callao.
		1400 => array(
			'150103', // Ate.
			'150108', // Chorrillos.
			'150110', // Comas.
			'150111', // El Agustino.
			'150112', // Independencia.
			'150114', // La Molina.
			'150117', // Los Olivos.
			'150132', // San Juan de Lurigancho.
			'150133', // San Juan de Miraflores.
			'150135', // San Martín de Porres.
			'150137', // Santa Anita.
			'150142', // Villa El Salvador.
			'150143', // Villa María del Triunfo.
			'070101', // Callao (Cercado).
			'070102', // Bellavista.
			'070103', // Carmen de la Legua Reynoso.
			'070104', // La Perla.
			'070105', // La Punta.
		),
		// Periferia.
		2000 => array(
			'150102', // Ancón.
			'150106', // Carabayllo.
			'150107', // Chaclacayo.
			'150109', // Cieneguilla.
			'150118', // Lurigancho.
			'150119', // Lurín.
			'150123', // Pachacámac.
			'150125', // Puente Piedra.
			'150144', // Santa María de Huachipa.
			'070106', // Ventanilla.
			'070107', // Mi Perú.
		),
		// Balnearios del sur y Santa Rosa.
		2500 => array(
			'150124', // Pucusana.
			'150126', // Punta Hermosa.
			'150127', // Punta Negra.
			'150129', // San Bartolo.
			'150138', // Santa María del Mar.
			'150139', // Santa Rosa.
		),
	);

	/**
	 * Tarifa de un distrito.
	 *
	 * @param string $code Ubigeo.
	 * @return int|null Céntimos, o null si no hay envío allí.
	 */
	public static function cents( string $code ): ?int {
		foreach ( self::BANDS as $cents => $codes ) {
			if ( in_array( $code, $codes, true ) ) {
				return $cents;
			}
		}

		return null;
	}

	/**
	 * Tarifa más baja: la que se muestra mientras no se ha elegido distrito.
	 *
	 * @return int Céntimos.
	 */
	public static function lowest(): int {
		return (int) min( array_keys( self::BANDS ) );
	}
}

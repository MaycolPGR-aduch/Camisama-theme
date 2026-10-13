<?php
/**
 * Validación de identificadores.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Config;

/**
 * Identificadores de vistas, zonas y colores: minúsculas, números y guiones.
 */
final class Slug {

	/**
	 * Comprueba que $value sea un slug válido.
	 *
	 * @param string $value Valor a comprobar.
	 * @param string $what  Qué identifica, para el mensaje de error.
	 *
	 * @throws \InvalidArgumentException Si no es válido.
	 */
	public static function assert( string $value, string $what ): void {
		if ( 1 !== preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value ) || strlen( $value ) > 64 ) {
			throw new \InvalidArgumentException( "Identificador de {$what} no válido: \"{$value}\"." );
		}
	}
}

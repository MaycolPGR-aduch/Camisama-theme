<?php
/**
 * Comprobación de rutas antes de borrar archivos.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Uploads;

/**
 * Antes de borrar un archivo cuya ruta viene de la base de datos, se exige que la ruta real
 * (resolviendo enlaces y "..") esté por debajo de la carpeta permitida y no sea esa carpeta.
 */
final class SafePath {

	/**
	 * Si $path es un archivo existente dentro de $root (al menos un nivel por debajo).
	 *
	 * @param string $path Ruta a comprobar.
	 * @param string $root Carpeta permitida.
	 */
	public static function is_file_within( string $path, string $root ): bool {
		$real_path = realpath( $path );
		$real_root = realpath( $root );

		if ( false === $real_path || false === $real_root || ! is_file( $real_path ) ) {
			return false;
		}

		$prefix = rtrim( $real_root, '/\\' ) . DIRECTORY_SEPARATOR;

		// Windows no distingue mayúsculas en las rutas.
		return '\\' === DIRECTORY_SEPARATOR
			? str_starts_with( strtolower( $real_path ), strtolower( $prefix ) )
			: str_starts_with( $real_path, $prefix );
	}
}

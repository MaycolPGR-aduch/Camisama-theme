<?php
/**
 * Resolución (DPI) guardada dentro de un PNG.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Production;

/**
 * El software de la impresora (RIP) usa el bloque pHYs del PNG para saber a qué tamaño físico
 * imprimirlo. GD lo descarta al recodificar, así que se vuelve a escribir.
 */
final class PngDpi {

	private const SIGNATURE = "\x89PNG\r\n\x1a\n";

	/**
	 * Devuelve el PNG con un único bloque pHYs con la resolución indicada.
	 *
	 * @param string $png Contenido del PNG.
	 * @param int    $dpi Puntos por pulgada.
	 *
	 * @throws \InvalidArgumentException Si no es un PNG válido.
	 */
	public static function set( string $png, int $dpi ): string {
		if ( ! str_starts_with( $png, self::SIGNATURE ) || strlen( $png ) < 33 || 'IHDR' !== substr( $png, 12, 4 ) ) {
			throw new \InvalidArgumentException( 'No es un PNG válido.' );
		}

		$output = self::SIGNATURE;
		$offset = 8;
		$length = strlen( $png );

		while ( $offset + 12 <= $length ) {
			$size    = unpack( 'N', substr( $png, $offset, 4 ) )[1];
			$type    = substr( $png, $offset + 4, 4 );
			$chunk   = substr( $png, $offset, 12 + $size );
			$offset += 12 + $size;

			if ( 'pHYs' === $type ) {
				continue; // Se sustituye por el nuevo.
			}

			$output .= $chunk;
			if ( 'IHDR' === $type ) {
				$output .= self::phys_chunk( $dpi );
			}
		}

		return $output;
	}

	/**
	 * Resolución guardada en el PNG, o null si no tiene (o no está en metros).
	 *
	 * @param string $png Contenido del PNG.
	 */
	public static function read( string $png ): ?int {
		$position = strpos( $png, 'pHYs' );
		if ( false === $position ) {
			return null;
		}

		$data = unpack( 'Nx/Ny/Cunit', substr( $png, $position + 4, 9 ) );
		if ( false === $data || 1 !== $data['unit'] ) {
			return null;
		}

		return (int) round( $data['x'] * 0.0254 );
	}

	/**
	 * Bloque pHYs: píxeles por metro en X e Y, unidad = metro.
	 *
	 * @param int $dpi Puntos por pulgada.
	 */
	private static function phys_chunk( int $dpi ): string {
		$per_meter = (int) round( $dpi / 0.0254 );
		$data      = 'pHYs' . pack( 'NNC', $per_meter, $per_meter, 1 );

		return pack( 'N', 9 ) . $data . pack( 'N', crc32( $data ) );
	}
}

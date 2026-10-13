<?php
/**
 * Saneado de archivos SVG.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Security;

use enshrined\svgSanitize\Sanitizer;

/**
 * Un SVG puede llevar JavaScript (<script>, onload…) o cargar recursos externos. Antes de guardar
 * cualquier SVG se pasa por aquí: lista blanca de etiquetas y atributos, sin referencias remotas.
 */
final class SvgSanitizer {

	/** Tamaño máximo aceptado: los estampados vectoriales no deberían acercarse a esto. */
	public const MAX_BYTES = 2 * 1024 * 1024;

	/**
	 * SVG saneado, o null si no es un SVG válido.
	 *
	 * @param string $markup Contenido del archivo.
	 */
	public function sanitize( string $markup ): ?string {
		if ( '' === trim( $markup ) || strlen( $markup ) > self::MAX_BYTES ) {
			return null;
		}

		$sanitizer = new Sanitizer();
		$sanitizer->removeRemoteReferences( true );
		$sanitizer->removeXMLTag( true );

		try {
			$clean = $sanitizer->sanitize( $markup );
		} catch ( \Throwable ) {
			// La librería lanza excepciones con XML válido que no es un único <svg>.
			return null;
		}

		if ( ! is_string( $clean ) || ! str_contains( $clean, '<svg' ) ) {
			return null;
		}

		return $clean;
	}
}

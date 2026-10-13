<?php
/**
 * Dimensiones intrínsecas de un SVG.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Security;

/**
 * WordPress no calcula ancho y alto de los SVG; el editor los necesita para conocer la proporción.
 */
final class SvgDimensions {

	/**
	 * Ancho y alto a partir de width/height o, si no, del viewBox. Null si no se pueden deducir.
	 *
	 * @param string $markup SVG ya saneado.
	 * @return array{width: float, height: float}|null
	 */
	public static function from_markup( string $markup ): ?array {
		if ( 1 !== preg_match( '/<svg\b[^>]*>/i', $markup, $tag ) ) {
			return null;
		}

		$width  = self::length( $tag[0], 'width' );
		$height = self::length( $tag[0], 'height' );
		if ( null !== $width && null !== $height ) {
			return array(
				'width'  => $width,
				'height' => $height,
			);
		}

		if ( 1 === preg_match( '/\bviewBox\s*=\s*["\']\s*([-\d.eE]+)[\s,]+([-\d.eE]+)[\s,]+([\d.eE]+)[\s,]+([\d.eE]+)\s*["\']/', $tag[0], $box ) ) {
			$w = (float) $box[3];
			$h = (float) $box[4];
			if ( $w > 0 && $h > 0 ) {
				return array(
					'width'  => $w,
					'height' => $h,
				);
			}
		}

		return null;
	}

	/**
	 * Valor numérico de un atributo de longitud absoluta (px o sin unidad); los porcentajes no sirven.
	 *
	 * @param string $tag       Etiqueta <svg …>.
	 * @param string $attribute width o height.
	 */
	private static function length( string $tag, string $attribute ): ?float {
		if ( 1 !== preg_match( '/\s' . $attribute . '\s*=\s*["\']\s*([\d.]+)\s*(px)?\s*["\']/i', $tag, $match ) ) {
			return null;
		}

		$value = (float) $match[1];

		return $value > 0 ? $value : null;
	}
}

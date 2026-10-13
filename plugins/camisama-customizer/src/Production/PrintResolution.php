<?php
/**
 * Conversión entre tamaño físico de impresión y píxeles.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Production;

/**
 * Reglas de resolución para los archivos de impresión (ver docs/produccion-e-impresion.md).
 */
final class PrintResolution {

	/** Resolución con la que se generan los archivos de producción. */
	public const TARGET_DPI = 300;

	/** Por debajo de esta resolución se advierte al cliente que su imagen puede verse pixelada. */
	public const MIN_ACCEPTABLE_DPI = 150;

	private const CM_PER_INCH = 2.54;

	/**
	 * Píxeles necesarios para imprimir una medida en centímetros.
	 *
	 * Redondea hacia arriba para no quedar nunca por debajo de $dpi; el round()
	 * previo evita que el error de coma flotante sume un píxel de más.
	 *
	 * @param float $cm  Medida física en centímetros.
	 * @param int   $dpi Resolución de salida.
	 */
	public static function cm_to_px( float $cm, int $dpi = self::TARGET_DPI ): int {
		return (int) ceil( round( $cm / self::CM_PER_INCH * $dpi, 6 ) );
	}

	/**
	 * Resolución real que tendrá una imagen de $px píxeles impresa a $cm centímetros.
	 *
	 * @param int   $px Píxeles de la imagen en ese eje.
	 * @param float $cm Medida física impresa en ese eje.
	 *
	 * @throws \InvalidArgumentException Si la medida no es positiva.
	 */
	public static function effective_dpi( int $px, float $cm ): float {
		if ( $cm <= 0.0 ) {
			throw new \InvalidArgumentException( 'La medida impresa debe ser mayor que cero.' );
		}

		return $px / ( $cm / self::CM_PER_INCH );
	}

	/**
	 * Calidad de impresión esperada para una imagen a un tamaño dado.
	 *
	 * @param int   $px Píxeles de la imagen en ese eje.
	 * @param float $cm Medida física impresa en ese eje.
	 *
	 * @throws \InvalidArgumentException Si la medida no es positiva.
	 */
	public static function quality( int $px, float $cm ): ResolutionQuality {
		$dpi = self::effective_dpi( $px, $cm );

		return match ( true ) {
			$dpi >= self::TARGET_DPI         => ResolutionQuality::Optimal,
			$dpi >= self::MIN_ACCEPTABLE_DPI => ResolutionQuality::Acceptable,
			default                          => ResolutionQuality::Low,
		};
	}
}

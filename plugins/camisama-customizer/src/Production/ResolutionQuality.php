<?php
/**
 * Niveles de calidad de una imagen según su resolución efectiva.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Production;

/**
 * Calidad esperada al imprimir una imagen.
 */
enum ResolutionQuality: string {
	case Optimal    = 'optimal';
	case Acceptable = 'acceptable';
	case Low        = 'low';
}

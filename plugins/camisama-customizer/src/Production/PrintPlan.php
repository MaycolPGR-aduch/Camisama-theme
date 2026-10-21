<?php
/**
 * Qué se imprime en cada zona de una prenda.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Production;

use Camisama\Customizer\Design\Element;

/**
 * A partir del diseño guardado en el pedido (elementos y copia de las zonas), calcula para cada
 * zona el rectángulo impreso: su tamaño y su posición dentro de la zona. Es la misma regla que
 * usan el precio (DesignPricer) y el archivo de impresión (renderer.js).
 */
final class PrintPlan {

	/**
	 * Plan por zona, en el orden de las zonas del diseño.
	 *
	 * @param array<string, mixed> $document Diseño normalizado (DesignValidator).
	 * @return list<array{zone: string, label: string, width_cm: float, height_cm: float, left_cm: float, top_cm: float, width_px: int, height_px: int}>
	 */
	public static function for_document( array $document ): array {
		$plan = array();

		foreach ( (array) ( $document['zones'] ?? array() ) as $zone ) {
			$boxes = array();
			foreach ( (array) ( $document['elements'] ?? array() ) as $data ) {
				if ( ( $data['zone'] ?? null ) !== $zone['id'] ) {
					continue;
				}
				$boxes[] = ( new Element( $zone['id'], 'text', (float) $data['cx'], (float) $data['cy'], (float) $data['width_cm'], (float) $data['height_cm'], (float) ( $data['angle'] ?? 0 ) ) )->bounds();
			}
			if ( array() === $boxes ) {
				continue;
			}

			$left   = max( 0.0, min( array_column( $boxes, 'left' ) ) );
			$top    = max( 0.0, min( array_column( $boxes, 'top' ) ) );
			$right  = min( (float) $zone['size_cm']['w'], max( array_column( $boxes, 'right' ) ) );
			$bottom = min( (float) $zone['size_cm']['h'], max( array_column( $boxes, 'bottom' ) ) );

			$plan[] = array(
				'zone'      => (string) $zone['id'],
				'label'     => (string) $zone['label'],
				'width_cm'  => round( $right - $left, 1 ),
				'height_cm' => round( $bottom - $top, 1 ),
				'left_cm'   => round( $left, 1 ),
				'top_cm'    => round( $top, 1 ),
				'width_px'  => PrintResolution::cm_to_px( $right - $left ),
				'height_px' => PrintResolution::cm_to_px( $bottom - $top ),
			);
		}

		return $plan;
	}
}

<?php
/**
 * Pruebas de OrderTracking.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Checkout;

use Camisama\Customizer\Checkout\OrderTracking;
use PHPUnit\Framework\TestCase;

/**
 * Seguimiento del pedido sin cuenta.
 */
final class OrderTrackingTest extends TestCase {

	/**
	 * Cada estado dice qué pasa con el pedido, no solo su nombre.
	 */
	public function test_explains_each_status(): void {
		$this->assertStringContainsString( 'transferencia', OrderTracking::message( 'on-hold' ) );
		$this->assertStringContainsString( 'preparando', OrderTracking::message( 'processing' ) );
		$this->assertStringContainsString( 'escríbenos', OrderTracking::message( 'cancelled' ) );
		$this->assertSame( '', OrderTracking::message( 'estado-de-otro-plugin' ) );
	}

	/**
	 * Mientras se pinta el formulario, sus textos hablan como la tienda; fuera de él no se tocan.
	 */
	public function test_rewrites_form_texts_only_while_active(): void {
		$tracking = new OrderTracking();

		$this->assertSame( 'Actualizaciones', $tracking->text( 'Actualizaciones', 'Order updates' ) );

		$tracking->start( false, 'woocommerce_order_tracking' );
		$this->assertSame( 'Novedades de tu pedido', $tracking->text( 'Actualizaciones', 'Order updates' ) );
		$this->assertSame( 'Otra cosa', $tracking->text( 'Otra cosa', 'Something else' ) );

		$tracking->stop( '<form></form>', 'woocommerce_order_tracking' );
		$this->assertSame( 'Actualizaciones', $tracking->text( 'Actualizaciones', 'Order updates' ) );
	}

	/**
	 * Otros shortcodes no lo activan.
	 */
	public function test_ignores_other_shortcodes(): void {
		$tracking = new OrderTracking();

		$tracking->start( false, 'gallery' );
		$this->assertSame( 'Actualizaciones', $tracking->text( 'Actualizaciones', 'Order updates' ) );
	}
}

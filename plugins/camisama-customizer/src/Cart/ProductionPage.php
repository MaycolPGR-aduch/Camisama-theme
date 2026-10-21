<?php
/**
 * Página de producción de un pedido.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

/**
 * WooCommerce → (oculta) Producción: archivos de impresión y hoja de producción de un pedido.
 */
final class ProductionPage {

	public const SLUG = 'camisama-production';

	/**
	 * URL de la página para un pedido.
	 *
	 * @param int $order_id Pedido.
	 */
	public static function url( int $order_id ): string {
		return add_query_arg(
			array(
				'page'  => self::SLUG,
				'order' => $order_id,
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Engancha la página.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ), 70 );
	}

	/**
	 * Página sin entrada en el menú (se llega desde el pedido).
	 */
	public function add_page(): void {
		add_submenu_page( '', __( 'Producción', 'camisama-customizer' ), '', 'edit_shop_orders', self::SLUG, array( $this, 'render' ) );
	}

	/**
	 * Contenido (se completa en el paso de producción).
	 */
	public function render(): void {
		echo '<div class="wrap"><h1>' . esc_html__( 'Producción', 'camisama-customizer' ) . '</h1></div>';
	}
}

<?php
/**
 * Deshacer al quitar una prenda del carrito.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use Camisama\Customizer\Plugin;

/**
 * Una prenda lleva un diseño hecho a mano: quitarla por error no debe obligar a rehacerlo. WooCommerce
 * guarda lo quitado en la sesión hasta la siguiente página, y sus bloques no ofrecen recuperarlo.
 * Aquí se ofrece (client/cart/undo.js) con la vía de extensiones de su API de carrito.
 */
final class CartUndo {

	/**
	 * Nombre de la acción en cart/extensions.
	 */
	private const ACTION = 'camisama-undo';

	/**
	 * Identificador del script.
	 */
	private const HANDLE = 'camisama-cart-undo';

	/**
	 * Crea el servicio.
	 *
	 * @param Plugin $plugin Plugin.
	 */
	public function __construct( private readonly Plugin $plugin ) {}

	/**
	 * Engancha los filtros de WooCommerce.
	 */
	public function register(): void {
		add_action( 'woocommerce_init', array( self::class, 'register_action' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Registra la acción en la API de carrito.
	 */
	public static function register_action(): void {
		if ( ! function_exists( 'woocommerce_store_api_register_update_callback' ) ) {
			return;
		}

		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => self::ACTION,
				'callback'  => array( self::class, 'restore_last' ),
			)
		);
	}

	/**
	 * Devuelve al carrito la última prenda quitada, con su diseño.
	 *
	 * @throws RouteException Si ya no hay nada que recuperar.
	 */
	public static function restore_last(): void {
		$cart = WC()->cart;
		$key  = array_key_last( $cart->get_removed_cart_contents() );

		if ( null === $key || ! $cart->restore_cart_item( (string) $key ) ) {
			throw new RouteException( 'camisama_undo_unavailable', 'Nothing to restore', 409 );
		}
	}

	/**
	 * Carga el script donde se puede quitar una prenda: en el pago no hay papelera.
	 */
	public function enqueue(): void {
		if ( ! function_exists( 'is_checkout' ) || is_checkout() ) {
			return;
		}

		$asset_file = $this->plugin->path( 'build/cart/undo.asset.php' );
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;
		wp_enqueue_script( self::HANDLE, $this->plugin->url( 'build/cart/undo.js' ), $asset['dependencies'], $asset['version'], true );
		wp_enqueue_style( self::HANDLE, $this->plugin->url( 'build/cart/style-undo.css' ), array(), $asset['version'] );
		wp_add_inline_script(
			self::HANDLE,
			'window.camisamaCartUndo = ' . wp_json_encode(
				array(
					'api'    => esc_url_raw( rest_url( 'wc/store/v1/' ) ),
					'action' => self::ACTION,
				)
			) . ';',
			'before'
		);
	}
}

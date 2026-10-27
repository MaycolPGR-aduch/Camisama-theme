<?php
/**
 * Editar desde el carrito un diseño que ya está en él.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

/**
 * Flujo:
 *
 * 1. Cada línea personalizada del carrito enlaza a la ficha con ?camisama_edit=<clave de la línea>
 *    (nombre, miniatura y un "Editar diseño" visible).
 * 2. La ficha abre el editor con ese diseño, su talla y su cantidad (current()).
 * 3. Al guardar, el editor envía camisama_replace=<clave> junto al diseño nuevo, que pasa por la
 *    validación de siempre; si se añade bien, la línea antigua se quita (replace()).
 *
 * Solo se leen y se quitan líneas del carrito de la sesión de quien navega.
 */
final class CartEdit {

	public const QUERY = 'camisama_edit';
	public const FIELD = 'camisama_replace';

	/**
	 * Engancha los filtros de WooCommerce.
	 */
	public function register(): void {
		add_filter( 'woocommerce_cart_item_permalink', array( $this, 'permalink' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'edit_link' ), 20, 2 );
		add_action( 'woocommerce_add_to_cart', array( $this, 'replace' ), 10, 3 );
	}

	/**
	 * El nombre y la miniatura de una línea personalizada abren el editor con su diseño.
	 *
	 * @param string               $permalink Enlace del producto.
	 * @param array<string, mixed> $cart_item Línea del carrito.
	 * @param string               $key       Clave de la línea.
	 */
	public function permalink( string $permalink, array $cart_item, string $key ): string {
		return self::is_designed( $cart_item ) && '' !== $permalink
			? add_query_arg( self::QUERY, $key, $permalink )
			: $permalink;
	}

	/**
	 * "Editar diseño" visible en el carrito, el minicarrito y el pago.
	 *
	 * @param list<array<string, mixed>> $item_data Datos ya visibles.
	 * @param array<string, mixed>       $cart_item Línea del carrito.
	 * @return list<array<string, mixed>>
	 */
	public function edit_link( array $item_data, array $cart_item ): array {
		$key     = (string) ( $cart_item['key'] ?? '' );
		$product = wc_get_product( (int) ( $cart_item['product_id'] ?? 0 ) );
		if ( ! self::is_designed( $cart_item ) || '' === $key || ! $product ) {
			return $item_data;
		}

		$link        = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( self::QUERY, $key, $product->get_permalink() ) ),
			esc_html__( 'Editar diseño', 'camisama-customizer' )
		);
		$item_data[] = array(
			'key'     => __( 'Diseño', 'camisama-customizer' ),
			'value'   => $link,
			'display' => $link,
		);

		return $item_data;
	}

	/**
	 * Diseño de la línea que se pide editar (?camisama_edit=), si es de este producto.
	 *
	 * @param int $product_id Producto de la ficha.
	 * @return array{key: string, color: string, elements: list<array<string, mixed>>, quantity: int, attributes: array<string, string>}|null
	 */
	public static function current( int $product_id ): ?array {
		// Enlace de navegación: solo lee una línea del carrito de la propia sesión.
		$key  = isset( $_GET[ self::QUERY ] ) ? sanitize_key( wp_unslash( $_GET[ self::QUERY ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$item = self::item( $key );
		if ( null === $item || (int) $item['product_id'] !== $product_id ) {
			return null;
		}

		$design = $item[ CartIntegration::KEY ]['design'];

		return array(
			'key'        => $key,
			'color'      => (string) ( $design['color'] ?? '' ),
			'elements'   => array_values( (array) ( $design['elements'] ?? array() ) ),
			'quantity'   => max( 1, (int) ( $item['quantity'] ?? 1 ) ),
			'attributes' => array_map( 'strval', (array) ( $item['variation'] ?? array() ) ),
		);
	}

	/**
	 * Tras añadir el diseño editado, quita la línea que sustituye. Si el diseño no cambió,
	 * WooCommerce lo suma a la misma línea: entonces se deja la cantidad pedida.
	 *
	 * @param string $new_key    Clave de la línea añadida.
	 * @param int    $product_id Producto.
	 * @param int    $quantity   Cantidad pedida.
	 */
	public function replace( string $new_key, int $product_id, int $quantity ): void {
		// El formulario de añadir al carrito de WooCommerce no lleva nonce; solo afecta a líneas del carrito propio.
		$old_key = isset( $_POST[ self::FIELD ] ) ? sanitize_key( wp_unslash( $_POST[ self::FIELD ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$old     = self::item( $old_key );
		if ( null === $old || (int) $old['product_id'] !== $product_id ) {
			return;
		}

		if ( $old_key === $new_key ) {
			WC()->cart->set_quantity( $new_key, $quantity );
			return;
		}
		WC()->cart->remove_cart_item( $old_key );
	}

	/**
	 * Línea personalizada del carrito por su clave, o null.
	 *
	 * @param string $key Clave de la línea.
	 * @return array<string, mixed>|null
	 */
	private static function item( string $key ): ?array {
		if ( 1 !== preg_match( '/^[a-f0-9]{32}$/', $key ) || ! function_exists( 'WC' ) || null === WC()->cart ) {
			return null;
		}
		$item = WC()->cart->get_cart_item( $key );

		return self::is_designed( $item ) ? $item : null;
	}

	/**
	 * ¿La línea lleva un diseño del personalizador?
	 *
	 * @param array<string, mixed> $cart_item Línea del carrito.
	 */
	private static function is_designed( array $cart_item ): bool {
		return ! empty( $cart_item[ CartIntegration::KEY ]['design']['elements'] );
	}
}

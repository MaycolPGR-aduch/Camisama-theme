<?php
/**
 * Carrito vacío, con la voz de la tienda.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

/**
 * Cambia el contenido de los bloques de carrito vacío al pintarlos. Va en código y no en la página
 * del carrito porque así funciona igual en cualquier instalación.
 */
final class EmptyCart {

	/**
	 * Engancha los filtros de WordPress.
	 */
	public function register(): void {
		add_filter( 'render_block_woocommerce/empty-cart-block', array( self::class, 'cart_page' ) );
		add_filter( 'render_block_woocommerce/empty-mini-cart-contents-block', array( self::class, 'mini_cart' ) );
	}

	/**
	 * Carrito vacío en su página: en lugar del mensaje y la lista de productos de WooCommerce, una
	 * sola salida.
	 *
	 * @param string $content HTML del bloque.
	 * @return string
	 */
	public static function cart_page( string $content ): string {
		$inner = sprintf(
			'<div class="camisama-empty-cart"><h2 class="wp-block-heading">%1$s</h2><p>%2$s</p><p><a class="wp-element-button" href="%3$s">%4$s</a></p></div>',
			esc_html( self::title() ),
			esc_html__( 'Tu carrito está vacío. Elige un modelo y ponle tu frase, tu foto o uno de nuestros estampados.', 'camisama-customizer' ),
			esc_url( (string) wc_get_page_permalink( 'shop' ) ),
			esc_html( self::button() )
		);

		return self::with_inner( $content, $inner );
	}

	/**
	 * Carrito lateral vacío: mismos textos, sin cambiar su botón ni su estructura.
	 *
	 * @param string $content HTML del bloque.
	 * @return string
	 */
	public static function mini_cart( string $content ): string {
		return self::with_texts( $content, esc_html( self::title() ), esc_html( self::button() ) );
	}

	/**
	 * Cambia lo que hay dentro de la primera etiqueta, conservando esa etiqueta: los bloques de
	 * WooCommerce la necesitan para funcionar.
	 *
	 * @param string $content HTML del bloque.
	 * @param string $inner   HTML nuevo.
	 * @return string El HTML nuevo, o el original si no tiene la forma esperada.
	 */
	public static function with_inner( string $content, string $inner ): string {
		if ( ! preg_match( '/^\s*<div\b[^>]*>/', $content, $opening ) ) {
			return $content;
		}

		return $opening[0] . $inner . '</div>';
	}

	/**
	 * Cambia el mensaje y el texto del botón.
	 *
	 * @param string $content HTML del bloque.
	 * @param string $title   Mensaje, ya escapado.
	 * @param string $button  Texto del botón, ya escapado.
	 * @return string
	 */
	public static function with_texts( string $content, string $title, string $button ): string {
		$replaced = preg_replace(
			array( '/<strong>.*?<\/strong>/s', '/(wc-block-components-button__text">).*?(<\/div>)/s' ),
			array( '<strong>' . $title . '</strong>', '${1}' . $button . '${2}' ),
			$content,
			1
		);

		return is_string( $replaced ) ? $replaced : $content;
	}

	/**
	 * Mensaje principal.
	 */
	private static function title(): string {
		return __( 'Aquí falta tu diseño', 'camisama-customizer' );
	}

	/**
	 * Texto del botón.
	 */
	private static function button(): string {
		return __( 'Elegir modelo', 'camisama-customizer' );
	}
}

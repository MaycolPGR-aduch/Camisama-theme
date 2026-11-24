<?php
/**
 * Entrada al editor en la ficha, ya en el HTML.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Products;

/**
 * En el móvil, el precio de partida, "Personalizar" y los estampados de inicio los pinta el editor
 * (FlowEntry.js). Mientras llega, el servidor deja en su sitio lo mismo, sin acción: así la ficha no
 * salta al cargar. App.js reutiliza el hueco (data-placeholder). Sin JavaScript no se ve (style.scss).
 */
final class FlowPlaceholder {

	/**
	 * Engancha el filtro de WordPress.
	 */
	public function register(): void {
		add_filter( 'render_block_woocommerce/add-to-cart-form', array( self::class, 'prepend' ) );
	}

	/**
	 * Pone la entrada delante del formulario de compra de un producto personalizable.
	 *
	 * @param string $content HTML del bloque.
	 */
	public static function prepend( string $content ): string {
		$data = is_singular( 'product' ) ? CustomizerData::create()->for_product( (int) get_queried_object_id() ) : null;

		return null === $data ? $content : self::render( $data ) . $content;
	}

	/**
	 * HTML de la entrada.
	 *
	 * @param array<string, mixed> $data Datos del editor (CustomizerData).
	 */
	private static function render( array $data ): string {
		$starters = '';
		foreach ( $data['starters'] as $design ) {
			$starters .= sprintf(
				'<li><button type="button" disabled><img src="%1$s" alt="" width="%2$d" height="%3$d">%4$s</button></li>',
				esc_url( $design['file']['url'] ),
				(int) $design['file']['width'],
				(int) $design['file']['height'],
				esc_html( $design['title'] )
			);
		}

		return sprintf(
			'<div class="camisama-customizer camisama-customizer__entry" data-placeholder><p class="camisama-customizer__from">%1$s <strong>%2$s</strong> %3$s</p><button type="button" class="camisama-customizer__cta" disabled>%4$s</button>%5$s<button type="button" class="camisama-customizer__link" disabled>%6$s</button></div>',
			esc_html__( 'Desde', 'camisama-customizer' ),
			wp_kses_post( wp_strip_all_tags( wc_price( $data['base_price'] / 100 ) ) ),
			esc_html__( '+ impresión según el tamaño', 'camisama-customizer' ),
			esc_html__( 'Preparando el editor…', 'camisama-customizer' ),
			'' === $starters ? '' : '<div class="camisama-customizer__starters"><p>' . esc_html__( 'O empieza con uno:', 'camisama-customizer' ) . '</p><ul class="camisama-customizer__designs">' . $starters . '</ul></div>',
			esc_html__( 'Comprar sin diseño', 'camisama-customizer' )
		);
	}
}

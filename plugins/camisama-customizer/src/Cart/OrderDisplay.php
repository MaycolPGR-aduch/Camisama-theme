<?php
/**
 * Vistas previas del diseño en los pedidos.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

use Camisama\Customizer\Production\ProductionStore;

/**
 * Muestra las vistas previas guardadas en cada línea: en la confirmación del pedido, en "Mi
 * cuenta", en los correos y en la pantalla del pedido de la administración.
 */
final class OrderDisplay {

	/**
	 * Engancha las vistas.
	 */
	public function register(): void {
		add_action( 'woocommerce_order_item_meta_end', array( $this, 'customer_previews' ), 10, 4 );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'admin_previews' ), 10, 2 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_internal_meta' ) );
	}

	/**
	 * La administración del pedido muestra todos los metadatos de la línea salvo los de esta
	 * lista: los datos internos (JSON) se ocultan; ya se ven como vistas previas y desglose.
	 *
	 * @param list<string> $keys Claves ocultas.
	 * @return list<string>
	 */
	public function hide_internal_meta( array $keys ): array {
		return array_merge( $keys, array( CartIntegration::META_DESIGN, CartIntegration::META_QUOTE, CartIntegration::META_PREVIEW, ProductionStore::META ) );
	}

	/**
	 * Vistas previas para el cliente (página del pedido y correos HTML).
	 *
	 * @param int            $item_id    ID de la línea.
	 * @param \WC_Order_Item $item       Línea.
	 * @param \WC_Order      $order      Pedido.
	 * @param bool           $plain_text Si es un correo de texto plano.
	 */
	public function customer_previews( int $item_id, \WC_Order_Item $item, \WC_Order $order, bool $plain_text = false ): void {
		if ( ! $plain_text ) {
			echo self::previews_html( $item, 120 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en previews_html().
		}
	}

	/**
	 * Vistas previas y acceso a los archivos de impresión en la administración.
	 *
	 * @param int            $item_id ID de la línea.
	 * @param \WC_Order_Item $item    Línea.
	 */
	public function admin_previews( int $item_id, \WC_Order_Item $item ): void {
		$html = self::previews_html( $item, 96 );
		if ( '' === $html ) {
			return;
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado en previews_html().
		printf(
			'<p><a class="button" href="%s">%s</a></p>',
			esc_url( ProductionPage::url( $item->get_order_id() ) ),
			esc_html__( 'Archivos de impresión y hoja de producción', 'camisama-customizer' )
		);
	}

	/**
	 * Vistas previas guardadas en la línea.
	 *
	 * @param \WC_Order_Item $item Línea.
	 * @return array<string, array{id: string, url: string}>
	 */
	public static function previews( \WC_Order_Item $item ): array {
		$previews = json_decode( (string) $item->get_meta( CartIntegration::META_PREVIEW ), true );

		return is_array( $previews ) ? $previews : array();
	}

	/**
	 * HTML de las vistas previas.
	 *
	 * @param \WC_Order_Item $item  Línea.
	 * @param int            $width Ancho de cada imagen.
	 */
	private static function previews_html( \WC_Order_Item $item, int $width ): string {
		$images = '';
		foreach ( self::previews( $item ) as $preview ) {
			$images .= sprintf(
				'<img src="%s" alt="%s" width="%d" style="width:%dpx;height:auto;margin:6px 6px 0 0;border-radius:8px;border:1px solid #e4ded3" />',
				esc_url( $preview['url'] ),
				esc_attr__( 'Vista previa del diseño', 'camisama-customizer' ),
				$width,
				$width
			);
		}

		return '' === $images ? '' : '<div class="camisama-order-previews">' . $images . '</div>';
	}
}

<?php
/**
 * Estampado de muestra sobre las tarjetas de producto.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Products;

use Camisama\Customizer\Config\ProductConfigRepository;
use Camisama\Customizer\Config\Rect;

/**
 * Lo que se vende es una prenda con tu diseño, y una camiseta lisa no lo cuenta. Si el producto tiene
 * un estampado asignado (META), la tarjeta lo muestra en su zona de impresión. Va encima de la imagen,
 * no dentro: la foto del producto sigue siendo la misma en la ficha y en el carrito.
 */
final class CardPrint {

	/**
	 * Estampado (ID de camisama_design) que muestra la tarjeta del producto.
	 */
	public const META = '_camisama_card_design';

	/**
	 * Parte de la zona que ocupa, centrado y arriba: a zona completa parecería una lámina.
	 */
	private const SCALE = 0.7;

	/**
	 * Crea el servicio.
	 *
	 * @param ProductConfigRepository $configs Configuración de los productos.
	 */
	public function __construct( private readonly ProductConfigRepository $configs ) {}

	/**
	 * Engancha el filtro de WordPress.
	 */
	public function register(): void {
		add_filter( 'render_block_woocommerce/product-image', array( $this, 'render' ), 10, 3 );
	}

	/**
	 * Añade el estampado a la imagen de la tarjeta.
	 *
	 * @param string               $content HTML del bloque.
	 * @param array<string, mixed> $block   Bloque.
	 * @param \WP_Block            $instance Instancia del bloque.
	 * @return string
	 */
	public function render( string $content, array $block, \WP_Block $instance ): string {
		unset( $block );

		$product_id = (int) ( $instance->context['postId'] ?? 0 );
		$design_id  = (int) get_post_meta( $product_id, self::META, true );
		$url        = $design_id ? get_the_post_thumbnail_url( $design_id, 'full' ) : false;
		$rect       = $this->front_zone( $product_id );

		if ( ! $url || null === $rect ) {
			return $content;
		}

		return self::with_print(
			$content,
			sprintf(
				'<img class="camisama-card-print" src="%1$s" alt="" loading="lazy" decoding="async" style="%2$s">',
				esc_url( $url ),
				esc_attr( self::position( $rect ) )
			)
		);
	}

	/**
	 * Coloca el estampado justo después de la imagen del producto, dentro de su enlace.
	 *
	 * @param string $content HTML del bloque.
	 * @param string $overlay Etiqueta del estampado.
	 * @return string El HTML con el estampado, o el original si no tiene imagen.
	 */
	public static function with_print( string $content, string $overlay ): string {
		$position = preg_match( '/<img\b[^>]*>/', $content, $image, PREG_OFFSET_CAPTURE ) ? $image[0][1] + strlen( $image[0][0] ) : null;

		return null === $position ? $content : substr_replace( $content, $overlay, $position, 0 );
	}

	/**
	 * Posición del estampado en la imagen, en porcentajes de sus lados.
	 *
	 * @param Rect $zone Zona de impresión, en fracciones de la imagen.
	 * @return string CSS.
	 */
	public static function position( Rect $zone ): string {
		$side_margin = ( 1 - self::SCALE ) / 2;

		return sprintf(
			'left:%.2F%%;top:%.2F%%;width:%.2F%%;height:%.2F%%',
			( $zone->x + $zone->w * $side_margin ) * 100,
			( $zone->y + $zone->h * 0.05 ) * 100,
			$zone->w * self::SCALE * 100,
			$zone->h * self::SCALE * 100
		);
	}

	/**
	 * Zona del frente del producto.
	 *
	 * @param int $product_id Producto.
	 * @return Rect|null
	 */
	private function front_zone( int $product_id ): ?Rect {
		foreach ( $this->configs->get( $product_id )->zones as $zone ) {
			if ( 'front' === $zone->id ) {
				return $zone->rect;
			}
		}

		return null;
	}
}

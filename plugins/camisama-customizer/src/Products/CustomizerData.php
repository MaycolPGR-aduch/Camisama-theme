<?php
/**
 * Datos públicos del personalizador de un producto.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Products;

use Camisama\Customizer\Config\ProductConfigRepository;
use Camisama\Customizer\Config\Zone;
use Camisama\Customizer\Design\Fonts;
use Camisama\Customizer\Pricing\PriceTableRepository;
use Camisama\Customizer\Pricing\PriceTier;
use Camisama\Customizer\Production\PrintResolution;

/**
 * Todo lo que el editor de la tienda necesita para dibujar y cotizar la prenda. Lo usan la API
 * (GET camisama/v1/products/{id}/customizer) y el bloque, que lo incrusta en la página.
 */
final class CustomizerData {

	/**
	 * Constructor.
	 *
	 * @param ProductConfigRepository $configs Configuraciones.
	 * @param PriceTableRepository    $prices  Tabla de precios.
	 */
	public function __construct(
		private readonly ProductConfigRepository $configs,
		private readonly PriceTableRepository $prices,
	) {}

	/**
	 * Instancia con los repositorios por defecto (para plantillas como render.php).
	 */
	public static function create(): self {
		return new self( new ProductConfigRepository(), new PriceTableRepository() );
	}

	/**
	 * Datos del producto, o null si no existe, no es visible o no es personalizable.
	 *
	 * @param int $product_id ID del producto.
	 * @return array<string, mixed>|null
	 */
	public function for_product( int $product_id ): ?array {
		$product = $product_id > 0 ? wc_get_product( $product_id ) : null;
		$visible = $product && ( 'publish' === $product->get_status() || current_user_can( 'edit_post', $product_id ) );
		$config  = $visible ? $this->configs->get( $product_id ) : null;

		if ( ! $product || null === $config || ! $config->enabled ) {
			return null;
		}

		$prices = $this->prices->get();
		$tiers  = static fn ( array $tier_list ): array => array_map( static fn ( PriceTier $tier ): array => $tier->to_array(), $tier_list );

		$views = array();
		foreach ( $config->views as $view ) {
			$mockups = array();
			foreach ( $view->mockups as $color => $attachment_id ) {
				$image = wp_get_attachment_image_src( $attachment_id, 'full' );
				if ( false !== $image ) {
					$mockups[ $color ] = array(
						'url'    => (string) $image[0],
						'width'  => (int) $image[1],
						'height' => (int) $image[2],
					);
				}
			}
			$views[] = array(
				'id'      => $view->id,
				'label'   => $view->label,
				'mockups' => (object) $mockups,
			);
		}

		return array(
			'product_id'  => $product_id,
			// Precio más bajo de la prenda (con impuestos si así se muestran); se reemplaza al elegir variante.
			'base_price'  => (int) round( (float) wc_get_price_to_display( $product ) * 100 ),
			'colors'      => ProductColors::for_product( $product_id ),
			'views'       => $views,
			'zones'       => array_map(
				static fn ( Zone $zone ): array => $zone->to_array() + array( 'tiers' => $tiers( $prices->tiers_within( $zone->longest_side_cm() ) ) ),
				$config->zones
			),
			'price_tiers' => $prices->to_array(),
			'fonts'       => Fonts::for_client(),
			'api'         => array(
				'root'  => esc_url_raw( rest_url( 'camisama/v1' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			),
			// Añadir al carrito sin recargar: el editor comprueba el resultado en la Store API.
			'cart'        => array(
				'api'      => esc_url_raw( rest_url( 'wc/store/v1/cart' ) ),
				'url'      => esc_url_raw( wc_get_cart_url() ),
				'redirect' => 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ),
			),
			// Medidas de la prenda por talla para "¿Qué talla?" (paso de talla y pago).
			'size_guide'  => SizeGuide::for_product( $product_id ),
			'resolution'  => array(
				'target_dpi' => PrintResolution::TARGET_DPI,
				'min_dpi'    => PrintResolution::MIN_ACCEPTABLE_DPI,
			),
			'currency'    => array(
				'code'     => get_woocommerce_currency(),
				'symbol'   => html_entity_decode( get_woocommerce_currency_symbol() ),
				'decimals' => wc_get_price_decimals(),
			),
		);
	}
}

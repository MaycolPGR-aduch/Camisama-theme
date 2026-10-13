<?php
/**
 * API de la configuración del personalizador de un producto.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Rest;

use Camisama\Customizer\Config\ProductConfigRepository;
use Camisama\Customizer\Config\Zone;
use Camisama\Customizer\Pricing\PriceTableRepository;
use Camisama\Customizer\Pricing\PriceTier;
use Camisama\Customizer\Products\ProductColors;

/**
 * GET camisama/v1/products/{id}/customizer: todo lo que el editor necesita para dibujar la prenda.
 */
final class ProductCustomizerController {

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
	 * Registra la ruta.
	 */
	public function register_routes(): void {
		register_rest_route(
			CatalogController::NAMESPACE,
			'/products/(?P<id>\d+)/customizer',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'show' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'type'    => 'integer',
						'minimum' => 1,
					),
				),
			)
		);
	}

	/**
	 * Configuración pública del producto.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Petición.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function show( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$product_id = (int) $request->get_param( 'id' );
		$product    = wc_get_product( $product_id );
		$visible    = $product && ( 'publish' === $product->get_status() || current_user_can( 'edit_post', $product_id ) );
		$config     = $visible ? $this->configs->get( $product_id ) : null;

		if ( null === $config || ! $config->enabled ) {
			return new \WP_Error( 'camisama_not_customizable', __( 'Este producto no se puede personalizar.', 'camisama-customizer' ), array( 'status' => 404 ) );
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
				'mockups' => $mockups,
			);
		}

		$zones = array_map(
			static fn ( Zone $zone ): array => $zone->to_array() + array( 'tiers' => $tiers( $prices->tiers_within( $zone->longest_side_cm() ) ) ),
			$config->zones
		);

		return new \WP_REST_Response(
			array(
				'product_id' => $product_id,
				'colors'     => ProductColors::for_product( $product_id ),
				'views'      => $views,
				'zones'      => $zones,
				'currency'   => array(
					'code'     => get_woocommerce_currency(),
					'symbol'   => html_entity_decode( get_woocommerce_currency_symbol() ),
					'decimals' => wc_get_price_decimals(),
				),
			)
		);
	}
}

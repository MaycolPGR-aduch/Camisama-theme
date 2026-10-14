<?php
/**
 * API de la configuración del personalizador de un producto.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Rest;

use Camisama\Customizer\Products\CustomizerData;

/**
 * GET camisama/v1/products/{id}/customizer: todo lo que el editor necesita para dibujar la prenda.
 */
final class ProductCustomizerController {

	/**
	 * Constructor.
	 *
	 * @param CustomizerData $data Datos del personalizador.
	 */
	public function __construct( private readonly CustomizerData $data ) {}

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
		$data = $this->data->for_product( (int) $request->get_param( 'id' ) );

		if ( null === $data ) {
			return new \WP_Error( 'camisama_not_customizable', __( 'Este producto no se puede personalizar.', 'camisama-customizer' ), array( 'status' => 404 ) );
		}

		return new \WP_REST_Response( $data );
	}
}

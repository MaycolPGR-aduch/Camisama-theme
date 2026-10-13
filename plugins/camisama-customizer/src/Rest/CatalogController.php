<?php
/**
 * API del catálogo de estampados.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Rest;

use Camisama\Customizer\Designs\DesignRepository;

/**
 * GET camisama/v1/designs y camisama/v1/design-categories (públicos, solo lectura).
 */
final class CatalogController {

	public const NAMESPACE = 'camisama/v1';

	/**
	 * Constructor.
	 *
	 * @param DesignRepository $designs Catálogo.
	 */
	public function __construct( private readonly DesignRepository $designs ) {}

	/**
	 * Registra las rutas.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/designs',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'designs' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'category' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_title',
					),
					'page'     => array(
						'type'    => 'integer',
						'default' => 1,
						'minimum' => 1,
					),
					'per_page' => array(
						'type'    => 'integer',
						'default' => 24,
						'minimum' => 1,
						'maximum' => 100,
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/design-categories',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'categories' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Lista paginada de estampados.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Petición.
	 */
	public function designs( \WP_REST_Request $request ): \WP_REST_Response {
		$category = $request->get_param( 'category' );
		$result   = $this->designs->query(
			is_string( $category ) && '' !== $category ? $category : null,
			(int) $request->get_param( 'page' ),
			(int) $request->get_param( 'per_page' )
		);

		$response = new \WP_REST_Response( $result['items'] );
		$response->header( 'X-WP-Total', (string) $result['total'] );
		$response->header( 'X-WP-TotalPages', (string) $result['pages'] );

		return $response;
	}

	/**
	 * Categorías con estampados.
	 */
	public function categories(): \WP_REST_Response {
		return new \WP_REST_Response( $this->designs->categories() );
	}
}

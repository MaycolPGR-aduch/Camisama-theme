<?php
/**
 * API de los archivos de impresión.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Rest;

use Camisama\Customizer\Cart\CartIntegration;
use Camisama\Customizer\Production\PrintPlan;
use Camisama\Customizer\Production\ProductionStore;

/**
 * POST camisama/v1/orders/{pedido}/items/{línea}/zones/{zona} (campo "file", PNG): guarda el
 * archivo de impresión que genera la página de producción. Solo para quien gestiona pedidos.
 */
final class ProductionController {

	/**
	 * Constructor.
	 *
	 * @param ProductionStore $store Almacén de archivos de producción.
	 */
	public function __construct( private readonly ProductionStore $store ) {}

	/**
	 * Registra la ruta.
	 */
	public function register_routes(): void {
		register_rest_route(
			CatalogController::NAMESPACE,
			'/orders/(?P<order>\d+)/items/(?P<item>\d+)/zones/(?P<zone>[a-z0-9-]+)',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'upload' ),
				'permission_callback' => static fn (): bool => current_user_can( 'edit_shop_orders' ),
			)
		);
	}

	/**
	 * Guarda el PNG de una zona.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Petición.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function upload( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		$order = wc_get_order( (int) $request->get_param( 'order' ) );
		$item  = $order instanceof \WC_Order ? $order->get_item( (int) $request->get_param( 'item' ) ) : false;
		if ( ! $item instanceof \WC_Order_Item_Product ) {
			return new \WP_Error( 'camisama_not_found', __( 'No existe esa línea de pedido.', 'camisama-customizer' ), array( 'status' => 404 ) );
		}

		$document = json_decode( (string) $item->get_meta( CartIntegration::META_DESIGN ), true );
		$zone     = (string) $request->get_param( 'zone' );
		$plan     = array_values( array_filter( PrintPlan::for_document( is_array( $document ) ? $document : array() ), static fn ( array $entry ): bool => $entry['zone'] === $zone ) );
		if ( array() === $plan ) {
			return new \WP_Error( 'camisama_not_found', __( 'Esa zona no tiene nada que imprimir.', 'camisama-customizer' ), array( 'status' => 404 ) );
		}

		$file = $request->get_file_params()['file'] ?? null;
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( (string) $file['tmp_name'] ) ) {
			return new \WP_Error( 'camisama_upload_missing', __( 'No llegó el archivo.', 'camisama-customizer' ), array( 'status' => 400 ) );
		}

		try {
			return new \WP_REST_Response( $this->store->save( $order, $item, $plan[0], (string) $file['tmp_name'] ), 201 );
		} catch ( \InvalidArgumentException $e ) {
			return new \WP_Error( 'camisama_upload_invalid', $e->getMessage(), array( 'status' => 422 ) );
		} catch ( \RuntimeException $e ) {
			return new \WP_Error( 'camisama_upload_failed', $e->getMessage(), array( 'status' => 500 ) );
		}
	}
}

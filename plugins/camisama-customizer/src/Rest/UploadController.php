<?php
/**
 * API de subida de imágenes de los clientes.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Rest;

use Camisama\Customizer\Uploads\RateLimiter;
use Camisama\Customizer\Uploads\UploadStore;
use Camisama\Customizer\Uploads\UploadValidator;

/**
 * POST camisama/v1/uploads (campo "file"). Abierta a visitantes sin cuenta, porque cualquiera
 * puede personalizar; la protegen el límite de frecuencia, la validación del contenido y la
 * recodificación. Los errores devuelven un mensaje para el cliente, nunca detalles internos.
 */
final class UploadController {

	/** Subidas permitidas por visitante y hora. */
	public const HOURLY_LIMIT = 30;

	/**
	 * Constructor.
	 *
	 * @param UploadValidator $validator Validación.
	 * @param UploadStore     $store     Almacén.
	 */
	public function __construct(
		private readonly UploadValidator $validator,
		private readonly UploadStore $store,
	) {}

	/**
	 * Registra la ruta.
	 */
	public function register_routes(): void {
		register_rest_route(
			CatalogController::NAMESPACE,
			'/uploads',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create' ),
				// Pública a propósito. El límite de frecuencia va en create(): WordPress vuelve a
				// llamar a permission_callback para la cabecera Allow y contaría dos veces.
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Valida, recodifica y guarda la imagen.
	 *
	 * @param \WP_REST_Request<array<string, mixed>> $request Petición.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
		if ( ! ( new RateLimiter( 'upload', self::HOURLY_LIMIT, HOUR_IN_SECONDS ) )->hit( RateLimiter::visitor() ) ) {
			return new \WP_Error( 'camisama_rate_limited', __( 'Has subido muchas imágenes seguidas. Inténtalo de nuevo en un rato.', 'camisama-customizer' ), array( 'status' => 429 ) );
		}

		$file = $request->get_file_params()['file'] ?? null;

		$error = is_array( $file ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;

		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== $error || ! is_uploaded_file( (string) $file['tmp_name'] ) ) {
			$message = in_array( $error, array( UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ), true )
				? __( 'La imagen pesa demasiado.', 'camisama-customizer' )
				: __( 'No llegó ninguna imagen.', 'camisama-customizer' );

			return new \WP_Error( 'camisama_upload_missing', $message, array( 'status' => 400 ) );
		}

		try {
			$path = (string) $file['tmp_name'];
			$info = $this->validator->validate( $path );

			return new \WP_REST_Response( $this->store->store( $path, $info ), 201 );
		} catch ( \InvalidArgumentException $e ) {
			return new \WP_Error( 'camisama_upload_invalid', $e->getMessage(), array( 'status' => 422 ) );
		} catch ( \RuntimeException $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Camisama: error al guardar una subida: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return new \WP_Error( 'camisama_upload_failed', __( 'No pudimos guardar la imagen. Inténtalo de nuevo.', 'camisama-customizer' ), array( 'status' => 500 ) );
		}
	}
}

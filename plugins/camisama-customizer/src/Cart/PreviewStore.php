<?php
/**
 * Vistas previas del diseño que genera el navegador.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

use Camisama\Customizer\Uploads\UploadStore;
use Camisama\Customizer\Uploads\UploadValidator;

/**
 * El navegador manda cada vista como data URL (JPEG o PNG). Son imágenes de un cliente como
 * cualquier otra: se decodifican, se validan, se recodifican y se guardan con UploadStore.
 */
final class PreviewStore {

	public const MAX_BYTES = 1536 * 1024;

	/**
	 * Constructor.
	 *
	 * @param UploadStore $uploads Almacén.
	 */
	public function __construct( private readonly UploadStore $uploads ) {}

	/**
	 * Guarda las vistas previas de las vistas indicadas; ignora las que no se reconocen.
	 *
	 * @param string       $json     JSON { vista: data URL } enviado con el formulario.
	 * @param list<string> $view_ids Vistas del producto.
	 * @return array<string, array{id: string, url: string}>
	 *
	 * @throws \InvalidArgumentException Si una vista previa no es una imagen válida.
	 */
	public function store( string $json, array $view_ids ): array {
		$previews = json_decode( $json, true );
		if ( ! is_array( $previews ) ) {
			return array();
		}

		// wp_tempnam() está en wp-admin y en la tienda no se carga.
		if ( ! function_exists( 'wp_tempnam' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$validator = new UploadValidator( self::MAX_BYTES, 4_000_000, 100, 2000 );
		$stored    = array();

		foreach ( $view_ids as $view_id ) {
			$data_url = $previews[ $view_id ] ?? null;
			if ( ! is_string( $data_url ) || 1 !== preg_match( '#^data:image/(?:jpeg|png);base64,([A-Za-z0-9+/=]+)$#', $data_url, $match ) ) {
				continue;
			}

			$bytes = base64_decode( $match[1], true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- imagen enviada como data URL.
			if ( false === $bytes || strlen( $bytes ) > self::MAX_BYTES ) {
				throw new \InvalidArgumentException( 'No pudimos guardar la vista previa de tu diseño. Inténtalo de nuevo.' );
			}

			$tmp = wp_tempnam( 'camisama-preview' );
			file_put_contents( $tmp, $bytes ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- archivo temporal.
			try {
				$upload             = $this->uploads->store( $tmp, $validator->validate( $tmp ), UploadStore::KIND_PREVIEW );
				$stored[ $view_id ] = array(
					'id'  => $upload['id'],
					'url' => $upload['url'],
				);
			} finally {
				wp_delete_file( $tmp );
			}
		}

		return $stored;
	}
}

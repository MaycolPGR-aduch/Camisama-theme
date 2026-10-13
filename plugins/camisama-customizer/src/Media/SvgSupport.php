<?php
/**
 * Subida de SVG a la biblioteca de medios.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Media;

use Camisama\Customizer\Security\SvgDimensions;
use Camisama\Customizer\Security\SvgSanitizer;

/**
 * Permite subir SVG solo a quien gestiona la tienda, siempre saneados, y hace que WordPress los
 * trate como imágenes (miniaturas, imagen destacada, biblioteca de medios).
 */
final class SvgSupport {

	private const MIME = 'image/svg+xml';

	/**
	 * Constructor.
	 *
	 * @param SvgSanitizer $sanitizer Saneador.
	 */
	public function __construct( private readonly SvgSanitizer $sanitizer ) {}

	/**
	 * Engancha los filtros.
	 */
	public function register(): void {
		add_filter( 'upload_mimes', array( $this, 'allow_svg' ) );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'check_filetype' ), 10, 3 );
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'sanitize_upload' ) );
		add_filter( 'wp_handle_sideload_prefilter', array( $this, 'sanitize_upload' ) );
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'add_dimensions' ), 10, 2 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'image_src' ), 10, 2 );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'prepare_for_js' ), 10, 2 );
	}

	/**
	 * Solo quien gestiona la tienda puede subir SVG.
	 */
	public static function current_user_can_upload(): bool {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * Añade SVG a los tipos permitidos.
	 *
	 * @param array<string, string> $mimes Extensión => tipo MIME.
	 * @return array<string, string>
	 */
	public function allow_svg( array $mimes ): array {
		if ( self::current_user_can_upload() ) {
			$mimes['svg'] = self::MIME;
		}

		return $mimes;
	}

	/**
	 * WordPress no reconoce el tipo real de los SVG; lo fijamos por extensión (el contenido se valida al sanear).
	 *
	 * @param array<string, mixed> $data     Resultado de WordPress (ext, type, proper_filename).
	 * @param string               $file     Ruta temporal.
	 * @param string               $filename Nombre original.
	 * @return array<string, mixed>
	 */
	public function check_filetype( array $data, string $file, string $filename ): array {
		if ( empty( $data['type'] ) && self::is_svg_name( $filename ) && self::current_user_can_upload() ) {
			$data['ext']  = 'svg';
			$data['type'] = self::MIME;
		}

		return $data;
	}

	/**
	 * Sanea el SVG antes de moverlo a uploads; si no es válido, cancela la subida.
	 *
	 * @param array<string, mixed> $file Archivo de $_FILES (name, type, tmp_name…).
	 * @return array<string, mixed>
	 */
	public function sanitize_upload( array $file ): array {
		if ( ! self::is_svg_name( (string) ( $file['name'] ?? '' ) ) && self::MIME !== ( $file['type'] ?? '' ) ) {
			return $file;
		}

		if ( ! self::current_user_can_upload() ) {
			$file['error'] = __( 'No tienes permiso para subir archivos SVG.', 'camisama-customizer' );
			return $file;
		}

		$path  = (string) ( $file['tmp_name'] ?? '' );
		$clean = is_readable( $path ) ? $this->sanitizer->sanitize( (string) file_get_contents( $path ) ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- archivo temporal local.

		if ( null === $clean ) {
			$file['error'] = __( 'El SVG no es válido o contiene elementos no permitidos.', 'camisama-customizer' );
			return $file;
		}

		file_put_contents( $path, $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- se reescribe el archivo temporal de la subida.

		return $file;
	}

	/**
	 * Guarda ancho, alto y ruta en los metadatos del adjunto SVG.
	 *
	 * @param array<string, mixed> $metadata      Metadatos generados por WordPress.
	 * @param int                  $attachment_id ID del adjunto.
	 * @return array<string, mixed>
	 */
	public function add_dimensions( array $metadata, int $attachment_id ): array {
		if ( self::MIME !== get_post_mime_type( $attachment_id ) ) {
			return $metadata;
		}

		$path       = (string) get_attached_file( $attachment_id );
		$dimensions = is_readable( $path ) ? SvgDimensions::from_markup( (string) file_get_contents( $path ) ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- archivo local.

		if ( null !== $dimensions ) {
			$metadata['width']  = (int) round( $dimensions['width'] );
			$metadata['height'] = (int) round( $dimensions['height'] );
		}
		$metadata['file'] = _wp_relative_upload_path( $path );

		return $metadata;
	}

	/**
	 * Devuelve la URL del SVG para cualquier tamaño (no hay miniaturas de un vector).
	 *
	 * @param array<int, mixed>|false $image         [url, ancho, alto, recortada] o false.
	 * @param int                     $attachment_id ID del adjunto.
	 * @return array<int, mixed>|false
	 */
	public function image_src( array|false $image, int $attachment_id ): array|false {
		if ( self::MIME !== get_post_mime_type( $attachment_id ) ) {
			return $image;
		}

		$metadata = wp_get_attachment_metadata( $attachment_id );
		$url      = wp_get_attachment_url( $attachment_id );

		return false === $url ? $image : array( $url, (int) ( $metadata['width'] ?? 0 ), (int) ( $metadata['height'] ?? 0 ), false );
	}

	/**
	 * Vista previa de los SVG en la biblioteca de medios.
	 *
	 * @param array<string, mixed> $response   Datos del adjunto para JS.
	 * @param \WP_Post             $attachment Adjunto.
	 * @return array<string, mixed>
	 */
	public function prepare_for_js( array $response, \WP_Post $attachment ): array {
		if ( self::MIME !== $attachment->post_mime_type ) {
			return $response;
		}

		$full              = array(
			'url'         => $response['url'],
			'width'       => (int) ( $response['width'] ?? 0 ),
			'height'      => (int) ( $response['height'] ?? 0 ),
			'orientation' => 'landscape',
		);
		$response['sizes'] = array(
			'full'      => $full,
			'medium'    => $full,
			'thumbnail' => $full,
		);
		$response['image'] = array( 'src' => $response['url'] );

		return $response;
	}

	/**
	 * Si el nombre de archivo termina en .svg.
	 *
	 * @param string $filename Nombre de archivo.
	 */
	private static function is_svg_name( string $filename ): bool {
		return str_ends_with( strtolower( $filename ), '.svg' );
	}
}

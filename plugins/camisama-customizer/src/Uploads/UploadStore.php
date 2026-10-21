<?php
/**
 * Almacén de las imágenes que suben los clientes.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Uploads;

/**
 * Guarda las imágenes en wp-content/uploads/camisama-uploads/AAAA/MM/ con un nombre aleatorio,
 * recodificadas (sin metadatos EXIF como la ubicación GPS, y sin contenido oculto), y deja un
 * registro privado para encontrarlas y borrarlas. No pasan por la biblioteca de medios.
 *
 * Retención: las que no acaban en un pedido se borran a los RETENTION_DAYS días (cleanup()).
 */
final class UploadStore {

	public const POST_TYPE     = 'camisama_upload';
	public const META_FILE     = '_camisama_file';
	public const META_IN_ORDER = '_camisama_in_order';
	public const META_KIND     = '_camisama_kind';
	public const META_SIZE     = '_camisama_size';

	/** Imagen que sube el cliente para su diseño. */
	public const KIND_UPLOAD = 'upload';

	/** Vista previa del diseño generada al añadir al carrito. */
	public const KIND_PREVIEW   = 'preview';
	public const RETENTION_DAYS = 30;
	public const CLEANUP_HOOK   = 'camisama_cleanup_uploads';

	private const FOLDER = 'camisama-uploads';

	/**
	 * Registra el tipo de contenido interno (sin interfaz).
	 */
	public static function register_post_type(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'public'       => false,
				'show_ui'      => false,
				'show_in_rest' => false,
				'rewrite'      => false,
				'query_var'    => false,
				'supports'     => array( 'title' ),
			)
		);
	}

	/**
	 * Recodifica y guarda una imagen ya validada.
	 *
	 * @param string                                                    $tmp_path Archivo temporal.
	 * @param array{mime: string, ext: string, width: int, height: int} $info     Resultado de UploadValidator.
	 * @param string                                                    $kind     KIND_UPLOAD o KIND_PREVIEW.
	 * @return array{id: string, url: string, width: int, height: int, mime: string}
	 *
	 * @throws \InvalidArgumentException Si la imagen no se puede decodificar.
	 * @throws \RuntimeException         Si no se puede guardar.
	 */
	public function store( string $tmp_path, array $info, string $kind = self::KIND_UPLOAD ): array {
		$editor = wp_get_image_editor( $tmp_path );
		if ( is_wp_error( $editor ) ) {
			throw new \InvalidArgumentException( 'La imagen está dañada o no es lo que parece.' );
		}
		$editor->maybe_exif_rotate();

		$token    = bin2hex( random_bytes( 16 ) );
		$relative = self::FOLDER . '/' . gmdate( 'Y/m' ) . "/{$token}.{$info['ext']}";
		$base     = self::base_dir();
		$target   = $base . '/' . $relative;

		self::protect_folder( $base . '/' . self::FOLDER );
		if ( ! wp_mkdir_p( dirname( $target ) ) ) {
			throw new \RuntimeException( 'No se pudo crear la carpeta de subidas.' );
		}

		if ( 'image/jpeg' === $info['mime'] ) {
			$editor->set_quality( 92 );
		}
		$saved = $editor->save( $target, $info['mime'] );
		if ( is_wp_error( $saved ) ) {
			throw new \RuntimeException( 'No se pudo guardar la imagen.' );
		}

		$size = $editor->get_size();
		$id   = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'private',
				'post_title'  => $token,
				'post_author' => get_current_user_id(),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $target );
			throw new \RuntimeException( 'No se pudo registrar la imagen.' );
		}
		update_post_meta( $id, self::META_FILE, $relative );
		update_post_meta( $id, self::META_KIND, $kind );
		update_post_meta(
			$id,
			self::META_SIZE,
			array(
				'width'  => (int) $size['width'],
				'height' => (int) $size['height'],
			)
		);

		return array(
			'id'     => $token,
			'url'    => self::base_url() . '/' . $relative,
			'width'  => (int) $size['width'],
			'height' => (int) $size['height'],
			'mime'   => $info['mime'],
		);
	}

	/**
	 * Subida por su token, o null si no existe o es de otro tipo.
	 *
	 * @param string $token Token de 32 caracteres hexadecimales.
	 * @param string $kind  KIND_UPLOAD o KIND_PREVIEW.
	 * @return array{id: string, url: string, width: int, height: int}|null
	 */
	public function find( string $token, string $kind = self::KIND_UPLOAD ): ?array {
		$post_id = $this->post_id( $token );
		if ( null === $post_id || get_post_meta( $post_id, self::META_KIND, true ) !== $kind ) {
			return null;
		}

		$relative = (string) get_post_meta( $post_id, self::META_FILE, true );
		$size     = get_post_meta( $post_id, self::META_SIZE, true );

		return array(
			'id'     => $token,
			'url'    => self::base_url() . '/' . $relative,
			'width'  => (int) ( is_array( $size ) ? ( $size['width'] ?? 0 ) : 0 ),
			'height' => (int) ( is_array( $size ) ? ( $size['height'] ?? 0 ) : 0 ),
		);
	}

	/**
	 * Marca subidas como parte de un pedido: dejan de borrarse automáticamente.
	 *
	 * @param list<string> $tokens   Tokens de las subidas.
	 * @param int          $order_id Pedido.
	 */
	public function attach_to_order( array $tokens, int $order_id ): void {
		foreach ( array_unique( $tokens ) as $token ) {
			$post_id = $this->post_id( $token );
			if ( null !== $post_id ) {
				update_post_meta( $post_id, self::META_IN_ORDER, $order_id );
			}
		}
	}

	/**
	 * ID del registro de una subida.
	 *
	 * @param string $token Token.
	 */
	private function post_id( string $token ): ?int {
		if ( 1 !== preg_match( '/^[a-f0-9]{32}$/', $token ) ) {
			return null;
		}

		$ids = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'private',
				'title'          => $token,
				'fields'         => 'ids',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
			)
		);

		return $ids ? (int) $ids[0] : null;
	}

	/**
	 * Borra las subidas más antiguas que RETENTION_DAYS que no estén en un pedido.
	 *
	 * @return int Número de subidas borradas.
	 */
	public function cleanup(): int {
		$ids = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'private',
				'fields'         => 'ids',
				'posts_per_page' => 100,
				'date_query'     => array( array( 'before' => self::RETENTION_DAYS . ' days ago' ) ),
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- tarea diaria en segundo plano.
					array(
						'key'     => self::META_IN_ORDER,
						'compare' => 'NOT EXISTS',
					),
				),
			)
		);

		$deleted = 0;
		foreach ( $ids as $id ) {
			$this->delete( (int) $id );
			++$deleted;
		}

		return $deleted;
	}

	/**
	 * Borra una subida: el archivo solo si está dentro de la carpeta de subidas, y su registro.
	 *
	 * @param int $id ID del registro.
	 */
	public function delete( int $id ): void {
		if ( self::POST_TYPE !== get_post_type( $id ) ) {
			return;
		}

		$relative = (string) get_post_meta( $id, self::META_FILE, true );
		$root     = self::base_dir() . '/' . self::FOLDER;
		$path     = $root . '/' . substr( $relative, strlen( self::FOLDER ) + 1 );

		if ( str_starts_with( $relative, self::FOLDER . '/' ) && SafePath::is_file_within( $path, $root ) ) {
			wp_delete_file( $path );
		}

		wp_delete_post( $id, true );
	}

	/**
	 * Evita listados de la carpeta y que se ejecute PHP dentro (Apache).
	 *
	 * @param string $folder Carpeta raíz de las subidas.
	 */
	private static function protect_folder( string $folder ): void {
		if ( is_file( $folder . '/.htaccess' ) ) {
			return;
		}

		wp_mkdir_p( $folder );
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- archivos de protección propios.
		file_put_contents( $folder . '/index.php', "<?php\n// Silence is golden.\n" );
		file_put_contents(
			$folder . '/.htaccess',
			"Options -Indexes\n<FilesMatch \"\\.(?i:php\\d?|phtml|phar|html?|svg)$\">\n\tRequire all denied\n</FilesMatch>\n<IfModule mod_headers.c>\n\tHeader set X-Content-Type-Options nosniff\n</IfModule>\n"
		);
		// phpcs:enable
	}

	/**
	 * Carpeta base de uploads.
	 */
	private static function base_dir(): string {
		return untrailingslashit( wp_get_upload_dir()['basedir'] );
	}

	/**
	 * URL base de uploads.
	 */
	private static function base_url(): string {
		return untrailingslashit( wp_get_upload_dir()['baseurl'] );
	}
}

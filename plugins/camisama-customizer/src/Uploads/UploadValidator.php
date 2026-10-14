<?php
/**
 * Validación de las imágenes que suben los clientes.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Uploads;

/**
 * Acepta solo PNG, JPEG y WEBP reconocidos por su contenido (no por la extensión ni por el tipo
 * que declara el navegador), dentro de límites de peso y de píxeles.
 */
final class UploadValidator {

	/** Tipo MIME => extensión con la que se guarda. */
	public const TYPES = array(
		'image/png'  => 'png',
		'image/jpeg' => 'jpg',
		'image/webp' => 'webp',
	);

	/**
	 * Constructor.
	 *
	 * @param int $max_bytes  Peso máximo del archivo.
	 * @param int $max_pixels Píxeles máximos (ancho × alto); limita la memoria al recodificar.
	 * @param int $min_side   Lado mínimo en píxeles.
	 * @param int $max_side   Lado máximo en píxeles.
	 */
	public function __construct(
		public readonly int $max_bytes = 15 * 1024 * 1024,
		public readonly int $max_pixels = 40_000_000,
		public readonly int $min_side = 100,
		public readonly int $max_side = 12_000,
	) {}

	/**
	 * Comprueba el archivo y devuelve su tipo y tamaño.
	 *
	 * @param string $path Ruta del archivo temporal.
	 * @return array{mime: string, ext: string, width: int, height: int}
	 *
	 * @throws \InvalidArgumentException Con un mensaje para el cliente si no es válido.
	 */
	public function validate( string $path ): array {
		$bytes = is_file( $path ) ? (int) filesize( $path ) : 0;
		if ( $bytes <= 0 ) {
			throw new \InvalidArgumentException( 'No llegó ninguna imagen.' );
		}
		if ( $bytes > $this->max_bytes ) {
			throw new \InvalidArgumentException( sprintf( 'La imagen pesa demasiado: el máximo es %d MB.', intdiv( $this->max_bytes, 1024 * 1024 ) ) );
		}

		$mime = ( new \finfo( FILEINFO_MIME_TYPE ) )->file( $path );
		if ( ! is_string( $mime ) || ! isset( self::TYPES[ $mime ] ) ) {
			throw new \InvalidArgumentException( 'Formato no admitido: sube una imagen PNG, JPG o WEBP.' );
		}

		$info = @getimagesize( $path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- un archivo dañado no debe generar avisos.
		if ( false === $info || $info['mime'] !== $mime ) {
			throw new \InvalidArgumentException( 'La imagen está dañada o no es lo que parece.' );
		}

		list( $width, $height ) = $info;
		if ( min( $width, $height ) < $this->min_side ) {
			throw new \InvalidArgumentException( sprintf( 'La imagen es demasiado pequeña: necesita al menos %d píxeles por lado.', $this->min_side ) );
		}
		if ( max( $width, $height ) > $this->max_side || $width * $height > $this->max_pixels ) {
			throw new \InvalidArgumentException( 'La imagen tiene demasiados píxeles; redúcela antes de subirla.' );
		}

		return array(
			'mime'   => $mime,
			'ext'    => self::TYPES[ $mime ],
			'width'  => $width,
			'height' => $height,
		);
	}
}

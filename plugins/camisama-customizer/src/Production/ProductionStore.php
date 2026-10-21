<?php
/**
 * Archivos de impresión de los pedidos.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Production;

use Camisama\Customizer\Uploads\SafePath;
use Camisama\Customizer\Uploads\UploadValidator;

/**
 * Guarda los PNG de cada zona en uploads/camisama-production/<pedido>-<aleatorio>/, una carpeta
 * que niega todo acceso directo: se descargan solo con download(), que comprueba permisos.
 * Cada línea del pedido guarda en META qué archivos tiene.
 */
final class ProductionStore {

	public const META = '_camisama_production';

	private const FOLDER    = 'camisama-production';
	private const ORDER_KEY = '_camisama_production_key';

	/**
	 * Valida y guarda el PNG de una zona, con su resolución, y lo anota en la línea.
	 *
	 * @param \WC_Order                                                                                                            $order Pedido.
	 * @param \WC_Order_Item_Product                                                                                               $item  Línea.
	 * @param array{zone: string, width_cm: float, height_cm: float, left_cm: float, top_cm: float, width_px: int, height_px: int} $plan Plan de la zona (PrintPlan).
	 * @param string                                                                                                               $tmp   Archivo subido.
	 * @return array<string, mixed> Datos del archivo guardado.
	 *
	 * @throws \InvalidArgumentException Si el archivo no corresponde a la zona.
	 * @throws \RuntimeException         Si no se puede guardar.
	 */
	public function save( \WC_Order $order, \WC_Order_Item_Product $item, array $plan, string $tmp ): array {
		$info = ( new UploadValidator( 80 * 1024 * 1024, 120_000_000, 1, 25_000 ) )->validate( $tmp );
		if ( 'image/png' !== $info['mime'] ) {
			throw new \InvalidArgumentException( 'El archivo de impresión debe ser PNG.' );
		}
		if ( abs( $info['width'] - $plan['width_px'] ) > 3 || abs( $info['height'] - $plan['height_px'] ) > 3 ) {
			throw new \InvalidArgumentException( 'El archivo no tiene el tamaño de la zona; vuelve a generarlo.' );
		}

		$folder = $this->folder( $order );
		$name   = self::file_name( $order, $item, $plan );
		$png    = PngDpi::set( (string) file_get_contents( $tmp ), PrintResolution::TARGET_DPI ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- archivo temporal local.

		if ( false === file_put_contents( $folder . '/' . $name, $png ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- carpeta protegida propia.
			throw new \RuntimeException( 'No se pudo guardar el archivo de impresión.' );
		}

		$files                  = self::files( $item );
		$files[ $plan['zone'] ] = $plan + array(
			'file'       => $name,
			'created_at' => gmdate( 'c' ),
		);
		$item->update_meta_data( self::META, wp_json_encode( $files ) );
		$item->save();

		return $files[ $plan['zone'] ];
	}

	/**
	 * Archivos guardados de una línea, por zona.
	 *
	 * @param \WC_Order_Item $item Línea.
	 * @return array<string, array<string, mixed>>
	 */
	public static function files( \WC_Order_Item $item ): array {
		$files = json_decode( (string) $item->get_meta( self::META ), true );

		return is_array( $files ) ? $files : array();
	}

	/**
	 * Ruta del archivo de una zona, solo si existe dentro de la carpeta del pedido.
	 *
	 * @param \WC_Order      $order Pedido.
	 * @param \WC_Order_Item $item  Línea.
	 * @param string         $zone  Zona.
	 */
	public function path( \WC_Order $order, \WC_Order_Item $item, string $zone ): ?string {
		$file = self::files( $item )[ $zone ]['file'] ?? null;
		$key  = (string) $order->get_meta( self::ORDER_KEY );
		if ( ! is_string( $file ) || '' === $key || 1 !== preg_match( '/^[A-Za-z0-9._-]+\.png$/', $file ) ) {
			return null;
		}

		$root = self::root();
		$path = "{$root}/{$order->get_id()}-{$key}/{$file}";

		return SafePath::is_file_within( $path, $root ) ? $path : null;
	}

	/**
	 * Carpeta del pedido, creada y protegida si no existe.
	 *
	 * @param \WC_Order $order Pedido.
	 *
	 * @throws \RuntimeException Si no se puede crear.
	 */
	private function folder( \WC_Order $order ): string {
		$key = (string) $order->get_meta( self::ORDER_KEY );
		if ( '' === $key ) {
			$key = bin2hex( random_bytes( 8 ) );
			$order->update_meta_data( self::ORDER_KEY, $key );
			$order->save();
		}

		$root = self::root();
		if ( ! is_file( $root . '/.htaccess' ) ) {
			wp_mkdir_p( $root );
			// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- protección de la carpeta.
			file_put_contents( $root . '/.htaccess', "Require all denied\n" );
			file_put_contents( $root . '/index.php', "<?php\n// Silence is golden.\n" );
			// phpcs:enable
		}

		$folder = "{$root}/{$order->get_id()}-{$key}";
		if ( ! wp_mkdir_p( $folder ) ) {
			throw new \RuntimeException( 'No se pudo crear la carpeta de producción.' );
		}

		return $folder;
	}

	/**
	 * Nombre del archivo: {pedido}-{línea}-{zona}-{ancho}x{alto}cm.png (docs/produccion-e-impresion.md).
	 *
	 * @param \WC_Order                                              $order Pedido.
	 * @param \WC_Order_Item_Product                                 $item  Línea.
	 * @param array{zone: string, width_cm: float, height_cm: float} $plan  Plan de la zona.
	 */
	public static function file_name( \WC_Order $order, \WC_Order_Item_Product $item, array $plan ): string {
		$size = static fn ( float $cm ): string => rtrim( rtrim( number_format( $cm, 1, '.', '' ), '0' ), '.' );
		$line = array_search( $item->get_id(), array_keys( $order->get_items() ), true );

		return sprintf( '%s-%d-%s-%sx%scm.png', sanitize_file_name( (string) $order->get_order_number() ), false === $line ? 0 : $line + 1, $plan['zone'], $size( $plan['width_cm'] ), $size( $plan['height_cm'] ) );
	}

	/**
	 * Carpeta raíz de producción.
	 */
	private static function root(): string {
		return untrailingslashit( wp_get_upload_dir()['basedir'] ) . '/' . self::FOLDER;
	}
}

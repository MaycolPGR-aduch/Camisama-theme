<?php
/**
 * Página de producción de un pedido.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

use Camisama\Customizer\Design\Fonts;
use Camisama\Customizer\Plugin;
use Camisama\Customizer\Production\PrintPlan;
use Camisama\Customizer\Production\ProductionStore;

/**
 * Página oculta (se llega desde el pedido) con:
 *
 * - la hoja de producción imprimible: por cada prenda, modelo, color, talla, cantidad, vistas
 *   previas y, por zona, el tamaño a imprimir y su posición dentro de la zona;
 * - el generador de archivos de impresión (client/admin/production), que dibuja cada zona a
 *   300 DPI con el mismo motor que vio el cliente y la sube con ProductionController;
 * - las descargas, servidas por download() tras comprobar permisos.
 */
final class ProductionPage {

	public const SLUG = 'camisama-production';

	private const HANDLE   = 'camisama-production';
	private const DOWNLOAD = 'camisama_production_file';

	/**
	 * Constructor.
	 *
	 * @param Plugin          $plugin Rutas del plugin.
	 * @param ProductionStore $store  Archivos de producción.
	 */
	public function __construct(
		private readonly Plugin $plugin,
		private readonly ProductionStore $store,
	) {}

	/**
	 * URL de la página para un pedido.
	 *
	 * @param int $order_id Pedido.
	 */
	public static function url( int $order_id ): string {
		return add_query_arg(
			array(
				'page'  => self::SLUG,
				'order' => $order_id,
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Engancha la página, sus assets y la descarga.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ), 70 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_post_' . self::DOWNLOAD, array( $this, 'download' ) );
	}

	/**
	 * Página sin entrada en el menú.
	 */
	public function add_page(): void {
		$hook = add_submenu_page( '', __( 'Producción', 'camisama-customizer' ), '', 'edit_shop_orders', self::SLUG, array( $this, 'render' ) );

		// Las páginas sin entrada en el menú no tienen título de pestaña.
		add_action(
			'load-' . $hook,
			static function (): void {
				add_filter( 'admin_title', static fn ( string $title ): string => __( 'Producción', 'camisama-customizer' ) . ' ' . $title );
			}
		);
	}

	/**
	 * Generador de archivos (solo en esta página).
	 *
	 * @param string $hook Pantalla actual.
	 */
	public function enqueue( string $hook ): void {
		$order = $this->current_order();
		if ( ! str_ends_with( $hook, self::SLUG ) || null === $order ) {
			return;
		}

		$asset_file = $this->plugin->path( 'build/admin/production.asset.php' );
		if ( ! is_readable( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;
		wp_enqueue_script( self::HANDLE, $this->plugin->url( 'build/admin/production.js' ), $asset['dependencies'], $asset['version'], true );
		wp_enqueue_style( self::HANDLE, $this->plugin->url( 'build/admin/production.css' ), array(), $asset['version'] );
		wp_set_script_translations( self::HANDLE, 'camisama-customizer' );

		$items = array();
		foreach ( self::custom_items( $order ) as $item_id => list( $item, $document ) ) {
			$items[] = array(
				'item_id'  => $item_id,
				'name'     => $item->get_name(),
				'elements' => $document['elements'],
				'zones'    => $document['zones'],
				'plan'     => PrintPlan::for_document( $document ),
			);
		}

		wp_add_inline_script(
			self::HANDLE,
			'window.camisamaProduction = ' . wp_json_encode(
				array(
					'orderId' => $order->get_id(),
					'items'   => $items,
					'fonts'   => Fonts::for_client(),
					'api'     => array(
						'root'  => esc_url_raw( rest_url( 'camisama/v1' ) ),
						'nonce' => wp_create_nonce( 'wp_rest' ),
					),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Hoja de producción.
	 */
	public function render(): void {
		$order = $this->current_order();
		if ( null === $order ) {
			echo '<div class="wrap"><p>' . esc_html__( 'No se encontró el pedido.', 'camisama-customizer' ) . '</p></div>';
			return;
		}

		$items = self::custom_items( $order );
		?>
		<div class="wrap camisama-production">
			<div class="camisama-production__toolbar">
				<a class="button" href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">&larr; <?php esc_html_e( 'Volver al pedido', 'camisama-customizer' ); ?></a>
				<button type="button" class="button" onclick="window.print()"><?php esc_html_e( 'Imprimir hoja', 'camisama-customizer' ); ?></button>
				<div id="camisama-production-app"></div>
			</div>

			<h1>
				<?php
				/* translators: %s: número de pedido. */
				echo esc_html( sprintf( __( 'Hoja de producción · Pedido %s', 'camisama-customizer' ), $order->get_order_number() ) );
				?>
			</h1>
			<p class="camisama-production__meta">
				<?php
				echo esc_html(
					implode(
						' · ',
						array_filter(
							array(
								wc_format_datetime( $order->get_date_created(), 'd/m/Y H:i' ),
								$order->get_formatted_billing_full_name(),
								wc_get_order_status_name( $order->get_status() ),
							)
						)
					)
				);
				?>
			</p>
			<?php if ( $order->get_customer_note() ) : ?>
				<p class="camisama-production__note"><strong><?php esc_html_e( 'Nota del cliente:', 'camisama-customizer' ); ?></strong> <?php echo esc_html( $order->get_customer_note() ); ?></p>
			<?php endif; ?>

			<?php if ( array() === $items ) : ?>
				<p><?php esc_html_e( 'Este pedido no tiene prendas personalizadas.', 'camisama-customizer' ); ?></p>
			<?php endif; ?>

			<?php
			$line = 0;
			foreach ( $order->get_items() as $item_id => $item ) :
				++$line;
				if ( ! isset( $items[ $item_id ] ) ) {
					continue;
				}
				list( $item, $document ) = $items[ $item_id ];
				$files                   = ProductionStore::files( $item );
				?>
				<section class="camisama-production__item">
					<h2>
						<?php
						/* translators: 1: número de línea, 2: producto con variante, 3: cantidad. */
						echo esc_html( sprintf( __( 'Prenda %1$d · %2$s × %3$d', 'camisama-customizer' ), $line, $item->get_name(), $item->get_quantity() ) );
						?>
					</h2>
					<div class="camisama-production__previews">
						<?php foreach ( OrderDisplay::previews( $item ) as $preview ) : ?>
							<img src="<?php echo esc_url( $preview['url'] ); ?>" alt="" />
						<?php endforeach; ?>
					</div>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Zona', 'camisama-customizer' ); ?></th>
								<th><?php esc_html_e( 'Tamaño a imprimir', 'camisama-customizer' ); ?></th>
								<th><?php esc_html_e( 'Posición dentro de la zona', 'camisama-customizer' ); ?></th>
								<th><?php esc_html_e( 'Contenido', 'camisama-customizer' ); ?></th>
								<th class="camisama-production__file"><?php esc_html_e( 'Archivo', 'camisama-customizer' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( PrintPlan::for_document( $document ) as $plan ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $plan['label'] ); ?></strong></td>
									<td><?php echo esc_html( sprintf( '%s × %s cm', $plan['width_cm'], $plan['height_cm'] ) ); ?></td>
									<td>
										<?php
										/* translators: 1: cm desde el borde izquierdo, 2: cm desde el borde superior. */
										echo esc_html( sprintf( __( '%1$s cm desde la izquierda, %2$s cm desde arriba', 'camisama-customizer' ), $plan['left_cm'], $plan['top_cm'] ) );
										?>
									</td>
									<td><?php echo esc_html( self::contents( $document, $plan['zone'] ) ); ?></td>
									<td class="camisama-production__file">
										<?php if ( isset( $files[ $plan['zone'] ] ) ) : ?>
											<a href="<?php echo esc_url( $this->download_url( $order, $item_id, $plan['zone'] ) ); ?>"><?php echo esc_html( $files[ $plan['zone'] ]['file'] ); ?></a>
										<?php else : ?>
											<em><?php esc_html_e( 'Pendiente de generar', 'camisama-customizer' ); ?></em>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</section>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Descarga de un archivo de impresión (admin-post.php).
	 */
	public function download(): void {
		$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
		$item_id  = isset( $_GET['item'] ) ? absint( $_GET['item'] ) : 0;
		$zone     = isset( $_GET['zone'] ) ? sanitize_key( wp_unslash( $_GET['zone'] ) ) : '';

		check_admin_referer( self::DOWNLOAD . '_' . $order_id );
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( esc_html__( 'No tienes permiso para descargar este archivo.', 'camisama-customizer' ), '', array( 'response' => 403 ) );
		}

		$order = wc_get_order( $order_id );
		$item  = $order instanceof \WC_Order ? $order->get_item( $item_id ) : false;
		$path  = $item instanceof \WC_Order_Item ? $this->store->path( $order, $item, $zone ) : null;
		if ( null === $path ) {
			wp_die( esc_html__( 'El archivo no existe.', 'camisama-customizer' ), '', array( 'response' => 404 ) );
		}

		nocache_headers();
		header( 'Content-Type: image/png' );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
		header( 'X-Content-Type-Options: nosniff' );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- descarga de un archivo propio ya comprobado.
		exit;
	}

	/**
	 * URL firmada de descarga.
	 *
	 * @param \WC_Order $order   Pedido.
	 * @param int       $item_id Línea.
	 * @param string    $zone    Zona.
	 */
	private function download_url( \WC_Order $order, int $item_id, string $zone ): string {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action' => self::DOWNLOAD,
					'order'  => $order->get_id(),
					'item'   => $item_id,
					'zone'   => $zone,
				),
				admin_url( 'admin-post.php' )
			),
			self::DOWNLOAD . '_' . $order->get_id()
		);
	}

	/**
	 * Pedido de la URL, si el usuario puede gestionarlo.
	 */
	private function current_order(): ?\WC_Order {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo lectura; la página exige edit_shop_orders.
		$order = isset( $_GET['order'] ) ? wc_get_order( absint( $_GET['order'] ) ) : false;

		return $order instanceof \WC_Order && current_user_can( 'edit_shop_orders' ) ? $order : null;
	}

	/**
	 * Líneas personalizadas con su diseño.
	 *
	 * @param \WC_Order $order Pedido.
	 * @return array<int, array{\WC_Order_Item_Product, array<string, mixed>}>
	 */
	private static function custom_items( \WC_Order $order ): array {
		$items = array();
		foreach ( $order->get_items() as $item_id => $item ) {
			$document = $item instanceof \WC_Order_Item_Product ? json_decode( (string) $item->get_meta( CartIntegration::META_DESIGN ), true ) : null;
			if ( is_array( $document ) && ! empty( $document['elements'] ) ) {
				$items[ (int) $item_id ] = array( $item, $document );
			}
		}

		return $items;
	}

	/**
	 * Resumen de lo que lleva una zona ("Llama con manta; texto «LIMA» en Inter #FFFFFF").
	 *
	 * @param array<string, mixed> $document Diseño.
	 * @param string               $zone     Zona.
	 */
	private static function contents( array $document, string $zone ): string {
		$parts = array();
		foreach ( $document['elements'] as $element ) {
			if ( $element['zone'] !== $zone ) {
				continue;
			}
			$parts[] = match ( $element['kind'] ) {
				/* translators: 1: texto, 2: fuente, 3: color. */
				'text'  => sprintf( __( 'texto «%1$s» (%2$s, %3$s)', 'camisama-customizer' ), $element['text'], $element['font'], $element['color'] ),
				'image' => __( 'imagen del cliente', 'camisama-customizer' ),
				default => (string) $element['title'],
			};
		}

		return implode( '; ', $parts );
	}
}

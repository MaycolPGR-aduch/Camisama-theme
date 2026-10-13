<?php
/**
 * Caja "Personalizador" en la edición de producto.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Admin;

use Camisama\Customizer\Config\Presets;
use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Config\ProductConfigRepository;
use Camisama\Customizer\Plugin;
use Camisama\Customizer\Pricing\PriceTableRepository;
use Camisama\Customizer\Products\ProductColors;

/**
 * Monta el editor visual de vistas y zonas (client/admin/product-config) y guarda su resultado.
 *
 * El editor escribe la configuración como JSON en un campo oculto que viaja con el formulario del
 * producto; aquí se valida con ProductConfig antes de guardarla.
 */
final class ProductConfigMetaBox {

	private const FIELD     = 'camisama_customizer_config';
	private const NONCE     = 'camisama_customizer_config_nonce';
	private const HANDLE    = 'camisama-product-config';
	private const ERROR_KEY = 'camisama_customizer_error_';

	/**
	 * Constructor.
	 *
	 * @param Plugin                  $plugin  Rutas del plugin.
	 * @param ProductConfigRepository $configs Configuraciones.
	 * @param PriceTableRepository    $prices  Tabla de precios.
	 */
	public function __construct(
		private readonly Plugin $plugin,
		private readonly ProductConfigRepository $configs,
		private readonly PriceTableRepository $prices,
	) {}

	/**
	 * Engancha la caja, los assets y el guardado.
	 */
	public function register(): void {
		add_action( 'add_meta_boxes_product', array( $this, 'add_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
		add_action( 'admin_notices', array( $this, 'show_error' ) );
	}

	/**
	 * Añade la caja.
	 */
	public function add_meta_box(): void {
		add_meta_box( 'camisama-customizer', __( 'Personalizador', 'camisama-customizer' ), array( $this, 'render' ), 'product', 'normal', 'high' );
	}

	/**
	 * Contenedor del editor y campo oculto con la configuración actual.
	 *
	 * @param \WP_Post $post Producto.
	 */
	public function render( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE, self::NONCE );
		printf(
			'<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />',
			esc_attr( self::FIELD ),
			esc_attr( (string) wp_json_encode( $this->configs->get( $post->ID )->to_array() ) )
		);
		echo '<div id="camisama-product-config"><p>' . esc_html__( 'Cargando el editor…', 'camisama-customizer' ) . '</p></div>';
	}

	/**
	 * Carga el editor solo en la pantalla de producto.
	 *
	 * @param string $hook Pantalla actual.
	 */
	public function enqueue( string $hook ): void {
		$screen = get_current_screen();
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || null === $screen || 'product' !== $screen->post_type ) {
			return;
		}

		$asset_file = $this->plugin->path( 'build/admin/product-config.asset.php' );
		if ( ! is_readable( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;
		wp_enqueue_media();
		wp_enqueue_script( self::HANDLE, $this->plugin->url( 'build/admin/product-config.js' ), $asset['dependencies'], $asset['version'], true );
		wp_enqueue_style( self::HANDLE, $this->plugin->url( 'build/admin/product-config.css' ), array( 'wp-components' ), $asset['version'] );
		wp_set_script_translations( self::HANDLE, 'camisama-customizer' );
		wp_add_inline_script( self::HANDLE, 'window.camisamaProductConfig = ' . wp_json_encode( $this->bootstrap_data() ) . ';', 'before' );
	}

	/**
	 * Valida y guarda la configuración enviada con el producto.
	 *
	 * @param int $product_id ID del producto.
	 */
	public function save( int $product_id ): void {
		if ( ! isset( $_POST[ self::NONCE ], $_POST[ self::FIELD ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $product_id ) ) {
			return;
		}

		// El JSON se valida campo a campo en ProductConfig::from_array().
		$data = json_decode( wp_unslash( $_POST[ self::FIELD ] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! is_array( $data ) ) {
			$this->remember_error( __( 'Los datos del personalizador llegaron dañados.', 'camisama-customizer' ) );
			return;
		}

		try {
			$this->configs->save( $product_id, ProductConfig::from_array( self::only_existing_images( $data ) ) );
		} catch ( \InvalidArgumentException $e ) {
			$this->remember_error( $e->getMessage() );
		}
	}

	/**
	 * Guarda el error para mostrarlo tras la redirección del guardado.
	 *
	 * @param string $message Mensaje.
	 */
	private function remember_error( string $message ): void {
		set_transient( self::ERROR_KEY . get_current_user_id(), $message, MINUTE_IN_SECONDS );
	}

	/**
	 * Aviso si la configuración no se pudo guardar.
	 */
	public function show_error(): void {
		$key     = self::ERROR_KEY . get_current_user_id();
		$message = get_transient( $key );
		if ( ! is_string( $message ) ) {
			return;
		}

		delete_transient( $key );
		printf(
			'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'No se guardó la configuración del personalizador:', 'camisama-customizer' ),
			esc_html( $message )
		);
	}

	/**
	 * Datos iniciales del editor.
	 *
	 * @return array<string, mixed>
	 */
	private function bootstrap_data(): array {
		$post_id = (int) get_the_ID();
		$config  = $this->configs->get( $post_id );

		$images = array();
		foreach ( $config->views as $view ) {
			foreach ( $view->mockups as $attachment_id ) {
				$url = wp_get_attachment_image_url( $attachment_id, 'large' );
				if ( $url ) {
					$images[ $attachment_id ] = $url;
				}
			}
		}

		$presets = array();
		foreach ( Presets::available() as $id => $label ) {
			$presets[] = array(
				'id'     => $id,
				'label'  => $label,
				'config' => Presets::get( $id )->to_array(),
			);
		}

		return array(
			'colors'      => ProductColors::for_product( $post_id ),
			'images'      => $images,
			'presets'     => $presets,
			'priceTiers'  => $this->prices->get()->to_array(),
			'currency'    => html_entity_decode( get_woocommerce_currency_symbol() ),
			'settingsUrl' => admin_url( 'admin.php?page=camisama-customizer' ),
		);
	}

	/**
	 * Descarta maquetas que no apuntan a una imagen existente de la biblioteca.
	 *
	 * @param array<string, mixed> $data Configuración enviada.
	 * @return array<string, mixed>
	 */
	private static function only_existing_images( array $data ): array {
		foreach ( (array) ( $data['views'] ?? array() ) as $index => $view ) {
			if ( ! is_array( $view ) || ! is_array( $view['mockups'] ?? null ) ) {
				continue;
			}
			$data['views'][ $index ]['mockups'] = array_filter(
				$view['mockups'],
				static fn ( mixed $id ): bool => is_numeric( $id ) && wp_attachment_is_image( (int) $id )
			);
		}

		return $data;
	}
}

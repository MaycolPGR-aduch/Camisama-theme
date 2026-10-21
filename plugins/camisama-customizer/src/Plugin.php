<?php
/**
 * Punto de entrada del plugin.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Camisama\Customizer\Admin\PriceSettingsPage;
use Camisama\Customizer\Admin\ProductConfigMetaBox;
use Camisama\Customizer\Cart\CartIntegration;
use Camisama\Customizer\Cart\OrderDisplay;
use Camisama\Customizer\Cart\ProductionPage;
use Camisama\Customizer\Cart\PreviewStore;
use Camisama\Customizer\Config\ProductConfigRepository;
use Camisama\Customizer\Designs\DesignPostType;
use Camisama\Customizer\Designs\DesignRepository;
use Camisama\Customizer\Media\SvgSupport;
use Camisama\Customizer\Pricing\PriceTableRepository;
use Camisama\Customizer\Products\ColorSwatchField;
use Camisama\Customizer\Products\CustomizerData;
use Camisama\Customizer\Rest\CatalogController;
use Camisama\Customizer\Production\ProductionStore;
use Camisama\Customizer\Rest\ProductCustomizerController;
use Camisama\Customizer\Rest\ProductionController;
use Camisama\Customizer\Rest\UploadController;
use Camisama\Customizer\Uploads\UploadStore;
use Camisama\Customizer\Uploads\UploadValidator;
use Camisama\Customizer\Security\SvgSanitizer;

/**
 * Registra los hooks del plugin. Se inicializa una sola vez desde el archivo principal.
 */
final class Plugin {

	public const VERSION = '0.2.0';

	/**
	 * Instancia única.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Constructor.
	 *
	 * @param string $file Ruta absoluta del archivo principal del plugin.
	 */
	private function __construct( private readonly string $file ) {}

	/**
	 * Inicializa el plugin.
	 *
	 * @param string $file Ruta absoluta del archivo principal del plugin.
	 */
	public static function boot( string $file ): void {
		if ( null !== self::$instance ) {
			return;
		}

		self::$instance = new self( $file );
		self::$instance->register_hooks();
	}

	/**
	 * Ruta absoluta dentro del plugin.
	 *
	 * @param string $relative Ruta relativa a la carpeta del plugin.
	 */
	public function path( string $relative = '' ): string {
		return plugin_dir_path( $this->file ) . ltrim( $relative, '/\\' );
	}

	/**
	 * URL dentro del plugin.
	 *
	 * @param string $relative Ruta relativa a la carpeta del plugin.
	 */
	public function url( string $relative = '' ): string {
		return plugins_url( ltrim( $relative, '/\\' ), $this->file );
	}

	/**
	 * Engancha los servicios del plugin a WordPress y WooCommerce.
	 */
	private function register_hooks(): void {
		add_action( 'before_woocommerce_init', array( $this, 'declare_woocommerce_compatibility' ) );
		add_action( 'init', array( $this, 'register_blocks' ) );

		$configs = new ProductConfigRepository();
		$prices  = new PriceTableRepository();
		$designs = new DesignRepository();

		( new DesignPostType() )->register();
		( new SvgSupport( new SvgSanitizer() ) )->register();

		$uploads    = new UploadStore();
		$production = new ProductionStore();
		( new CartIntegration( $configs, $prices, $uploads, new PreviewStore( $uploads ) ) )->register();
		( new OrderDisplay() )->register();
		add_action( 'init', array( UploadStore::class, 'register_post_type' ) );
		add_action(
			UploadStore::CLEANUP_HOOK,
			static function () use ( $uploads ): void {
				$uploads->cleanup();
			}
		);
		add_action( 'init', array( $this, 'schedule_cleanup' ) );

		if ( is_admin() ) {
			( new PriceSettingsPage( $prices ) )->register();
			( new ProductConfigMetaBox( $this, $configs, $prices ) )->register();
			( new ColorSwatchField() )->register();
			( new ProductionPage( $this, $production ) )->register();
		}

		add_action(
			'rest_api_init',
			static function () use ( $configs, $prices, $designs, $uploads, $production ): void {
				( new CatalogController( $designs ) )->register_routes();
				( new ProductCustomizerController( new CustomizerData( $configs, $prices ) ) )->register_routes();
				( new UploadController( new UploadValidator(), $uploads ) )->register_routes();
				( new ProductionController( $production ) )->register_routes();
			}
		);
	}

	/**
	 * Programa la limpieza diaria de las subidas que no acabaron en un pedido.
	 */
	public function schedule_cleanup(): void {
		if ( ! wp_next_scheduled( UploadStore::CLEANUP_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', UploadStore::CLEANUP_HOOK );
		}
	}

	/**
	 * Al desactivar el plugin, quita la tarea programada.
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( UploadStore::CLEANUP_HOOK );
	}

	/**
	 * Declara compatibilidad con el almacenamiento de pedidos HPOS y con el carrito/checkout de bloques.
	 */
	public function declare_woocommerce_compatibility(): void {
		if ( ! class_exists( FeaturesUtil::class ) ) {
			return;
		}

		FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->file, true );
		FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->file, true );
	}

	/**
	 * Registra los bloques compilados en build/blocks.
	 */
	public function register_blocks(): void {
		register_block_type( $this->path( 'build/blocks/customizer' ) );
	}
}

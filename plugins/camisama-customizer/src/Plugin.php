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
use Camisama\Customizer\Config\ProductConfigRepository;
use Camisama\Customizer\Designs\DesignPostType;
use Camisama\Customizer\Designs\DesignRepository;
use Camisama\Customizer\Media\SvgSupport;
use Camisama\Customizer\Pricing\PriceTableRepository;
use Camisama\Customizer\Rest\CatalogController;
use Camisama\Customizer\Rest\ProductCustomizerController;
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

		if ( is_admin() ) {
			( new PriceSettingsPage( $prices ) )->register();
			( new ProductConfigMetaBox( $this, $configs, $prices ) )->register();
		}

		add_action(
			'rest_api_init',
			static function () use ( $configs, $prices, $designs ): void {
				( new CatalogController( $designs ) )->register_routes();
				( new ProductCustomizerController( $configs, $prices ) )->register_routes();
			}
		);
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

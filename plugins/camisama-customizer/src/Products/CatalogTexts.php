<?php
/**
 * Textos del catálogo: lo que se vende es una prenda para personalizar.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Products;

use Camisama\Customizer\Config\ProductConfigRepository;

/**
 * En los productos personalizables, "Personalizar" en lugar de "Seleccionar opciones" y, en la
 * tienda, "Desde S/ 39.90 + impresión" en lugar de un precio que no es el final.
 */
final class CatalogTexts {

	/**
	 * Título de la tienda.
	 */
	private const SHOP_TITLE = 'Elige tu modelo';

	/**
	 * Constructor.
	 *
	 * @param ProductConfigRepository $configs Configuración de los productos.
	 */
	public function __construct( private readonly ProductConfigRepository $configs ) {}

	/**
	 * Engancha los filtros de WooCommerce y WordPress.
	 */
	public function register(): void {
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'button_text' ), 10, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'price_html' ), 10, 2 );
		add_filter( 'get_the_archive_title', array( $this, 'archive_title' ) );
		add_filter( 'woocommerce_get_breadcrumb', array( $this, 'breadcrumb' ) );
	}

	/**
	 * Texto del botón de las tarjetas.
	 *
	 * @param string      $text    Texto de WooCommerce.
	 * @param \WC_Product $product Producto.
	 */
	public function button_text( string $text, \WC_Product $product ): string {
		return $this->configs->get( $product->get_id() )->enabled ? __( 'Personalizar', 'camisama-customizer' ) : $text;
	}

	/**
	 * Precio en la tienda y sus categorías: el de la prenda más barata, y que se suma la impresión.
	 * Fuera de ahí no se toca (la portada lo explica con su propio texto).
	 *
	 * @param string      $html    Precio de WooCommerce.
	 * @param \WC_Product $product Producto.
	 */
	public function price_html( string $html, \WC_Product $product ): string {
		if ( is_admin() || ! ( is_shop() || is_product_taxonomy() ) || ! $this->configs->get( $product->get_id() )->enabled ) {
			return $html;
		}

		$from = $product instanceof \WC_Product_Variable
			? (float) $product->get_variation_price( 'min', true )
			: (float) wc_get_price_to_display( $product );

		return sprintf(
			/* translators: %s: precio de la prenda. */
			esc_html__( 'Desde %s + impresión', 'camisama-customizer' ),
			wc_price( $from )
		);
	}

	/**
	 * Título de la tienda.
	 *
	 * @param string $title Título del archivo.
	 */
	public function archive_title( string $title ): string {
		return is_shop() ? esc_html__( self::SHOP_TITLE, 'camisama-customizer' ) : $title; // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText -- Constante con el texto.
	}

	/**
	 * La tienda se llama "Modelos" en la miga de pan, igual que en el menú.
	 *
	 * @param array<int, array<int, string>> $crumbs Migas: [ nombre, enlace ].
	 * @return array<int, array<int, string>>
	 */
	public function breadcrumb( array $crumbs ): array {
		$shop = wc_get_page_permalink( 'shop' );
		foreach ( $crumbs as $index => $crumb ) {
			if ( isset( $crumb[1] ) && $shop === $crumb[1] ) {
				$crumbs[ $index ][0] = __( 'Modelos', 'camisama-customizer' );
			}
		}

		return $crumbs;
	}
}

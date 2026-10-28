<?php
/**
 * El diseño en el carrito y en el pedido.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

use Camisama\Customizer\Config\ProductConfigRepository;
use Camisama\Customizer\Design\DesignValidator;
use Camisama\Customizer\Pricing\DesignPricer;
use Camisama\Customizer\Pricing\PriceTableRepository;
use Camisama\Customizer\Products\ProductColors;
use Camisama\Customizer\Uploads\UploadStore;

/**
 * Flujo de WooCommerce:
 *
 * 1. Al añadir al carrito se valida el diseño (DesignValidator) y se recalcula el recargo en el
 *    servidor (DesignPricer); lo que diga el navegador sobre precios no cuenta.
 * 2. El precio de la línea es el de la variante elegida más ese recargo.
 * 3. El carrito muestra la vista previa y el desglose.
 * 4. Al crear el pedido, el diseño, el desglose y las vistas previas quedan en la línea, y las
 *    imágenes usadas dejan de borrarse automáticamente.
 */
final class CartIntegration {

	public const KEY          = 'camisama';
	public const META_DESIGN  = '_camisama_design';
	public const META_QUOTE   = '_camisama_quote';
	public const META_PREVIEW = '_camisama_previews';

	private const MAX_JSON = 200_000;

	/**
	 * Diseño validado entre la validación y el alta en el carrito (misma petición).
	 *
	 * @var array<string, mixed>|null
	 */
	private ?array $pending = null;

	/**
	 * Constructor.
	 *
	 * @param ProductConfigRepository $configs  Configuraciones.
	 * @param PriceTableRepository    $prices   Tabla de precios.
	 * @param UploadStore             $uploads  Subidas.
	 * @param PreviewStore            $previews Vistas previas.
	 */
	public function __construct(
		private readonly ProductConfigRepository $configs,
		private readonly PriceTableRepository $prices,
		private readonly UploadStore $uploads,
		private readonly PreviewStore $previews,
	) {}

	/**
	 * Engancha los filtros de WooCommerce.
	 */
	public function register(): void {
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate' ), 10, 5 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_item_data' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_prices' ), 20 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'item_data' ), 10, 2 );
		add_filter( 'woocommerce_store_api_cart_item_images', array( $this, 'store_api_images' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'thumbnail' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_item_meta' ), 10, 3 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'attach_uploads' ) );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'attach_uploads' ) );
	}

	/**
	 * Valida el diseño enviado con el formulario del producto.
	 *
	 * @param bool                  $passed       Si WooCommerce dio por buena la petición.
	 * @param int                   $product_id   Producto.
	 * @param int                   $quantity     Cantidad.
	 * @param int                   $variation_id Variante.
	 * @param array<string, string> $variations   Atributos elegidos.
	 */
	public function validate( bool $passed, int $product_id, int $quantity, int $variation_id = 0, array $variations = array() ): bool {
		$this->pending = null;
		// El formulario de WooCommerce para añadir al carrito no lleva nonce; el JSON se valida entero en prepare().
		$raw = isset( $_POST['camisama_design'] ) ? (string) wp_unslash( $_POST['camisama_design'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! $passed || '' === trim( $raw ) ) {
			return $passed; // Sin diseño: prenda lisa.
		}

		try {
			$this->pending = $this->prepare( $raw, $product_id, $variations );
		} catch ( \InvalidArgumentException $e ) {
			wc_add_notice( esc_html( $e->getMessage() ), 'error' );
			return false;
		}

		return true;
	}

	/**
	 * Valida el diseño y calcula su recargo.
	 *
	 * @param string                $raw        JSON del editor.
	 * @param int                   $product_id Producto.
	 * @param array<string, string> $variations Atributos elegidos.
	 * @return array<string, mixed>|null Datos para la línea del carrito, o null si el diseño está vacío.
	 *
	 * @throws \InvalidArgumentException Con un mensaje para el cliente si algo no es válido.
	 */
	private function prepare( string $raw, int $product_id, array $variations ): ?array {
		$config = $this->configs->get( $product_id );
		if ( ! $config->enabled || strlen( $raw ) > self::MAX_JSON ) {
			throw new \InvalidArgumentException( __( 'Este producto no se puede personalizar así.', 'camisama-customizer' ) );
		}

		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			throw new \InvalidArgumentException( __( 'El diseño llegó dañado. Vuelve a intentarlo.', 'camisama-customizer' ) );
		}

		$result = ( new DesignValidator(
			$config,
			array_column( ProductColors::for_product( $product_id ), 'slug' ),
			array( Lookups::class, 'design' ),
			fn ( string $token ): ?array => $this->uploads->find( $token )
		) )->validate( $data );

		$chosen = $variations[ 'attribute_' . ProductColors::TAXONOMY ] ?? null;
		if ( null !== $chosen && $chosen !== $result['document']['color'] ) {
			throw new \InvalidArgumentException( __( 'El color elegido no coincide con el del diseño. Vuelve a elegirlo.', 'camisama-customizer' ) );
		}

		if ( array() === $result['elements'] ) {
			return null;
		}

		$previews_json = isset( $_POST['camisama_previews'] ) ? (string) wp_unslash( $_POST['camisama_previews'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- imágenes validadas por PreviewStore.

		return array(
			'product_id' => $product_id,
			'design'     => $result['document'],
			'quote'      => ( new DesignPricer( $this->prices->get() ) )->quote( $config, $result['elements'], $result['premiums'] ),
			'previews'   => $this->previews->store( $previews_json, array_map( static fn ( $view ) => $view->id, $config->views ) ),
		);
	}

	/**
	 * Adjunta el diseño validado a la línea del carrito.
	 *
	 * @param array<string, mixed> $cart_item_data Datos de la línea.
	 * @param int                  $product_id     Producto.
	 * @return array<string, mixed>
	 */
	public function add_item_data( array $cart_item_data, int $product_id ): array {
		if ( null !== $this->pending && $this->pending['product_id'] === $product_id ) {
			$cart_item_data[ self::KEY ] = $this->pending;
			$this->pending               = null;
		}

		return $cart_item_data;
	}

	/**
	 * Precio de cada línea personalizada: el de la variante (leído de nuevo, para no acumular
	 * recargos si WooCommerce recalcula varias veces) más el recargo del diseño.
	 *
	 * @param \WC_Cart $cart Carrito.
	 */
	public function set_prices( \WC_Cart $cart ): void {
		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item[ self::KEY ]['quote'] ) || ! $item['data'] instanceof \WC_Product ) {
				continue;
			}

			$fresh = wc_get_product( $item['data']->get_id() );
			if ( $fresh ) {
				$item['data']->set_price( (string) ( (float) $fresh->get_price() + $item[ self::KEY ]['quote']['total'] / 100 ) );
			}
		}
	}

	/**
	 * Desglose visible en el carrito, el checkout y el mini carrito.
	 *
	 * @param list<array<string, mixed>> $item_data Datos ya visibles.
	 * @param array<string, mixed>       $cart_item Línea del carrito.
	 * @return list<array<string, mixed>>
	 */
	public function item_data( array $item_data, array $cart_item ): array {
		if ( empty( $cart_item[ self::KEY ]['quote'] ) ) {
			return $item_data;
		}

		$quote       = $cart_item[ self::KEY ]['quote'];
		$item_data[] = array(
			'key'   => __( 'Personalización', 'camisama-customizer' ),
			'value' => self::summary( $quote ),
		);
		$item_data[] = array(
			'key'     => __( 'Impresión y estampados', 'camisama-customizer' ),
			'value'   => self::money( $quote['total'] ),
			'display' => self::money( $quote['total'] ),
		);

		return $item_data;
	}

	/**
	 * Vista previa como imagen de la línea en el carrito de bloques (Store API).
	 *
	 * @param array<object>        $images    Imágenes del producto.
	 * @param array<string, mixed> $cart_item Línea del carrito.
	 * @return array<object>
	 */
	public function store_api_images( array $images, array $cart_item ): array {
		$previews = $cart_item[ self::KEY ]['previews'] ?? array();
		if ( array() === $previews ) {
			return $images;
		}

		return array_values(
			array_map(
				static fn ( array $preview ): object => (object) array(
					'id'        => 0,
					'src'       => $preview['url'],
					'thumbnail' => $preview['url'],
					'srcset'    => '',
					'sizes'     => '',
					'name'      => __( 'Vista previa del diseño', 'camisama-customizer' ),
					'alt'       => __( 'Vista previa del diseño', 'camisama-customizer' ),
				),
				$previews
			)
		);
	}

	/**
	 * Vista previa en el carrito clásico.
	 *
	 * @param string               $html      Miniatura.
	 * @param array<string, mixed> $cart_item Línea del carrito.
	 */
	public function thumbnail( string $html, array $cart_item ): string {
		$preview = reset( $cart_item[ self::KEY ]['previews'] );
		if ( ! is_array( $preview ) ) {
			return $html;
		}

		return sprintf( '<img src="%s" alt="%s" width="300" height="300" loading="lazy" />', esc_url( $preview['url'] ), esc_attr__( 'Vista previa del diseño', 'camisama-customizer' ) );
	}

	/**
	 * Guarda el diseño en la línea del pedido.
	 *
	 * @param \WC_Order_Item_Product $item          Línea del pedido.
	 * @param string                 $cart_item_key Clave de la línea del carrito.
	 * @param array<string, mixed>   $values        Línea del carrito.
	 */
	public function order_item_meta( \WC_Order_Item_Product $item, string $cart_item_key, array $values ): void {
		$custom = $values[ self::KEY ] ?? null;
		if ( ! is_array( $custom ) ) {
			return;
		}

		$item->add_meta_data( self::META_DESIGN, wp_json_encode( $custom['design'] ), true );
		$item->add_meta_data( self::META_QUOTE, wp_json_encode( $custom['quote'] ), true );
		$item->add_meta_data( self::META_PREVIEW, wp_json_encode( $custom['previews'] ), true );
		$item->add_meta_data( __( 'Personalización', 'camisama-customizer' ), self::summary( $custom['quote'] ), true );
		$item->add_meta_data( __( 'Impresión y estampados', 'camisama-customizer' ), self::plain_money( $custom['quote']['total'] ), true );
	}

	/**
	 * Marca las imágenes y vistas previas del pedido para que no se borren.
	 *
	 * @param int|\WC_Order $order Pedido (el checkout clásico pasa el ID; el de bloques, el pedido).
	 */
	public function attach_uploads( int|\WC_Order $order ): void {
		$order = $order instanceof \WC_Order ? $order : wc_get_order( $order );
		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$tokens = array();
		foreach ( $order->get_items() as $item ) {
			$design   = json_decode( (string) $item->get_meta( self::META_DESIGN ), true );
			$previews = json_decode( (string) $item->get_meta( self::META_PREVIEW ), true );

			foreach ( is_array( $design ) ? $design['elements'] ?? array() : array() as $element ) {
				if ( 'image' === ( $element['kind'] ?? '' ) ) {
					$tokens[] = (string) $element['upload_id'];
				}
			}
			foreach ( is_array( $previews ) ? $previews : array() as $preview ) {
				$tokens[] = (string) ( $preview['id'] ?? '' );
			}
		}

		$this->uploads->attach_to_order( $tokens, $order->get_id() );
	}

	/**
	 * "Frente (Grande) · Manga izquierda (Pequeño)".
	 *
	 * @param array<string, mixed> $quote Desglose de DesignPricer.
	 */
	public static function summary( array $quote ): string {
		return implode(
			' · ',
			array_map(
				/* translators: 1: zona, 2: tamaño. */
				static fn ( array $zone ): string => sprintf( __( '%1$s (%2$s)', 'camisama-customizer' ), $zone['label'], $zone['tier'] ),
				$quote['zones']
			)
		);
	}

	/**
	 * Importe como texto plano ("S/ 33.00"), sin HTML ni entidades: válido también en correos de texto.
	 *
	 * @param int $cents Céntimos.
	 */
	private static function plain_money( int $cents ): string {
		return str_replace( "\u{00A0}", ' ', html_entity_decode( wp_strip_all_tags( self::money( $cents ) ), ENT_QUOTES, 'UTF-8' ) );
	}

	/**
	 * Importe en céntimos con el formato de la tienda.
	 *
	 * @param int $cents Céntimos.
	 */
	private static function money( int $cents ): string {
		return wc_price( $cents / 100 );
	}
}

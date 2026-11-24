<?php
/**
 * Dirección de entrega adaptada a Perú.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Checkout;

use Camisama\Customizer\Plugin;

/**
 * Campos de la dirección en Perú: en lugar de región y ciudad se elige departamento, provincia y
 * distrito en combos encadenados (client/checkout). Lo elegido se guarda como ubigeo en un campo
 * adicional de WooCommerce, oculto, que aporta la validación y el guardado; de él salen la ciudad
 * y la región. Sin código postal, que aquí casi nadie usa.
 */
final class Delivery {

	/**
	 * Campo adicional de la dirección con el ubigeo del distrito.
	 */
	public const FIELD = 'camisama/district';

	/**
	 * Identificador del script y los estilos de los combos.
	 */
	private const HANDLE = 'camisama-checkout-ubigeo';

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin Plugin, para las rutas de los archivos compilados.
	 */
	public function __construct( private readonly Plugin $plugin ) {}

	/**
	 * Engancha los filtros de WooCommerce.
	 */
	public function register(): void {
		add_action( 'woocommerce_init', array( self::class, 'register_field' ) );
		add_filter( 'woocommerce_get_country_locale', array( self::class, 'peru_fields' ) );
		add_action( 'woocommerce_store_api_cart_update_customer_from_request', array( self::class, 'apply_to_customer' ), 10, 2 );
		// También al confirmar: WooCommerce recalcula el envío con la dirección que manda el formulario,
		// sin ciudad ni región (campos ocultos), y sin esto cobraría la tarifa más baja.
		add_action( 'woocommerce_store_api_checkout_update_customer_from_request', array( self::class, 'apply_to_customer' ), 10, 2 );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( self::class, 'apply_to_order' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'woocommerce_package_rates', array( self::class, 'rates_in_zone' ), 10, 2 );
		add_filter( 'woocommerce_shipping_chosen_method', array( self::class, 'default_method' ), 10, 3 );
		add_filter( 'woocommerce_customer_default_location_array', array( self::class, 'default_location' ) );
		add_action( 'woocommerce_checkout_validate_order_before_payment', array( self::class, 'require_street' ), 10, 2 );
	}

	/**
	 * El recojo solo se elige a mano. Sin distrito es la única tarifa (rates_in_zone), y al añadir desde
	 * la ficha WooCommerce la tomaba por defecto y la recordaba: el carrito decía "Recojo GRATIS" y el
	 * pago se abría en recojo sin que nadie lo hubiera elegido.
	 *
	 * @param mixed $default_id Tarifa por defecto.
	 * @param mixed $rates      Tarifas del paquete.
	 * @param mixed $chosen     Tarifa que ya estaba elegida, si la había.
	 */
	public static function default_method( $default_id, $rates, $chosen ): string {
		$default_id = (string) $default_id;

		return str_starts_with( $default_id, 'pickup_location' ) && $default_id !== (string) $chosen ? '' : $default_id;
	}

	/**
	 * La región sale del distrito. Con la de la tienda puesta de partida, la facturación la guardaba y el
	 * envío no, y al recargar el pago dejaban de ser "la misma dirección": salía un segundo formulario.
	 *
	 * @param mixed $location País y región de partida.
	 * @return mixed
	 */
	public static function default_location( $location ) {
		if ( is_array( $location ) && 'PE' === ( $location['country'] ?? '' ) ) {
			$location['state'] = '';
		}

		return $location;
	}

	/**
	 * La calle no es obligatoria para WooCommerce (peru_fields), porque al recoger no hace falta; con
	 * envío, sí. El formulario ya lo pide (client/checkout/street.js); esto es para el servidor.
	 *
	 * @param mixed $order  Pedido.
	 * @param mixed $errors Errores de validación.
	 */
	public static function require_street( $order, $errors ): void {
		if ( ! $order instanceof \WC_Order || ! $errors instanceof \WP_Error ) {
			return;
		}

		$methods = array_map( static fn ( \WC_Order_Item_Shipping $item ): string => $item->get_method_id(), array_values( $order->get_shipping_methods() ) );
		if ( self::misses_street( $methods, (string) $order->get_shipping_address_1() ) ) {
			$errors->add( 'camisama_missing_street', __( 'Falta la dirección a la que te llevamos el pedido.', 'camisama-customizer' ) );
		}
	}

	/**
	 * Si un pedido con envío a domicilio no trae calle.
	 *
	 * @param list<string> $methods Métodos de envío del pedido.
	 * @param string       $street  Dirección de entrega.
	 */
	public static function misses_street( array $methods, string $street ): bool {
		return '' === trim( $street ) && array() !== array_diff( $methods, array( 'pickup_location' ) );
	}

	/**
	 * Campo con el ubigeo del distrito.
	 */
	public static function register_field(): void {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
			return;
		}

		woocommerce_register_additional_checkout_field(
			array(
				'id'                         => self::FIELD,
				'label'                      => __( 'Distrito', 'camisama-customizer' ),
				'location'                   => 'address',
				'type'                       => 'text',
				'validate_callback'          => array( self::class, 'validate' ),
				// La confirmación ya lo muestra como ciudad y región.
				'show_in_order_confirmation' => false,
			) + self::pickup_rules()
		);
	}

	/**
	 * Al recoger en el taller el distrito no hace falta: se oculta y deja de ser obligatorio. Son
	 * reglas de WooCommerce sobre el carrito, así el pago y el servidor deciden lo mismo.
	 *
	 * @return array{required: array<string, mixed>, hidden: array<string, mixed>}
	 */
	public static function pickup_rules(): array {
		$collecting = static fn ( bool $value ): array => array(
			'cart' => array(
				'properties' => array(
					'prefers_collection' => array( 'const' => $value ),
				),
			),
		);

		return array(
			'required' => $collecting( false ),
			'hidden'   => $collecting( true ),
		);
	}

	/**
	 * El ubigeo tiene que ser de un distrito real (que no esté vacío lo comprueba WooCommerce).
	 *
	 * @param mixed $value Valor del campo.
	 * @return true|\WP_Error
	 */
	public static function validate( $value ) {
		if ( '' === $value || null !== Ubigeo::name( (string) $value ) ) {
			return true;
		}

		return new \WP_Error( 'camisama_invalid_district', __( 'Elige tu distrito en la lista.', 'camisama-customizer' ) );
	}

	/**
	 * Combos del formulario, solo en la página de pago.
	 */
	public function enqueue(): void {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}

		$asset_file = $this->plugin->path( 'build/checkout/ubigeo.asset.php' );
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = require $asset_file;
		wp_enqueue_script( self::HANDLE, $this->plugin->url( 'build/checkout/ubigeo.js' ), $asset['dependencies'], $asset['version'], array( 'in_footer' => true ) );
		wp_enqueue_style( self::HANDLE, $this->plugin->url( 'build/checkout/style-ubigeo.css' ), array(), $asset['version'] );
		wp_add_inline_script(
			self::HANDLE,
			'window.camisamaUbigeo = ' . wp_json_encode(
				array(
					'url'   => $this->plugin->url( 'data/ubigeo.json' ),
					'rates' => DeliveryRates::all(),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Campos de la dirección para Perú.
	 *
	 * @param array<string, mixed> $locale Campos por país.
	 * @return array<string, mixed>
	 */
	public static function peru_fields( array $locale ): array {
		$locale['PE'] = array(
			self::FIELD => array( 'index' => 45 ),
			// Obligatoria solo con envío: require_street y client/checkout/street.js.
			'address_1' => array(
				'required'      => false,
				'optionalLabel' => __( 'Dirección', 'camisama-customizer' ),
			),
			'address_2' => array( 'label' => __( 'Referencia', 'camisama-customizer' ) ),
			'city'      => array(
				'hidden'   => true,
				'required' => false,
			),
			'state'     => array(
				'hidden'   => true,
				'required' => false,
			),
			'postcode'  => array(
				'hidden'   => true,
				'required' => false,
			),
		);

		return $locale;
	}

	/**
	 * Tarifa de envío según el distrito (DeliveryRates). Fuera de la zona de envíos solo queda el
	 * recojo. Sin distrito tampoco hay envío a domicilio todavía: no se puede saber cuánto cuesta, y
	 * sumar la tarifa más baja hacía subir el total al elegir un distrito lejano. Cada distrito lleva
	 * su tarifa en el combo (client/checkout/ubigeo.js).
	 *
	 * @param array<string, \WC_Shipping_Rate> $rates   Tarifas.
	 * @param array<string, mixed>             $package Paquete, con su destino.
	 * @return array<string, \WC_Shipping_Rate>
	 */
	public static function rates_in_zone( array $rates, array $package ): array {
		$state = (string) ( $package['destination']['state'] ?? '' );
		if ( '' !== $state && ! Ubigeo::state_delivers( $state ) ) {
			return array_filter(
				$rates,
				static fn ( \WC_Shipping_Rate $rate ): bool => 'pickup_location' === $rate->get_method_id()
			);
		}

		$city  = (string) ( $package['destination']['city'] ?? '' );
		$code  = Ubigeo::delivery_code( $city, $state );
		$cents = null === $code ? null : DeliveryRates::cents( $code );

		foreach ( $rates as $id => $rate ) {
			if ( 'flat_rate' !== $rate->get_method_id() ) {
				continue;
			}
			if ( null === $cents ) {
				unset( $rates[ $id ] );
				continue;
			}
			$rate->set_cost( (string) ( $cents / 100 ) );
			/* translators: %s: distrito. */
			$rate->set_label( sprintf( __( 'Envío a %s', 'camisama-customizer' ), $city ) );
		}

		return $rates;
	}

	/**
	 * Ciudad y región de una dirección a partir de su distrito.
	 *
	 * @param mixed $address Dirección de la petición.
	 * @return array{city: string, state: string}|null Null si no trae un distrito válido.
	 */
	public static function place( $address ): ?array {
		$code = is_array( $address ) ? (string) ( $address[ self::FIELD ] ?? '' ) : '';
		$name = Ubigeo::name( $code );

		return null === $name
			? null
			: array(
				'city'  => $name,
				'state' => Ubigeo::state( $code ),
			);
	}

	/**
	 * El cliente guarda la ciudad y la región de su distrito: las usan el resumen y los envíos.
	 *
	 * @param \WC_Customer     $customer Cliente.
	 * @param \WP_REST_Request $request  Petición.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function apply_to_customer( \WC_Customer $customer, \WP_REST_Request $request ): void {
		$shipping = self::place( $request['shipping_address'] );
		if ( null !== $shipping ) {
			$customer->set_shipping_city( $shipping['city'] );
			$customer->set_shipping_state( $shipping['state'] );
		}

		$billing = self::place( $request['billing_address'] );
		if ( null !== $billing ) {
			$customer->set_billing_city( $billing['city'] );
			$customer->set_billing_state( $billing['state'] );
		}
	}

	/**
	 * El pedido guarda la ciudad y la región del distrito elegido.
	 *
	 * @param \WC_Order        $order   Pedido.
	 * @param \WP_REST_Request $request Petición.
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function apply_to_order( \WC_Order $order, \WP_REST_Request $request ): void {
		$shipping = self::place( $request['shipping_address'] );
		if ( null !== $shipping ) {
			$order->set_shipping_city( $shipping['city'] );
			$order->set_shipping_state( $shipping['state'] );
		}

		$billing = self::place( $request['billing_address'] );
		if ( null !== $billing ) {
			$order->set_billing_city( $billing['city'] );
			$order->set_billing_state( $billing['state'] );
		}
	}
}

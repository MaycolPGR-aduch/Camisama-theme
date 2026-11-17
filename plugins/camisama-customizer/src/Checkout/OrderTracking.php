<?php
/**
 * Seguimiento del pedido sin cuenta: número de pedido y correo.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Checkout;

/**
 * Usa el formulario de seguimiento de WooCommerce (shortcode woocommerce_order_tracking, en la página
 * que crea bin/seed-tracking.php): solo muestra un pedido si el número y el correo de facturación
 * coinciden. Aquí se le da la voz de la tienda, se explica cada estado y se enlaza desde la
 * confirmación del pedido y los correos.
 */
final class OrderTracking {

	/**
	 * Ruta de la página.
	 */
	public const SLUG = 'sigue-tu-pedido';

	/**
	 * Texto original de WooCommerce => texto de la tienda, solo mientras se pinta el seguimiento. Los
	 * del formulario están en el tema (woocommerce/order/form-tracking.php); estos son los avisos y el
	 * resultado.
	 */
	private const TEXTS = array(
		'Order updates'                      => 'Novedades de tu pedido',
		'Please enter a valid order ID'      => 'Escribe tu número de pedido.',
		'Please enter a valid email address' => 'Escribe el correo con el que compraste.',
		'Sorry, the order could not be found. Please contact us if you are having difficulty finding your order details.' => 'No encontramos un pedido con ese número y ese correo. Revisa los dos datos; si sigue sin aparecer, escríbenos.',
	);

	/**
	 * Si se está pintando el formulario de seguimiento.
	 *
	 * @var bool
	 */
	private bool $active = false;

	/**
	 * Engancha los filtros de WordPress y WooCommerce.
	 */
	public function register(): void {
		add_filter( 'pre_do_shortcode_tag', array( $this, 'start' ), 10, 2 );
		add_filter( 'do_shortcode_tag', array( $this, 'stop' ), 10, 2 );
		add_filter( 'gettext_woocommerce', array( $this, 'text' ), 10, 2 );
		add_filter( 'woocommerce_order_tracking_status', array( self::class, 'status' ), 10, 2 );
		add_filter( 'render_block_woocommerce/order-confirmation-status', array( self::class, 'confirmation_hint' ) );
		add_action( 'woocommerce_email_after_order_table', array( self::class, 'email_hint' ), 20, 3 );
	}

	/**
	 * Empieza a pintarse el formulario.
	 *
	 * @param mixed  $output Lo que devuelve el filtro (false para seguir con el shortcode).
	 * @param string $tag    Shortcode.
	 * @return mixed
	 */
	public function start( $output, string $tag ) {
		if ( 'woocommerce_order_tracking' === $tag ) {
			$this->active = true;
		}

		return $output;
	}

	/**
	 * Termina de pintarse el formulario.
	 *
	 * @param mixed  $output HTML del shortcode.
	 * @param string $tag    Shortcode.
	 * @return mixed
	 */
	public function stop( $output, string $tag ) {
		if ( 'woocommerce_order_tracking' === $tag ) {
			$this->active = false;
		}

		return $output;
	}

	/**
	 * Textos del formulario, solo mientras se pinta.
	 *
	 * @param string $translation Texto traducido.
	 * @param string $text        Texto original.
	 * @return string
	 */
	public function text( string $translation, string $text ): string {
		return $this->active && isset( self::TEXTS[ $text ] ) ? self::TEXTS[ $text ] : $translation;
	}

	/**
	 * Qué significa cada estado para quien compra.
	 *
	 * @param string $status Estado del pedido, sin "wc-".
	 * @return string Explicación, o vacío si no hay una propia.
	 */
	public static function message( string $status ): string {
		return match ( $status ) {
			'pending'    => __( 'Aún no recibimos el pago.', 'camisama-customizer' ),
			'on-hold'    => __( 'Esperamos tu transferencia: en cuanto la confirmemos, empezamos a preparar tu prenda.', 'camisama-customizer' ),
			'processing' => __( 'Pago confirmado: estamos preparando tu prenda.', 'camisama-customizer' ),
			'completed'  => __( 'Pedido completado.', 'camisama-customizer' ),
			'cancelled'  => __( 'Pedido cancelado. Si no lo esperabas, escríbenos.', 'camisama-customizer' ),
			'refunded'   => __( 'Te devolvimos el pago de este pedido.', 'camisama-customizer' ),
			'failed'     => __( 'El pago no se completó. Puedes hacer el pedido de nuevo o escribirnos.', 'camisama-customizer' ),
			default      => '',
		};
	}

	/**
	 * Frase de estado del seguimiento: número, fecha, estado y qué significa.
	 *
	 * @param string    $text  Frase de WooCommerce.
	 * @param \WC_Order $order Pedido.
	 * @return string
	 */
	public static function status( string $text, \WC_Order $order ): string {
		$message = self::message( $order->get_status() );
		if ( '' === $message ) {
			return $text;
		}

		return sprintf(
			/* translators: 1: número de pedido, 2: fecha, 3: estado, 4: qué significa. */
			__( 'Pedido n.º %1$s, del %2$s: %3$s. %4$s', 'camisama-customizer' ),
			'<mark class="order-number">' . esc_html( $order->get_order_number() ) . '</mark>',
			'<mark class="order-date">' . esc_html( wc_format_datetime( $order->get_date_created() ) ) . '</mark>',
			'<mark class="order-status">' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</mark>',
			esc_html( $message )
		);
	}

	/**
	 * Ruta de la página, o null si aún no existe.
	 */
	public static function url(): ?string {
		$page = get_page_by_path( self::SLUG );

		return $page instanceof \WP_Post && 'publish' === $page->post_status ? (string) get_permalink( $page ) : null;
	}

	/**
	 * En la confirmación: dónde seguir el pedido y con qué número.
	 *
	 * @param string $content HTML del bloque de estado.
	 * @return string
	 */
	public static function confirmation_hint( string $content ): string {
		$url = self::url();
		if ( null === $url ) {
			return $content;
		}

		// La confirmación solo enseña el pedido a quien trae su clave; el número, igual.
		$order = wc_get_order( absint( get_query_var( 'order-received' ) ) );
		$key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Solo se compara con la clave del pedido.
		$own   = $order instanceof \WC_Order && '' !== $key && hash_equals( $order->get_order_key(), $key );

		$hint = $own
			? sprintf(
				/* translators: 1: número de pedido, 2: enlace. */
				__( 'Guarda tu número de pedido, <strong>%1$s</strong>. Con él y tu correo puedes ver cómo va en %2$s.', 'camisama-customizer' ),
				esc_html( $order->get_order_number() ),
				'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Sigue tu pedido', 'camisama-customizer' ) . '</a>'
			)
			: sprintf(
				/* translators: %s: enlace. */
				__( 'Con tu número de pedido y tu correo puedes ver cómo va en %s.', 'camisama-customizer' ),
				'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Sigue tu pedido', 'camisama-customizer' ) . '</a>'
			);

		return $content . '<p class="camisama-track-hint">' . $hint . '</p>';
	}

	/**
	 * En los correos al cliente: dónde seguir el pedido.
	 *
	 * @param mixed $order         Pedido (otros correos pueden pasar otro objeto).
	 * @param bool  $sent_to_admin Si va a la tienda.
	 * @param bool  $plain_text    Si es texto plano.
	 */
	public static function email_hint( $order, $sent_to_admin, $plain_text ): void {
		$url = self::url();
		if ( $sent_to_admin || null === $url || ! $order instanceof \WC_Order ) {
			return;
		}

		/* translators: 1: número de pedido, 2: dirección de la página. */
		$text = __( 'Puedes ver cómo va tu pedido en %2$s con el número %1$s y este correo.', 'camisama-customizer' );

		if ( $plain_text ) {
			echo "\n" . esc_html( sprintf( $text, $order->get_order_number(), $url ) ) . "\n";
			return;
		}

		printf(
			'<p>%s</p>',
			wp_kses_post(
				sprintf(
					$text,
					'<strong>' . esc_html( $order->get_order_number() ) . '</strong>',
					'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Sigue tu pedido', 'camisama-customizer' ) . '</a>'
				)
			)
		);
	}
}

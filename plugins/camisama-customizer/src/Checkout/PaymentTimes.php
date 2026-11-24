<?php
/**
 * Plazos de la transferencia bancaria: cuánto hay para pagar, cuánto se tarda en preparar y entregar.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Checkout;

/**
 * PLAZOS PROVISIONALES: hay que cambiarlos por los reales del taller antes de abrir al público. Se
 * cambian solo aquí: de estos valores salen el plazo de la ficha, el carrito, el pago y las preguntas
 * de la portada, las instrucciones de la confirmación y del correo del pedido, y la fecha límite de
 * "Sigue tu pedido".
 *
 * Los textos de la transferencia se escriben encima de los guardados en WooCommerce → Ajustes →
 * Pagos → Transferencia bancaria (que siguen sirviendo para el título y los datos de la cuenta).
 */
final class PaymentTimes {

	/**
	 * Horas para hacer la transferencia desde el pedido.
	 */
	public const PAY_WITHIN_HOURS = 48;

	/**
	 * Días hábiles de preparación de la prenda con el pago confirmado.
	 */
	private const PREPARE_MIN = 2;
	private const PREPARE_MAX = 3;

	/**
	 * Días hábiles del envío a domicilio en Lima Metropolitana y el Callao, una vez lista la prenda.
	 */
	private const DELIVERY_MIN = 1;
	private const DELIVERY_MAX = 2;

	/**
	 * Tiempo de preparación.
	 */
	public const PREPARE = self::PREPARE_MIN . ' a ' . self::PREPARE_MAX . ' días hábiles';

	/**
	 * Tiempo total con envío, desde que se confirma el pago.
	 */
	public const TOTAL = ( self::PREPARE_MIN + self::DELIVERY_MIN ) . ' a ' . ( self::PREPARE_MAX + self::DELIVERY_MAX ) . ' días hábiles';

	/**
	 * Cuándo llega, dicho igual en todas partes.
	 */
	public static function promise(): string {
		return sprintf(
			/* translators: 1: tiempo con envío, 2: tiempo de preparación. */
			__( 'Llega en %1$s desde que confirmamos tu pago; si la recoges, está lista en %2$s.', 'camisama-customizer' ),
			self::TOTAL,
			self::PREPARE
		);
	}

	/**
	 * Engancha el filtro de WordPress.
	 */
	public function register(): void {
		add_filter( 'option_woocommerce_bacs_settings', array( self::class, 'bacs_texts' ) );
	}

	/**
	 * Descripción (en el pago) e instrucciones (en la confirmación y el correo) de la transferencia.
	 *
	 * @param mixed $settings Ajustes guardados de la transferencia.
	 * @return mixed
	 */
	public static function bacs_texts( $settings ) {
		if ( ! is_array( $settings ) ) {
			return $settings;
		}

		$settings['description'] = sprintf(
			/* translators: 1: horas para pagar, 2: cuándo llega. */
			__( 'Al hacer el pedido te enviamos por correo los datos de la cuenta. Tienes %1$d horas para transferir. %2$s', 'camisama-customizer' ),
			self::PAY_WITHIN_HOURS,
			self::promise()
		);
		$settings['instructions'] = sprintf(
			/* translators: 1: horas para pagar, 2: tiempo de preparación. */
			__( 'Haz la transferencia en las próximas %1$d horas e indica tu número de pedido. Cuando la confirmemos, preparamos tu prenda (%2$s) y te avisamos por correo.', 'camisama-customizer' ),
			self::PAY_WITHIN_HOURS,
			self::PREPARE
		);

		return $settings;
	}

	/**
	 * Fecha límite para transferir.
	 *
	 * @param \DateTimeInterface $created Fecha del pedido.
	 */
	public static function pay_by( \DateTimeInterface $created ): \DateTimeImmutable {
		return \DateTimeImmutable::createFromInterface( $created )->modify( '+' . self::PAY_WITHIN_HOURS . ' hours' );
	}
}

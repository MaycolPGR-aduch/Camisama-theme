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
 * cambian solo aquí: de estos valores salen el texto de "Cómo pagas" en el pago, las instrucciones de
 * la confirmación y del correo del pedido, y la fecha límite de "Sigue tu pedido".
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
	 * Tiempo de preparación de la prenda con el pago confirmado.
	 */
	public const PREPARE = '2 a 3 días hábiles';

	/**
	 * Tiempo del envío a domicilio en Lima Metropolitana y el Callao, una vez lista la prenda.
	 */
	public const DELIVERY = '1 a 2 días hábiles';

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
			/* translators: 1: horas para pagar, 2: tiempo de preparación, 3: tiempo de envío. */
			__( 'Al hacer el pedido te enviamos por correo los datos de la cuenta. Tienes %1$d horas para transferir. Con el pago confirmado, preparamos tu prenda en %2$s; si es con envío, llega en %3$s más.', 'camisama-customizer' ),
			self::PAY_WITHIN_HOURS,
			self::PREPARE,
			self::DELIVERY
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

<?php
/**
 * Pruebas de PaymentTimes.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Checkout;

use Camisama\Customizer\Checkout\PaymentTimes;
use PHPUnit\Framework\TestCase;

/**
 * Plazos de la transferencia.
 */
final class PaymentTimesTest extends TestCase {

	/**
	 * La transferencia dice cuánto tiempo hay y qué pasa después; la cuenta y el título no se tocan.
	 */
	public function test_bank_transfer_texts_say_when(): void {
		$settings = PaymentTimes::bacs_texts(
			array(
				'title'       => 'Transferencia bancaria',
				'description' => 'Texto viejo',
				'enabled'     => 'yes',
			)
		);

		$this->assertSame( 'Transferencia bancaria', $settings['title'] );
		$this->assertSame( 'yes', $settings['enabled'] );
		$this->assertStringContainsString( PaymentTimes::PAY_WITHIN_HOURS . ' horas', $settings['description'] );
		$this->assertStringContainsString( PaymentTimes::PREPARE, $settings['description'] );
		$this->assertStringContainsString( PaymentTimes::PAY_WITHIN_HOURS . ' horas', $settings['instructions'] );
	}

	/**
	 * Sin ajustes guardados todavía, no se inventan.
	 */
	public function test_leaves_missing_settings_alone(): void {
		$this->assertFalse( PaymentTimes::bacs_texts( false ) );
	}

	/**
	 * La fecha límite es la del pedido más el plazo.
	 */
	public function test_pay_by_adds_the_deadline(): void {
		$deadline = PaymentTimes::pay_by( new \DateTimeImmutable( '2026-11-04 17:18:00' ) );

		$this->assertSame( '2026-11-06 17:18', $deadline->format( 'Y-m-d H:i' ) );
	}
}

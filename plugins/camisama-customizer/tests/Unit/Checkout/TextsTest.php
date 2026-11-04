<?php
/**
 * Pruebas de Texts.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Checkout;

use Camisama\Customizer\Checkout\Texts;
use PHPUnit\Framework\TestCase;

/**
 * Textos de WooCommerce en la voz de la tienda.
 */
final class TextsTest extends TestCase {

	/**
	 * Cambia los textos conocidos y deja los demás.
	 */
	public function test_rewrites_known_texts_only(): void {
		$json = '{"locale_data":{"messages":{"Please enter a valid %s":["Por favor, introduce un %s válido"],"Add coupons":["Añadir cupones"]}}}';

		$messages = json_decode( Texts::rewrite( $json ), true )['locale_data']['messages'];

		$this->assertSame( array( 'Revisa este dato: %s' ), $messages['Please enter a valid %s'] );
		$this->assertSame( array( 'Añadir cupones' ), $messages['Add coupons'] );
	}

	/**
	 * No añade textos que ese script no usa.
	 */
	public function test_does_not_add_missing_texts(): void {
		$json = '{"locale_data":{"messages":{"Add coupons":["Añadir cupones"]}}}';

		$this->assertSame( $json, Texts::rewrite( $json ) );
	}

	/**
	 * Lo que no es un JSON de traducciones queda igual.
	 */
	public function test_leaves_other_content_alone(): void {
		$this->assertSame( 'no es json', Texts::rewrite( 'no es json' ) );
		$this->assertSame( '{"a":1}', Texts::rewrite( '{"a":1}' ) );
	}

	/**
	 * Solo actúa sobre el dominio de WooCommerce.
	 */
	public function test_filter_only_touches_woocommerce(): void {
		$json = '{"locale_data":{"messages":{"Privacy Policy":["Politica de privacidad"]}}}';

		$this->assertSame( $json, Texts::filter( $json, 'a.json', 'handle', 'otro' ) );
		$this->assertStringContainsString( 'Política de privacidad', Texts::filter( $json, 'a.json', 'handle', 'woocommerce' ) );
		$this->assertFalse( Texts::filter( false, 'a.json', 'handle', 'woocommerce' ) );
	}
}

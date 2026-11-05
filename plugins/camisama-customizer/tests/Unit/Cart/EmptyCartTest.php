<?php
/**
 * Pruebas de EmptyCart.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Cart;

use Camisama\Customizer\Cart\EmptyCart;
use PHPUnit\Framework\TestCase;

/**
 * Textos del carrito vacío.
 */
final class EmptyCartTest extends TestCase {

	/**
	 * Conserva la etiqueta que WooCommerce necesita y cambia el resto.
	 */
	public function test_keeps_the_opening_tag(): void {
		$html = EmptyCart::with_inner( "\n<div data-block-name=\"x\" class=\"y\"><h2>Vacío</h2><ul><li>A</li></ul></div>\n", '<p>Nuevo</p>' );

		$this->assertSame( "\n<div data-block-name=\"x\" class=\"y\"><p>Nuevo</p></div>", $html );
	}

	/**
	 * Sin la forma esperada no se toca nada.
	 */
	public function test_leaves_unknown_markup_alone(): void {
		$this->assertSame( '<section>Vacío</section>', EmptyCart::with_inner( '<section>Vacío</section>', '<p>Nuevo</p>' ) );
	}

	/**
	 * En el carrito lateral solo cambian el mensaje y el botón.
	 */
	public function test_replaces_mini_cart_texts(): void {
		$html = EmptyCart::with_texts(
			'<div aria-hidden="false"><p class="c"><strong>Tu carrito está vacío</strong></p><a href="/t" class="b"><div class="wc-block-components-button__text">Empezar</div></a></div>',
			'Falta tu diseño',
			'Elegir'
		);

		$this->assertStringContainsString( '<p class="c"><strong>Falta tu diseño</strong></p>', $html );
		$this->assertStringContainsString( 'button__text">Elegir</div></a>', $html );
		$this->assertStringContainsString( 'aria-hidden="false"', $html );
	}
}

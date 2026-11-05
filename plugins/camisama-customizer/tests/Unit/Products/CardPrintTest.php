<?php
/**
 * Pruebas de CardPrint.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Products;

use Camisama\Customizer\Config\Rect;
use Camisama\Customizer\Products\CardPrint;
use PHPUnit\Framework\TestCase;

/**
 * Estampado de muestra en las tarjetas.
 */
final class CardPrintTest extends TestCase {

	/**
	 * Va justo después de la imagen, dentro del enlace.
	 */
	public function test_goes_after_the_image(): void {
		$this->assertSame(
			'<div><a href="/p"><img src="a.png"><i></i></a></div>',
			CardPrint::with_print( '<div><a href="/p"><img src="a.png"></a></div>', '<i></i>' )
		);
	}

	/**
	 * Sin imagen no se añade nada.
	 */
	public function test_without_image_leaves_the_block_alone(): void {
		$this->assertSame( '<div></div>', CardPrint::with_print( '<div></div>', '<i></i>' ) );
	}

	/**
	 * Queda centrado en la zona, con el 70 % de sus lados y pegado a su parte alta.
	 */
	public function test_is_centered_in_the_zone(): void {
		$this->assertSame(
			'left:43.00%;top:22.00%;width:14.00%;height:28.00%',
			CardPrint::position( new Rect( 0.4, 0.2, 0.2, 0.4 ) )
		);
	}
}

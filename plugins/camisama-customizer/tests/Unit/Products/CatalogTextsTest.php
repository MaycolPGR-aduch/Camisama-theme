<?php
/**
 * Pruebas de CatalogTexts.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Products;

use Camisama\Customizer\Products\CatalogTexts;
use PHPUnit\Framework\TestCase;

/**
 * Textos de las tarjetas.
 */
final class CatalogTextsTest extends TestCase {

	/**
	 * La tarjeta enseña la primera frase entera, sin cortarla en un número con decimales.
	 */
	public function test_first_sentence(): void {
		$this->assertSame(
			'Corte recto de algodón peinado 100 %, 180 g/m².',
			CatalogTexts::first_sentence( 'Corte recto de algodón peinado 100 %, 180 g/m². La base perfecta para cualquier diseño.' )
		);
		$this->assertSame( 'Pesa 1.5 kg.', CatalogTexts::first_sentence( 'Pesa 1.5 kg. Es abrigadora.' ) );
		$this->assertSame( 'Sin punto final', CatalogTexts::first_sentence( ' Sin punto final ' ) );
		$this->assertSame( '', CatalogTexts::first_sentence( '' ) );
	}
}

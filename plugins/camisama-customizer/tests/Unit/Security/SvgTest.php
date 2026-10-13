<?php
/**
 * Pruebas del saneado y las dimensiones de SVG.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Security;

use Camisama\Customizer\Security\SvgDimensions;
use Camisama\Customizer\Security\SvgSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * Un SVG subido nunca debe poder ejecutar código ni cargar recursos externos.
 */
final class SvgTest extends TestCase {

	private const CIRCLE = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 50"><circle cx="25" cy="25" r="20" fill="#D3261C"/></svg>';

	public function test_keeps_safe_drawing(): void {
		$clean = ( new SvgSanitizer() )->sanitize( self::CIRCLE );

		self::assertNotNull( $clean );
		self::assertStringContainsString( '<circle', $clean );
	}

	public function test_removes_scripts_and_event_handlers(): void {
		$dirty = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><script>alert(2)</script><rect width="10" height="10" onclick="alert(3)"/></svg>';
		$clean = (string) ( new SvgSanitizer() )->sanitize( $dirty );

		self::assertStringNotContainsStringIgnoringCase( 'script', $clean );
		self::assertStringNotContainsStringIgnoringCase( 'alert', $clean );
		self::assertStringContainsString( '<rect', $clean );
	}

	public function test_removes_javascript_links_and_remote_references(): void {
		$dirty = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><a xlink:href="javascript:alert(1)"><rect width="5" height="5"/></a><image href="https://example.com/track.png" width="5" height="5"/></svg>';
		$clean = (string) ( new SvgSanitizer() )->sanitize( $dirty );

		self::assertStringNotContainsStringIgnoringCase( 'javascript:', $clean );
		self::assertStringNotContainsString( 'example.com', $clean );
	}

	public function test_rejects_non_svg(): void {
		$sanitizer = new SvgSanitizer();

		self::assertNull( $sanitizer->sanitize( '' ) );
		self::assertNull( $sanitizer->sanitize( 'esto no es xml' ) );
		self::assertNull( $sanitizer->sanitize( '<html><body>hola</body></html>' ) );
	}

	public function test_dimensions_from_view_box(): void {
		self::assertSame(
			array(
				'width'  => 100.0,
				'height' => 50.0,
			),
			SvgDimensions::from_markup( self::CIRCLE )
		);
	}

	public function test_dimensions_prefer_width_and_height(): void {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300px" height="120" viewBox="0 0 100 40"></svg>';

		self::assertSame(
			array(
				'width'  => 300.0,
				'height' => 120.0,
			),
			SvgDimensions::from_markup( $svg )
		);
	}

	public function test_percentages_fall_back_to_view_box(): void {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0,0,64,32"></svg>';

		self::assertSame(
			array(
				'width'  => 64.0,
				'height' => 32.0,
			),
			SvgDimensions::from_markup( $svg )
		);
	}

	public function test_unknown_dimensions(): void {
		self::assertNull( SvgDimensions::from_markup( '<svg xmlns="http://www.w3.org/2000/svg"></svg>' ) );
		self::assertNull( SvgDimensions::from_markup( '<p>no</p>' ) );
	}
}

<?php
/**
 * Pruebas de la validación del diseño.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Design;

use Camisama\Customizer\Config\Presets;
use Camisama\Customizer\Design\DesignValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * El navegador puede mandar cualquier cosa: solo pasa un diseño coherente con el producto.
 */
final class DesignValidatorTest extends TestCase {

	private const UPLOAD = 'c02eb219748d55a1509d70d0c0861ed2';

	private function validator(): DesignValidator {
		return new DesignValidator(
			Presets::adult(),
			array( 'blanco', 'negro' ),
			static fn ( int $id ): ?array => 7 === $id
				? array(
					'title'   => 'Llama',
					'url'     => 'http://camisama.test/llama.svg',
					'mime'    => 'image/svg+xml',
					'premium' => 500,
				)
				: null,
			static fn ( string $token ): ?array => self::UPLOAD === $token
				? array(
					'url'    => 'http://camisama.test/foto.png',
					'width'  => 1200,
					'height' => 900,
				)
				: null
		);
	}

	/**
	 * Elementos válidos de cada tipo.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function elements(): array {
		return array(
			'design' => array(
				'id'        => 'el-1',
				'zone'      => 'front',
				'kind'      => 'design',
				'design_id' => 7,
				'cx'        => 15,
				'cy'        => 20,
				'width_cm'  => 18,
				'height_cm' => 25.6,
				'angle'     => 0,
				// Lo que manda el navegador sobre URL o recargo se ignora.
				'src'       => 'https://evil.example/x.svg',
				'premium'   => 0,
			),
			'text'   => array(
				'id'           => 'el-2',
				'zone'         => 'left-sleeve',
				'kind'         => 'text',
				'text'         => "  LIMA\u{0007}  ",
				'font'         => 'pacifico',
				'color'        => '#d3261c',
				'align'        => 'center',
				'font_size_cm' => 1.4,
				'cx'           => 4.5,
				'cy'           => 4.5,
				'width_cm'     => 5,
				'height_cm'    => 2,
				'angle'        => -90,
			),
			'image'  => array(
				'id'        => 'el-3',
				'zone'      => 'back',
				'kind'      => 'image',
				'upload_id' => self::UPLOAD,
				'cx'        => 15,
				'cy'        => 20,
				'width_cm'  => 10,
				'height_cm' => 7.5,
				'angle'     => 30,
			),
		);
	}

	public function test_normalizes_a_valid_design(): void {
		$result = $this->validator()->validate(
			array(
				'color'    => 'negro',
				'elements' => array_values( self::elements() ),
			)
		);

		$document = $result['document'];
		self::assertSame( 'negro', $document['color'] );
		self::assertCount( 3, $result['elements'] );

		list( $design, $text, $image ) = $document['elements'];
		self::assertSame( 'http://camisama.test/llama.svg', $design['src'], 'la URL sale del servidor' );
		self::assertSame( 500, $design['premium'], 'el recargo sale del servidor' );
		self::assertSame( 'LIMA', $text['text'], 'sin espacios ni caracteres de control' );
		self::assertSame( '#D3261C', $text['color'] );
		self::assertEquals( 270, $text['angle'], 'ángulo normalizado' );
		self::assertSame( 1200, $image['pixel_width'] );
		self::assertSame( array( 7 => 500 ), $result['premiums'] );
		self::assertSame( array( 'front', 'left-sleeve', 'back' ), array_column( $document['zones'], 'id' ), 'copia de las zonas usadas' );
	}

	public function test_empty_design_is_valid(): void {
		$result = $this->validator()->validate(
			array(
				'color'    => 'blanco',
				'elements' => array(),
			)
		);

		self::assertSame( array(), $result['elements'] );
		self::assertSame( array(), $result['document']['zones'] );
	}

	/**
	 * Diseños que deben rechazarse y el mensaje esperado.
	 *
	 * @return array<string, array{callable(array<string, array<string, mixed>>): array<string, mixed>, string}>
	 */
	public static function invalid(): array {
		$with = static fn ( string $kind, array $change ): callable => static fn ( array $elements ): array => array(
			'color'    => 'blanco',
			'elements' => array( array_merge( $elements[ $kind ], $change ) ),
		);

		return array(
			'color que el producto no tiene'     => array( static fn (): array => array( 'color' => 'verde-neon' ), 'color de prenda' ),
			'elementos que no son una lista'     => array(
				static fn (): array => array(
					'color'    => 'blanco',
					'elements' => array( 'a' => 1 ),
				),
				'dañado',
			),
			'demasiados elementos'               => array(
				static fn ( array $e ): array => array(
					'color'    => 'blanco',
					'elements' => array_fill( 0, 41, $e['design'] ),
				),
				'demasiados',
			),
			'zona inexistente'                   => array( $with( 'design', array( 'zone' => 'pocket' ) ), 'zona' ),
			'tipo desconocido'                   => array( $with( 'design', array( 'kind' => 'video' ) ), 'dañado' ),
			'medida no numérica'                 => array( $with( 'design', array( 'cx' => 'NaN' ) ), 'dañado' ),
			'medida infinita'                    => array( $with( 'design', array( 'width_cm' => INF ) ), 'dañado' ),
			'se sale de la zona'                 => array( $with( 'design', array( 'cx' => 28 ) ), 'se sale' ),
			'estampado que no existe'            => array( $with( 'design', array( 'design_id' => 99 ) ), 'estampados' ),
			'texto vacío'                        => array( $with( 'text', array( 'text' => "  \t " ) ), 'caracteres' ),
			'texto demasiado largo'              => array( $with( 'text', array( 'text' => str_repeat( 'a', 61 ) ) ), 'caracteres' ),
			'fuente no permitida'                => array( $with( 'text', array( 'font' => 'comic-sans' ) ), 'fuentes' ),
			'color de texto no hexadecimal'      => array( $with( 'text', array( 'color' => 'red;background:url(x)' ) ), 'color del texto' ),
			'imagen de otra persona o inventada' => array( $with( 'image', array( 'upload_id' => str_repeat( 'f', 32 ) ) ), 'imágenes' ),
			'token de imagen con otro formato'   => array( $with( 'image', array( 'upload_id' => '../../wp-config' ) ), 'imágenes' ),
			'identificador de elemento extraño'  => array( $with( 'design', array( 'id' => '<script>' ) ), 'dañado' ),
		);
	}

	/**
	 * Rechaza diseños incoherentes con un mensaje para el cliente.
	 *
	 * @param callable $build    Construye el diseño a partir de los elementos válidos.
	 * @param string   $fragment Parte del mensaje esperado.
	 */
	#[DataProvider( 'invalid' )]
	public function test_rejects_invalid_design( callable $build, string $fragment ): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/' . preg_quote( $fragment, '/' ) . '/iu' );

		$this->validator()->validate( $build( self::elements() ) );
	}
}

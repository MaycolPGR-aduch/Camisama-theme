<?php
/**
 * Pruebas de la validación de subidas y de las rutas seguras.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Tests\Unit\Uploads;

use Camisama\Customizer\Uploads\SafePath;
use Camisama\Customizer\Uploads\UploadValidator;
use PHPUnit\Framework\TestCase;

// phpcs:disable WordPress.WP.AlternativeFunctions -- archivos temporales de prueba.

/**
 * Solo pasan imágenes reales de los formatos permitidos y dentro de los límites.
 */
final class UploadValidatorTest extends TestCase {

	/**
	 * Carpeta temporal de la prueba.
	 *
	 * @var string
	 */
	private string $dir;

	protected function setUp(): void {
		$this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'camisama-test-' . bin2hex( random_bytes( 4 ) );
		mkdir( $this->dir );
	}

	protected function tearDown(): void {
		foreach ( (array) glob( $this->dir . '/*' ) as $file ) {
			unlink( (string) $file );
		}
		rmdir( $this->dir );
	}

	public function test_accepts_png_jpeg_and_webp(): void {
		$validator = new UploadValidator();

		self::assertSame( 'png', $validator->validate( $this->image( 'a.png', 'imagepng' ) )['ext'] );
		self::assertSame( 'jpg', $validator->validate( $this->image( 'b.jpg', 'imagejpeg' ) )['ext'] );
		self::assertSame( 'webp', $validator->validate( $this->image( 'c.webp', 'imagewebp' ) )['ext'] );

		$result = $validator->validate( $this->image( 'd.png', 'imagepng', 320, 200 ) );
		self::assertSame( 320, $result['width'] );
		self::assertSame( 200, $result['height'] );
	}

	public function test_rejects_php_disguised_as_png(): void {
		$path = $this->dir . '/foto.png';
		file_put_contents( $path, '<?php system($_GET["c"]); ?>' );

		$this->expectExceptionMessage( 'Formato no admitido' );
		( new UploadValidator() )->validate( $path );
	}

	public function test_rejects_gif(): void {
		$this->expectExceptionMessage( 'Formato no admitido' );
		( new UploadValidator() )->validate( $this->image( 'e.gif', 'imagegif' ) );
	}

	/**
	 * Aquí solo se leen las cabeceras: una imagen cortada después de la cabecera la rechaza
	 * UploadStore al decodificarla entera para recodificarla.
	 */
	public function test_rejects_image_without_header(): void {
		$path = $this->image( 'f.png', 'imagepng' );
		file_put_contents( $path, substr( (string) file_get_contents( $path ), 0, 12 ) );

		$this->expectException( \InvalidArgumentException::class );
		( new UploadValidator() )->validate( $path );
	}

	public function test_rejects_heavy_files(): void {
		$this->expectExceptionMessage( 'pesa demasiado' );
		( new UploadValidator( max_bytes: 100 ) )->validate( $this->image( 'g.png', 'imagepng' ) );
	}

	public function test_rejects_tiny_images(): void {
		$this->expectExceptionMessage( 'demasiado pequeña' );
		( new UploadValidator() )->validate( $this->image( 'h.png', 'imagepng', 50, 400 ) );
	}

	public function test_rejects_too_many_pixels(): void {
		$this->expectExceptionMessage( 'demasiados píxeles' );
		( new UploadValidator( max_pixels: 10_000 ) )->validate( $this->image( 'i.png', 'imagepng' ) );
	}

	public function test_rejects_missing_file(): void {
		$this->expectExceptionMessage( 'No llegó ninguna imagen' );
		( new UploadValidator() )->validate( $this->dir . '/no-existe.png' );
	}

	public function test_safe_path_allows_only_files_below_root(): void {
		$root = $this->dir . '/raiz';
		mkdir( $root );
		$inside = $root . '/archivo.png';
		file_put_contents( $inside, 'x' );
		$outside = $this->dir . '/fuera.png';
		file_put_contents( $outside, 'x' );

		self::assertTrue( SafePath::is_file_within( $inside, $root ) );
		self::assertFalse( SafePath::is_file_within( $outside, $root ) );
		self::assertFalse( SafePath::is_file_within( $root . '/../fuera.png', $root ), 'no se sale con ..' );
		self::assertFalse( SafePath::is_file_within( $root, $root ), 'la carpeta raíz no es un archivo borrable' );
		self::assertFalse( SafePath::is_file_within( $root . '/no-existe.png', $root ) );

		unlink( $inside );
		rmdir( $root );
	}

	/**
	 * Crea una imagen de prueba con GD.
	 *
	 * @param string   $name   Nombre del archivo.
	 * @param callable $writer imagepng, imagejpeg, imagewebp o imagegif.
	 * @param int      $width  Ancho.
	 * @param int      $height Alto.
	 */
	private function image( string $name, callable $writer, int $width = 200, int $height = 200 ): string {
		$path  = $this->dir . '/' . $name;
		$image = imagecreatetruecolor( $width, $height );
		imagefill( $image, 0, 0, (int) imagecolorallocate( $image, 211, 38, 28 ) );
		$writer( $image, $path );

		return $path;
	}
}

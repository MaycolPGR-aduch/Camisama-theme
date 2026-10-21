<?php
/**
 * Validación del diseño que envía el navegador.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Design;

use Camisama\Customizer\Config\ProductConfig;
use Camisama\Customizer\Config\Zone;

/**
 * Convierte el JSON del editor en un diseño normalizado en el que se puede confiar:
 *
 * - solo zonas del producto, medidas finitas y dentro de su zona (con 0,5 cm de tolerancia);
 * - textos con fuente permitida, color hexadecimal y longitud limitada;
 * - estampados e imágenes que existen en el servidor: sus URLs, medidas y recargos salen de la
 *   base de datos, nunca de lo que manda el cliente.
 *
 * El resultado incluye una copia de las zonas usadas, para que el pedido se pueda producir igual
 * aunque después cambie la configuración del producto.
 */
final class DesignValidator {

	public const MAX_ELEMENTS = 40;
	public const MAX_TEXT     = 60;

	private const TOLERANCE_CM = 0.5;
	private const ALIGNS       = array( 'left', 'center', 'right' );

	/**
	 * Busca un estampado publicado: ID => [title, url, mime, premium] o null.
	 *
	 * @var callable(int): ?array{title: string, url: string, mime: string, premium: int}
	 */
	private $designs;

	/**
	 * Busca una subida: token => [url, width, height] o null.
	 *
	 * @var callable(string): ?array{url: string, width: int, height: int}
	 */
	private $uploads;

	/**
	 * Constructor.
	 *
	 * @param ProductConfig                                                                 $config  Zonas del producto.
	 * @param list<string>                                                                  $colors  Colores válidos del producto.
	 * @param callable(int): ?array{title: string, url: string, mime: string, premium: int} $designs Búsqueda de estampados.
	 * @param callable(string): ?array{url: string, width: int, height: int}                $uploads Búsqueda de subidas.
	 */
	public function __construct(
		private readonly ProductConfig $config,
		private readonly array $colors,
		callable $designs,
		callable $uploads,
	) {
		$this->designs = $designs;
		$this->uploads = $uploads;
	}

	/**
	 * Valida y normaliza el diseño.
	 *
	 * @param array<string, mixed> $data JSON del editor ya decodificado.
	 * @return array{document: array<string, mixed>, elements: list<Element>, premiums: array<int, int>}
	 *
	 * @throws \InvalidArgumentException Con un mensaje para el cliente si algo no es válido.
	 */
	public function validate( array $data ): array {
		$color = (string) ( $data['color'] ?? '' );
		if ( ! in_array( $color, $this->colors, true ) ) {
			throw new \InvalidArgumentException( 'Elige un color de prenda válido.' );
		}

		$raw = $data['elements'] ?? array();
		if ( ! is_array( $raw ) || ! array_is_list( $raw ) ) {
			throw new \InvalidArgumentException( 'El diseño llegó dañado. Vuelve a intentarlo.' );
		}
		if ( count( $raw ) > self::MAX_ELEMENTS ) {
			throw new \InvalidArgumentException( sprintf( 'El diseño tiene demasiados elementos (máximo %d).', self::MAX_ELEMENTS ) );
		}

		$document = array();
		$elements = array();
		$premiums = array();
		$used     = array();

		foreach ( $raw as $item ) {
			if ( ! is_array( $item ) ) {
				throw new \InvalidArgumentException( 'El diseño llegó dañado. Vuelve a intentarlo.' );
			}

			list( $normalized, $element, $zone ) = $this->element( $item );
			$document[]                          = $normalized;
			$elements[]                          = $element;
			$used[ $zone->id ]                   = $zone;

			if ( 'design' === $normalized['kind'] ) {
				$premiums[ $normalized['design_id'] ] = $normalized['premium'];
			}
		}

		$zones = array();
		foreach ( $this->config->zones as $zone ) {
			if ( isset( $used[ $zone->id ] ) ) {
				$zones[] = $zone->to_array();
			}
		}

		return array(
			'document' => array(
				'version'  => 1,
				'color'    => $color,
				'elements' => $document,
				'zones'    => $zones,
			),
			'elements' => $elements,
			'premiums' => $premiums,
		);
	}

	/**
	 * Valida un elemento.
	 *
	 * @param array<string, mixed> $item Elemento enviado.
	 * @return array{array<string, mixed>, Element, Zone}
	 *
	 * @throws \InvalidArgumentException Si no es válido.
	 */
	private function element( array $item ): array {
		$zone = $this->config->zone( (string) ( $item['zone'] ?? '' ) );
		if ( null === $zone ) {
			throw new \InvalidArgumentException( 'El diseño usa una zona que esta prenda no tiene.' );
		}

		$kind = (string) ( $item['kind'] ?? '' );
		$id   = (string) ( $item['id'] ?? '' );
		if ( 1 !== preg_match( '/^[A-Za-z0-9_-]{1,40}$/', $id ) ) {
			throw new \InvalidArgumentException( 'El diseño llegó dañado. Vuelve a intentarlo.' );
		}

		$geometry = array();
		foreach ( array( 'cx', 'cy', 'width_cm', 'height_cm', 'angle' ) as $key ) {
			$value = $item[ $key ] ?? ( 'angle' === $key ? 0 : null );
			if ( ! is_numeric( $value ) || ! is_finite( (float) $value ) ) {
				throw new \InvalidArgumentException( 'El diseño llegó dañado. Vuelve a intentarlo.' );
			}
			$geometry[ $key ] = round( (float) $value, 2 );
		}
		$geometry['angle'] = fmod( fmod( $geometry['angle'], 360 ) + 360, 360 );

		$normalized = array(
			'id'   => $id,
			'zone' => $zone->id,
			'kind' => $kind,
		) + $geometry + $this->kind_fields( $kind, $item, $zone );

		$element = new Element( $zone->id, $kind, $geometry['cx'], $geometry['cy'], $geometry['width_cm'], $geometry['height_cm'], $geometry['angle'], $normalized['design_id'] ?? null );

		$bounds = $element->bounds();
		if (
			$bounds['left'] < -self::TOLERANCE_CM
			|| $bounds['top'] < -self::TOLERANCE_CM
			|| $bounds['right'] > $zone->width_cm + self::TOLERANCE_CM
			|| $bounds['bottom'] > $zone->height_cm + self::TOLERANCE_CM
		) {
			throw new \InvalidArgumentException( sprintf( 'Un elemento se sale de la zona %s. Muévelo o redúcelo.', $zone->label ) );
		}

		return array( $normalized, $element, $zone );
	}

	/**
	 * Campos propios de cada tipo de elemento.
	 *
	 * @param string               $kind Tipo.
	 * @param array<string, mixed> $item Elemento enviado.
	 * @param Zone                 $zone Zona del elemento.
	 * @return array<string, mixed>
	 *
	 * @throws \InvalidArgumentException Si no es válido.
	 */
	private function kind_fields( string $kind, array $item, Zone $zone ): array {
		switch ( $kind ) {
			case 'design':
				$design_id = is_numeric( $item['design_id'] ?? null ) ? (int) $item['design_id'] : 0;
				$design    = $design_id > 0 ? ( $this->designs )( $design_id ) : null;
				if ( null === $design ) {
					throw new \InvalidArgumentException( 'Uno de los estampados ya no está disponible. Quítalo del diseño.' );
				}

				return array(
					'design_id' => $design_id,
					'title'     => $design['title'],
					'src'       => $design['url'],
					'mime'      => $design['mime'],
					'premium'   => max( 0, $design['premium'] ),
				);

			case 'text':
				$text = trim( preg_replace( '/[^\P{C}\n]/u', '', (string) ( $item['text'] ?? '' ) ) ?? '' );
				if ( '' === $text || mb_strlen( $text ) > self::MAX_TEXT ) {
					throw new \InvalidArgumentException( sprintf( 'Cada texto debe tener entre 1 y %d caracteres.', self::MAX_TEXT ) );
				}

				$font = (string) ( $item['font'] ?? '' );
				if ( ! Fonts::exists( $font ) ) {
					throw new \InvalidArgumentException( 'Elige una de las fuentes disponibles.' );
				}

				$color = strtoupper( (string) ( $item['color'] ?? '' ) );
				if ( 1 !== preg_match( '/^#[0-9A-F]{6}$/', $color ) ) {
					throw new \InvalidArgumentException( 'El color del texto no es válido.' );
				}

				$align     = in_array( $item['align'] ?? null, self::ALIGNS, true ) ? (string) $item['align'] : 'center';
				$font_size = is_numeric( $item['font_size_cm'] ?? null ) ? round( (float) $item['font_size_cm'], 2 ) : 0.0;
				if ( $font_size < 0.2 || $font_size > max( $zone->width_cm, $zone->height_cm ) ) {
					throw new \InvalidArgumentException( 'El tamaño del texto no es válido.' );
				}

				return array(
					'text'         => $text,
					'font'         => $font,
					'color'        => $color,
					'align'        => $align,
					'font_size_cm' => $font_size,
				);

			case 'image':
				$token  = (string) ( $item['upload_id'] ?? '' );
				$upload = 1 === preg_match( '/^[a-f0-9]{32}$/', $token ) ? ( $this->uploads )( $token ) : null;
				if ( null === $upload ) {
					throw new \InvalidArgumentException( 'Una de tus imágenes ya no está disponible. Súbela de nuevo.' );
				}

				return array(
					'upload_id'    => $token,
					'src'          => $upload['url'],
					'pixel_width'  => $upload['width'],
					'pixel_height' => $upload['height'],
				);

			default:
				throw new \InvalidArgumentException( 'El diseño llegó dañado. Vuelve a intentarlo.' );
		}
	}
}

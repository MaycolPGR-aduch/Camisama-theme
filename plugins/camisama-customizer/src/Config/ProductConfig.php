<?php
/**
 * Configuración del personalizador para un producto.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Config;

/**
 * Vistas y zonas de impresión de un modelo de prenda.
 */
final class ProductConfig {

	public const VERSION = 1;

	/**
	 * Constructor.
	 *
	 * @param bool       $enabled Si el producto se personaliza.
	 * @param list<View> $views   Vistas en orden de presentación.
	 * @param list<Zone> $zones   Zonas de impresión.
	 *
	 * @throws \InvalidArgumentException Si hay identificadores repetidos o zonas en vistas inexistentes.
	 */
	public function __construct(
		public readonly bool $enabled,
		public readonly array $views,
		public readonly array $zones,
	) {
		$view_ids = array_map( static fn ( View $view ): string => $view->id, $views );
		if ( count( $view_ids ) !== count( array_unique( $view_ids ) ) ) {
			throw new \InvalidArgumentException( 'Hay dos vistas con el mismo identificador.' );
		}

		$zone_ids = array_map( static fn ( Zone $zone ): string => $zone->id, $zones );
		if ( count( $zone_ids ) !== count( array_unique( $zone_ids ) ) ) {
			throw new \InvalidArgumentException( 'Hay dos zonas con el mismo identificador.' );
		}

		foreach ( $zones as $zone ) {
			if ( ! in_array( $zone->view, $view_ids, true ) ) {
				throw new \InvalidArgumentException( "La zona \"{$zone->label}\" está en una vista que no existe." );
			}
		}

		if ( $enabled && array() === $zones ) {
			throw new \InvalidArgumentException( 'Para activar el personalizador el producto necesita al menos una zona de impresión.' );
		}
	}

	/**
	 * Configuración vacía (producto no personalizable).
	 */
	public static function disabled(): self {
		return new self( false, array(), array() );
	}

	/**
	 * Crea la configuración desde un array (meta guardado o formulario del administrador).
	 *
	 * @param array<string, mixed> $data Claves enabled, views y zones.
	 *
	 * @throws \InvalidArgumentException Si algún dato no es válido.
	 */
	public static function from_array( array $data ): self {
		$views = array();
		foreach ( (array) ( $data['views'] ?? array() ) as $view ) {
			if ( ! is_array( $view ) ) {
				throw new \InvalidArgumentException( 'Vista incompleta.' );
			}
			$views[] = View::from_array( $view );
		}

		$zones = array();
		foreach ( (array) ( $data['zones'] ?? array() ) as $zone ) {
			if ( ! is_array( $zone ) ) {
				throw new \InvalidArgumentException( 'Zona de impresión incompleta.' );
			}
			$zones[] = Zone::from_array( $zone );
		}

		return new self( ! empty( $data['enabled'] ), $views, $zones );
	}

	/**
	 * Zonas de una vista.
	 *
	 * @param string $view_id Identificador de la vista.
	 * @return list<Zone>
	 */
	public function zones_in( string $view_id ): array {
		return array_values( array_filter( $this->zones, static fn ( Zone $zone ): bool => $zone->view === $view_id ) );
	}

	/**
	 * Zona por identificador.
	 *
	 * @param string $zone_id Identificador de la zona.
	 */
	public function zone( string $zone_id ): ?Zone {
		foreach ( $this->zones as $zone ) {
			if ( $zone->id === $zone_id ) {
				return $zone;
			}
		}

		return null;
	}

	/**
	 * Representación serializable.
	 *
	 * @return array{version: int, enabled: bool, views: list<array<string, mixed>>, zones: list<array<string, mixed>>}
	 */
	public function to_array(): array {
		return array(
			'version' => self::VERSION,
			'enabled' => $this->enabled,
			'views'   => array_map( static fn ( View $view ): array => $view->to_array(), $this->views ),
			'zones'   => array_map( static fn ( Zone $zone ): array => $zone->to_array(), $this->zones ),
		);
	}
}

<?php
/**
 * Persistencia de la configuración del personalizador.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Config;

/**
 * Guarda la configuración como JSON en un metadato del producto.
 */
final class ProductConfigRepository {

	public const META_KEY = '_camisama_customizer';

	/**
	 * Configuración del producto; si no tiene o está dañada, devuelve una desactivada.
	 *
	 * @param int $product_id ID del producto.
	 */
	public function get( int $product_id ): ProductConfig {
		$json = get_post_meta( $product_id, self::META_KEY, true );
		$data = is_string( $json ) && '' !== $json ? json_decode( $json, true ) : null;

		if ( ! is_array( $data ) ) {
			return ProductConfig::disabled();
		}

		try {
			return ProductConfig::from_array( $data );
		} catch ( \InvalidArgumentException ) {
			return ProductConfig::disabled();
		}
	}

	/**
	 * Guarda la configuración.
	 *
	 * @param int           $product_id ID del producto.
	 * @param ProductConfig $config     Configuración validada.
	 */
	public function save( int $product_id, ProductConfig $config ): void {
		update_post_meta( $product_id, self::META_KEY, wp_slash( (string) wp_json_encode( $config->to_array() ) ) );
	}
}

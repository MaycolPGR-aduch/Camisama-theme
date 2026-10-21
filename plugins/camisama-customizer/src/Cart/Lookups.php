<?php
/**
 * Búsquedas que necesita la validación del diseño.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Cart;

use Camisama\Customizer\Designs\DesignPostType;

/**
 * Datos de los estampados del catálogo, tal como están en la base de datos.
 */
final class Lookups {

	/**
	 * Estampado publicado con archivo, o null.
	 *
	 * @param int $id ID del estampado.
	 * @return array{title: string, url: string, mime: string, premium: int}|null
	 */
	public static function design( int $id ): ?array {
		$post = get_post( $id );
		if ( ! $post instanceof \WP_Post || DesignPostType::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
			return null;
		}

		$file_id = (int) get_post_thumbnail_id( $post );
		$image   = $file_id ? wp_get_attachment_image_src( $file_id, 'full' ) : false;
		if ( false === $image ) {
			return null;
		}

		return array(
			'title'   => get_the_title( $post ),
			'url'     => (string) $image[0],
			'mime'    => (string) get_post_mime_type( $file_id ),
			'premium' => DesignPostType::premium( $id ),
		);
	}
}

<?php
/**
 * Consulta del catálogo de estampados.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Designs;

/**
 * Estampados publicados con archivo, en el formato que consume el editor.
 */
final class DesignRepository {

	/**
	 * Página de estampados.
	 *
	 * @param string|null $category Slug de categoría, o null para todas.
	 * @param int         $page     Página (desde 1).
	 * @param int         $per_page Elementos por página.
	 * @return array{items: list<array<string, mixed>>, total: int, pages: int}
	 */
	public function query( ?string $category, int $page, int $per_page ): array {
		$args = array(
			'post_type'      => DesignPostType::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- solo estampados con archivo.
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		);

		if ( null !== $category ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => DesignPostType::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $category,
				),
			);
		}

		$query = new \WP_Query( $args );
		$items = array();

		foreach ( $query->posts as $post ) {
			if ( $post instanceof \WP_Post ) {
				$item = $this->present( $post );
				if ( null !== $item ) {
					$items[] = $item;
				}
			}
		}

		return array(
			'items' => $items,
			'total' => (int) $query->found_posts,
			'pages' => (int) $query->max_num_pages,
		);
	}

	/**
	 * Categorías con estampados.
	 *
	 * @return list<array{slug: string, name: string, count: int}>
	 */
	public function categories(): array {
		$terms = get_terms(
			array(
				'taxonomy'   => DesignPostType::TAXONOMY,
				'hide_empty' => true,
			)
		);

		if ( ! is_array( $terms ) ) {
			return array();
		}

		return array_values(
			array_map(
				static fn ( \WP_Term $term ): array => array(
					'slug'  => $term->slug,
					'name'  => $term->name,
					'count' => $term->count,
				),
				$terms
			)
		);
	}

	/**
	 * Datos públicos de un estampado, o null si no tiene archivo de imagen.
	 *
	 * @param \WP_Post $post Estampado.
	 * @return array<string, mixed>|null
	 */
	private function present( \WP_Post $post ): ?array {
		$file_id = (int) get_post_thumbnail_id( $post );
		$image   = $file_id ? wp_get_attachment_image_src( $file_id, 'full' ) : false;

		if ( false === $image ) {
			return null;
		}

		$terms = get_the_terms( $post, DesignPostType::TAXONOMY );

		return array(
			'id'         => $post->ID,
			'title'      => get_the_title( $post ),
			'categories' => is_array( $terms ) ? array_map( static fn ( \WP_Term $term ): string => $term->slug, $terms ) : array(),
			'file'       => array(
				'url'    => (string) $image[0],
				'mime'   => (string) get_post_mime_type( $file_id ),
				'width'  => (int) $image[1],
				'height' => (int) $image[2],
			),
			'premium'    => DesignPostType::premium( $post->ID ),
		);
	}
}

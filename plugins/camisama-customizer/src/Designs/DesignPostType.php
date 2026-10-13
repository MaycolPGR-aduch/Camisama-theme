<?php
/**
 * Catálogo de estampados.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Designs;

use Camisama\Customizer\Pricing\Money;

/**
 * Tipo de contenido "Estampado": título, archivo (imagen destacada, PNG o SVG), categorías y recargo premium.
 */
final class DesignPostType {

	public const POST_TYPE    = 'camisama_design';
	public const TAXONOMY     = 'camisama_design_cat';
	public const META_PREMIUM = '_camisama_premium';

	private const NONCE = 'camisama_design_premium';

	/**
	 * Engancha el registro y la administración.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'register_types' ) );
		add_action( 'add_meta_boxes_' . self::POST_TYPE, array( $this, 'add_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_premium' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	/**
	 * Registra el tipo de contenido y su taxonomía.
	 */
	public function register_types(): void {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'          => array(
					'name'                  => __( 'Estampados', 'camisama-customizer' ),
					'singular_name'         => __( 'Estampado', 'camisama-customizer' ),
					'add_new'               => __( 'Añadir estampado', 'camisama-customizer' ),
					'add_new_item'          => __( 'Añadir estampado', 'camisama-customizer' ),
					'edit_item'             => __( 'Editar estampado', 'camisama-customizer' ),
					'new_item'              => __( 'Nuevo estampado', 'camisama-customizer' ),
					'search_items'          => __( 'Buscar estampados', 'camisama-customizer' ),
					'not_found'             => __( 'No hay estampados.', 'camisama-customizer' ),
					'all_items'             => __( 'Todos los estampados', 'camisama-customizer' ),
					'featured_image'        => __( 'Archivo del estampado', 'camisama-customizer' ),
					'set_featured_image'    => __( 'Elegir archivo (SVG o PNG transparente)', 'camisama-customizer' ),
					'remove_featured_image' => __( 'Quitar archivo', 'camisama-customizer' ),
					'use_featured_image'    => __( 'Usar como archivo del estampado', 'camisama-customizer' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_position'   => 56,
				'menu_icon'       => 'dashicons-art',
				'supports'        => array( 'title', 'thumbnail' ),
				'capability_type' => 'product',
				'map_meta_cap'    => true,
				'show_in_rest'    => false,
			)
		);

		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Categorías de estampados', 'camisama-customizer' ),
					'singular_name' => __( 'Categoría', 'camisama-customizer' ),
					'menu_name'     => __( 'Categorías', 'camisama-customizer' ),
					'add_new_item'  => __( 'Añadir categoría', 'camisama-customizer' ),
					'search_items'  => __( 'Buscar categorías', 'camisama-customizer' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'show_in_rest'      => false,
				'capabilities'      => array(
					'manage_terms' => 'manage_product_terms',
					'edit_terms'   => 'edit_product_terms',
					'delete_terms' => 'delete_product_terms',
					'assign_terms' => 'assign_product_terms',
				),
			)
		);
	}

	/**
	 * Caja "Precio" en la pantalla de edición.
	 */
	public function add_meta_box(): void {
		add_meta_box( 'camisama-design-premium', __( 'Precio', 'camisama-customizer' ), array( $this, 'render_meta_box' ), self::POST_TYPE, 'side' );
	}

	/**
	 * Campo del recargo premium.
	 *
	 * @param \WP_Post $post Estampado.
	 */
	public function render_meta_box( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE, self::NONCE );
		$value = Money::to_decimal( self::premium( $post->ID ) );
		?>
		<p>
			<label for="camisama-premium"><?php esc_html_e( 'Recargo premium (S/)', 'camisama-customizer' ); ?></label>
			<input type="text" inputmode="decimal" id="camisama-premium" name="camisama_premium" value="<?php echo esc_attr( $value ); ?>" class="widefat" />
		</p>
		<p class="description"><?php esc_html_e( 'Se suma al precio cuando el cliente usa este estampado, por ejemplo para pagar la comisión de un artista. Déjalo en 0 si no aplica.', 'camisama-customizer' ); ?></p>
		<?php
	}

	/**
	 * Guarda el recargo premium.
	 *
	 * @param int $post_id ID del estampado.
	 */
	public function save_premium( int $post_id ): void {
		if ( ! isset( $_POST[ self::NONCE ], $_POST['camisama_premium'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$cents = Money::to_cents( sanitize_text_field( wp_unslash( $_POST['camisama_premium'] ) ) );
		update_post_meta( $post_id, self::META_PREMIUM, $cents ?? 0 );
	}

	/**
	 * Columnas del listado.
	 *
	 * @param array<string, string> $columns Columnas por defecto.
	 * @return array<string, string>
	 */
	public function columns( array $columns ): array {
		$result = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$result['camisama_file'] = __( 'Archivo', 'camisama-customizer' );
			}
			$result[ $key ] = $label;
		}
		$result['camisama_premium'] = __( 'Premium', 'camisama-customizer' );

		return $result;
	}

	/**
	 * Contenido de las columnas propias.
	 *
	 * @param string $column  Columna.
	 * @param int    $post_id ID del estampado.
	 */
	public function render_column( string $column, int $post_id ): void {
		if ( 'camisama_file' === $column ) {
			$url = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
			if ( $url ) {
				printf( '<img src="%s" alt="" style="width:48px;height:48px;object-fit:contain;background:#f0f0f1;border-radius:4px" />', esc_url( $url ) );
			} else {
				echo '<span style="color:#b32d2e">' . esc_html__( 'Sin archivo', 'camisama-customizer' ) . '</span>';
			}
		}

		if ( 'camisama_premium' === $column ) {
			$premium = self::premium( $post_id );
			echo $premium > 0 ? wp_kses_post( wc_price( $premium / 100 ) ) : '—';
		}
	}

	/**
	 * Recargo premium en céntimos.
	 *
	 * @param int $post_id ID del estampado.
	 */
	public static function premium( int $post_id ): int {
		return max( 0, (int) get_post_meta( $post_id, self::META_PREMIUM, true ) );
	}
}

<?php
/**
 * Color de muestra de los términos del atributo Color.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Products;

/**
 * Campo "Color de muestra" en Productos → Atributos → Color → términos, para pintar las muestras
 * del editor de la tienda.
 */
final class ColorSwatchField {

	public const META = 'camisama_hex';

	private const NONCE = 'camisama_swatch_nonce';

	/**
	 * Engancha los formularios de alta y edición del término.
	 */
	public function register(): void {
		$taxonomy = ProductColors::TAXONOMY;
		add_action( "{$taxonomy}_add_form_fields", array( $this, 'render_add' ) );
		add_action( "{$taxonomy}_edit_form_fields", array( $this, 'render_edit' ) );
		add_action( "created_{$taxonomy}", array( $this, 'save' ) );
		add_action( "edited_{$taxonomy}", array( $this, 'save' ) );
	}

	/**
	 * Campo en el formulario de alta.
	 */
	public function render_add(): void {
		wp_nonce_field( self::NONCE, self::NONCE );
		?>
		<div class="form-field">
			<label for="camisama-hex"><?php esc_html_e( 'Color de muestra', 'camisama-customizer' ); ?></label>
			<input type="color" id="camisama-hex" name="camisama_hex" value="#ffffff" />
			<p><?php esc_html_e( 'Se usa en las muestras de color del personalizador.', 'camisama-customizer' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Campo en el formulario de edición.
	 *
	 * @param \WP_Term $term Término.
	 */
	public function render_edit( \WP_Term $term ): void {
		$hex = self::hex( $term->term_id ) ?? '#ffffff';
		?>
		<tr class="form-field">
			<th scope="row"><label for="camisama-hex"><?php esc_html_e( 'Color de muestra', 'camisama-customizer' ); ?></label></th>
			<td>
				<?php wp_nonce_field( self::NONCE, self::NONCE ); ?>
				<input type="color" id="camisama-hex" name="camisama_hex" value="<?php echo esc_attr( $hex ); ?>" />
				<p class="description"><?php esc_html_e( 'Se usa en las muestras de color del personalizador.', 'camisama-customizer' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Guarda el color.
	 *
	 * @param int $term_id ID del término.
	 */
	public function save( int $term_id ): void {
		if ( ! isset( $_POST[ self::NONCE ], $_POST['camisama_hex'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_product_terms' ) ) {
			return;
		}

		$hex = sanitize_hex_color( sanitize_text_field( wp_unslash( $_POST['camisama_hex'] ) ) );
		if ( $hex ) {
			update_term_meta( $term_id, self::META, $hex );
		}
	}

	/**
	 * Color guardado del término, o null.
	 *
	 * @param int $term_id ID del término.
	 */
	public static function hex( int $term_id ): ?string {
		$hex = sanitize_hex_color( (string) get_term_meta( $term_id, self::META, true ) );

		return $hex ? $hex : null;
	}
}

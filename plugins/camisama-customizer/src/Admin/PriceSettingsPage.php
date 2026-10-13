<?php
/**
 * Ajustes de precios del personalizador.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Admin;

use Camisama\Customizer\Pricing\Money;
use Camisama\Customizer\Pricing\PriceTable;
use Camisama\Customizer\Pricing\PriceTableRepository;
use Camisama\Customizer\Pricing\PriceTier;

/**
 * WooCommerce → Personalizador: tabla de recargos por tamaño de estampado.
 */
final class PriceSettingsPage {

	private const SLUG       = 'camisama-customizer';
	private const GROUP      = 'camisama_customizer';
	private const EMPTY_ROWS = 2;

	/**
	 * Constructor.
	 *
	 * @param PriceTableRepository $prices Tabla de precios.
	 */
	public function __construct( private readonly PriceTableRepository $prices ) {}

	/**
	 * Engancha el menú y el registro del ajuste.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ), 60 );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
	}

	/**
	 * Submenú dentro de WooCommerce.
	 */
	public function add_page(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Personalizador', 'camisama-customizer' ),
			__( 'Personalizador', 'camisama-customizer' ),
			'manage_woocommerce',
			self::SLUG,
			array( $this, 'render' )
		);
	}

	/**
	 * Registra la opción con su validación.
	 */
	public function register_setting(): void {
		register_setting(
			self::GROUP,
			PriceTableRepository::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'show_in_rest'      => false,
			)
		);
	}

	/**
	 * Convierte las filas del formulario en una tabla válida; si hay errores, conserva la anterior.
	 *
	 * @param mixed $input Filas enviadas (label, max_cm, amount en soles).
	 * @return list<array{label: string, max_cm: float, amount: int}>
	 */
	public function sanitize( mixed $input ): array {
		$previous = $this->prices->get()->to_array();
		$tiers    = array();

		// Si la opción no existía, WordPress vuelve a sanear el valor ya convertido (importes en céntimos, int).
		if ( is_array( $input ) && array() !== $input && array_is_list( $input ) && is_array( $input[0] ) && is_int( $input[0]['amount'] ?? null ) ) {
			try {
				return PriceTable::from_array( $input )->to_array();
			} catch ( \InvalidArgumentException ) {
				return $previous;
			}
		}

		foreach ( is_array( $input ) ? $input : array() as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$label  = sanitize_text_field( (string) ( $row['label'] ?? '' ) );
			$max_cm = (string) ( $row['max_cm'] ?? '' );
			$amount = Money::to_cents( (string) ( $row['amount'] ?? '' ) );

			if ( '' === $label && '' === trim( $max_cm ) ) {
				continue;
			}

			if ( ! is_numeric( str_replace( ',', '.', $max_cm ) ) || null === $amount ) {
				/* translators: %s: nombre del escalón. */
				$this->error( sprintf( __( 'Revisa el tamaño y el precio del escalón "%s".', 'camisama-customizer' ), $label ) );
				return $previous;
			}

			try {
				$tiers[] = new PriceTier( $label, (float) str_replace( ',', '.', $max_cm ), $amount );
			} catch ( \InvalidArgumentException $e ) {
				$this->error( $e->getMessage() );
				return $previous;
			}
		}

		try {
			return ( new PriceTable( $tiers ) )->to_array();
		} catch ( \InvalidArgumentException $e ) {
			$this->error( $e->getMessage() );
			return $previous;
		}
	}

	/**
	 * Pinta la página.
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$rows = $this->prices->get()->to_array();
		for ( $i = 0; $i < self::EMPTY_ROWS; $i++ ) {
			$rows[] = null;
		}
		$name = PriceTableRepository::OPTION;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Personalizador: precios por tamaño', 'camisama-customizer' ); ?></h1>
			<p><?php esc_html_e( 'Cada zona impresa suma un recargo según el lado mayor del diseño que lleva (estampados, texto e imágenes juntos). El precio de la prenda se define en cada producto.', 'camisama-customizer' ); ?></p>

			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php settings_fields( self::GROUP ); ?>
				<table class="widefat striped" style="max-width:720px">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Nombre', 'camisama-customizer' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Lado mayor hasta (cm)', 'camisama-customizer' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Recargo por zona (S/)', 'camisama-customizer' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $index => $row ) : ?>
							<tr>
								<td><input type="text" class="regular-text" name="<?php echo esc_attr( "{$name}[{$index}][label]" ); ?>" value="<?php echo esc_attr( $row['label'] ?? '' ); ?>" aria-label="<?php esc_attr_e( 'Nombre', 'camisama-customizer' ); ?>" /></td>
								<td><input type="text" inputmode="decimal" class="small-text" name="<?php echo esc_attr( "{$name}[{$index}][max_cm]" ); ?>" value="<?php echo esc_attr( null === $row ? '' : (string) $row['max_cm'] ); ?>" aria-label="<?php esc_attr_e( 'Lado mayor hasta (cm)', 'camisama-customizer' ); ?>" /></td>
								<td><input type="text" inputmode="decimal" class="small-text" name="<?php echo esc_attr( "{$name}[{$index}][amount]" ); ?>" value="<?php echo esc_attr( null === $row ? '' : Money::to_decimal( $row['amount'] ) ); ?>" aria-label="<?php esc_attr_e( 'Recargo por zona (S/)', 'camisama-customizer' ); ?>" /></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p class="description"><?php esc_html_e( 'Para añadir un escalón, rellena una fila vacía. Para quitarlo, vacía su nombre y su tamaño. Un diseño más grande que el último escalón no se puede pedir.', 'camisama-customizer' ); ?></p>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Muestra un error tras guardar.
	 *
	 * @param string $message Mensaje.
	 */
	private function error( string $message ): void {
		add_settings_error( PriceTableRepository::OPTION, 'invalid', $message );
	}
}

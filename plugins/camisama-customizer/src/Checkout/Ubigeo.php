<?php
/**
 * Departamentos, provincias y distritos del Perú (data/ubigeo.json).
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Checkout;

/**
 * Consulta de distritos por su código de ubigeo del INEI: dos dígitos de departamento, dos de
 * provincia y dos de distrito. Se carga solo cuando hace falta, en las peticiones del pago.
 */
final class Ubigeo {

	/**
	 * Provincias donde se hacen envíos: Lima Metropolitana y el Callao.
	 */
	private const DELIVERY_PROVINCES = array( '1501', '0701' );

	/**
	 * Región de WooCommerce por departamento. Lima se reparte en dos: la provincia de Lima (LMA,
	 * Lima Metropolitana) y el resto del departamento (LIM).
	 */
	private const STATES = array(
		'01' => 'AMA',
		'02' => 'ANC',
		'03' => 'APU',
		'04' => 'ARE',
		'05' => 'AYA',
		'06' => 'CAJ',
		'07' => 'CAL',
		'08' => 'CUS',
		'09' => 'HUV',
		'10' => 'HUC',
		'11' => 'ICA',
		'12' => 'JUN',
		'13' => 'LAL',
		'14' => 'LAM',
		'15' => 'LIM',
		'16' => 'LOR',
		'17' => 'MDD',
		'18' => 'MOQ',
		'19' => 'PAS',
		'20' => 'PIU',
		'21' => 'PUN',
		'22' => 'SAM',
		'23' => 'TAC',
		'24' => 'TUM',
		'25' => 'UCA',
	);

	/**
	 * Distritos ya leídos: ubigeo => nombre.
	 *
	 * @var array<string, string>|null
	 */
	private static ?array $districts = null;

	/**
	 * Ruta del archivo de datos.
	 */
	public static function file(): string {
		return dirname( __DIR__, 2 ) . '/data/ubigeo.json';
	}

	/**
	 * Nombre del distrito, o null si el código no es de un distrito.
	 *
	 * @param string $code Ubigeo.
	 */
	public static function name( string $code ): ?string {
		return self::districts()[ $code ] ?? null;
	}

	/**
	 * Región de WooCommerce del distrito.
	 *
	 * @param string $code Ubigeo.
	 */
	public static function state( string $code ): string {
		return str_starts_with( $code, '1501' ) ? 'LMA' : ( self::STATES[ substr( $code, 0, 2 ) ] ?? '' );
	}

	/**
	 * ¿Se hacen envíos a este distrito?
	 *
	 * @param string $code Ubigeo.
	 */
	public static function delivers( string $code ): bool {
		return null !== self::name( $code ) && in_array( substr( $code, 0, 4 ), self::DELIVERY_PROVINCES, true );
	}

	/**
	 * ¿Se hacen envíos a esta región de WooCommerce? (LIM es el resto del departamento de Lima.)
	 *
	 * @param string $state Región.
	 */
	public static function state_delivers( string $state ): bool {
		return in_array( $state, array( 'LMA', 'CAL' ), true );
	}

	/**
	 * Distrito con envío a partir de la ciudad y la región de un destino.
	 *
	 * @param string $city  Nombre del distrito.
	 * @param string $state Región de WooCommerce.
	 * @return string|null Ubigeo, o null si no es un distrito con envío.
	 */
	public static function delivery_code( string $city, string $state ): ?string {
		foreach ( self::districts() as $code => $name ) {
			$code = (string) $code;
			if ( $name === $city && self::state( $code ) === $state && self::delivers( $code ) ) {
				return $code;
			}
		}

		return null;
	}

	/**
	 * Lee los distritos del archivo de datos.
	 *
	 * @return array<string, string>
	 */
	private static function districts(): array {
		if ( null !== self::$districts ) {
			return self::$districts;
		}

		self::$districts = array();
		$tree            = json_decode( (string) file_get_contents( self::file() ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Archivo local del plugin.
		foreach ( is_array( $tree ) ? $tree : array() as $department ) {
			foreach ( $department[2] as $province ) {
				foreach ( $province[2] as $district ) {
					self::$districts[ (string) $district[0] ] = (string) $district[1];
				}
			}
		}

		return self::$districts;
	}
}

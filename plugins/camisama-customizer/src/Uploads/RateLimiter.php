<?php
/**
 * Límite de frecuencia por visitante.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Uploads;

/**
 * Ventana fija guardada en transients: en la base de datos (o en la caché de objetos si existe),
 * así que el límite se comparte entre todos los procesos del servidor.
 */
final class RateLimiter {

	/**
	 * Constructor.
	 *
	 * @param string $scope   Prefijo del contador.
	 * @param int    $max     Intentos permitidos por ventana.
	 * @param int    $seconds Duración de la ventana.
	 */
	public function __construct(
		private readonly string $scope,
		private readonly int $max,
		private readonly int $seconds,
	) {}

	/**
	 * Registra un intento y dice si está dentro del límite.
	 *
	 * @param string $who Identificador del visitante (se guarda solo su hash).
	 */
	public function hit( string $who ): bool {
		$key   = 'camisama_rl_' . $this->scope . '_' . substr( hash( 'sha256', wp_salt( 'nonce' ) . $who ), 0, 32 );
		$state = get_transient( $key );
		$now   = time();

		if ( ! is_array( $state ) || ( $state['until'] ?? 0 ) <= $now ) {
			$state = array(
				'count' => 0,
				'until' => $now + $this->seconds,
			);
		}

		++$state['count'];
		set_transient( $key, $state, max( 1, $state['until'] - $now ) );

		return $state['count'] <= $this->max;
	}

	/**
	 * Identificador del visitante: el usuario si ha iniciado sesión y, si no, su IP.
	 * No se usa X-Forwarded-For: lo puede falsificar cualquiera.
	 */
	public static function visitor(): string {
		$user = get_current_user_id();
		if ( $user > 0 ) {
			return "user:{$user}";
		}

		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		return 'ip:' . $ip;
	}
}

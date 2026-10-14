<?php
/**
 * Fuentes disponibles para el texto de los clientes.
 *
 * @package Camisama\Customizer
 */

declare( strict_types=1 );

namespace Camisama\Customizer\Design;

/**
 * Todas con licencia que permite el uso comercial y el estampado (OFL o Apache 2.0); los archivos
 * y sus licencias están en assets/fonts/. Subconjunto latino: incluye tildes, ñ, ¿ y ¡.
 */
final class Fonts {

	/** Slug => [nombre, archivo, peso con el que se usa]. */
	private const FONTS = array(
		'inter'               => array( 'Inter', 'inter/inter-latin-wght-normal.woff2', 800 ),
		'bricolage-grotesque' => array( 'Bricolage Grotesque', 'bricolage-grotesque/bricolage-grotesque-latin-wght-normal.woff2', 800 ),
		'bebas-neue'          => array( 'Bebas Neue', 'bebas-neue/bebas-neue-latin-400-normal.woff2', 400 ),
		'anton'               => array( 'Anton', 'anton/anton-latin-400-normal.woff2', 400 ),
		'pacifico'            => array( 'Pacifico', 'pacifico/pacifico-latin-400-normal.woff2', 400 ),
		'permanent-marker'    => array( 'Permanent Marker', 'permanent-marker/permanent-marker-latin-400-normal.woff2', 400 ),
		'playfair-display'    => array( 'Playfair Display', 'playfair-display/playfair-display-latin-wght-normal.woff2', 700 ),
		'space-mono'          => array( 'Space Mono', 'space-mono/space-mono-latin-700-normal.woff2', 700 ),
	);

	/**
	 * Si el slug es una fuente permitida.
	 *
	 * @param string $slug Slug de la fuente.
	 */
	public static function exists( string $slug ): bool {
		return isset( self::FONTS[ $slug ] );
	}

	/**
	 * Fuentes para el editor. La familia lleva prefijo para no chocar con las fuentes del tema.
	 *
	 * @return list<array{slug: string, name: string, family: string, url: string, weight: int}>
	 */
	public static function for_client(): array {
		$plugin_file = dirname( __DIR__, 2 ) . '/camisama-customizer.php';
		$fonts       = array();

		foreach ( self::FONTS as $slug => list( $name, $file, $weight ) ) {
			$fonts[] = array(
				'slug'   => $slug,
				'name'   => $name,
				'family' => "Camisama {$name}",
				'url'    => plugins_url( "assets/fonts/{$file}", $plugin_file ),
				'weight' => $weight,
			);
		}

		return $fonts;
	}
}

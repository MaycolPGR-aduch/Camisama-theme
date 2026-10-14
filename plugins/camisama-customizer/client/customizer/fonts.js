/**
 * Carga bajo demanda de las fuentes del texto (assets/fonts del plugin).
 */

const loading = new Map();

/**
 * Carga una fuente y la registra en el documento; se puede llamar varias veces.
 *
 * @param {{slug: string, family: string, url: string, weight: number}} font Fuente.
 * @return {Promise<void>} Se resuelve cuando la fuente está lista para dibujar.
 */
export function loadFont( font ) {
	if ( ! loading.has( font.slug ) ) {
		const face = new window.FontFace( font.family, `url("${ font.url }")`, {
			weight: String( font.weight ),
			display: 'swap',
		} );
		loading.set(
			font.slug,
			face.load().then( ( loaded ) => {
				document.fonts.add( loaded );
			} )
		);
	}

	return loading.get( font.slug );
}

( function () {
	/**
	 * Bloque dinámico de la portada con vista previa del servidor en el editor.
	 *
	 * @param {string} name  Nombre del bloque.
	 * @param {string} title Título en el insertador.
	 * @param {string} icon  Dashicon.
	 */
	function register( name, title, icon ) {
		wp.blocks.registerBlockType( name, {
			apiVersion: 3,
			title,
			category: 'widgets',
			icon,
			edit() {
				return wp.element.createElement(
					'div',
					wp.blockEditor.useBlockProps(),
					wp.element.createElement(
						wp.serverSideRender.default || wp.serverSideRender,
						{ block: name }
					)
				);
			},
			save() {
				return null;
			},
		} );
	}

	register(
		'camisama/home-price',
		wp.i18n.__( 'Zonas y precio actual', 'camisama' ),
		'tag'
	);
	register(
		'camisama/home-designs',
		wp.i18n.__( 'Estampados del catálogo', 'camisama' ),
		'art'
	);
} )();

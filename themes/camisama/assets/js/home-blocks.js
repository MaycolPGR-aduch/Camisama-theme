( function () {
	wp.blocks.registerBlockType( 'camisama/home-price', {
		apiVersion: 3,
		title: wp.i18n.__( 'Zonas y precio actual', 'camisama' ),
		category: 'widgets',
		icon: 'tag',
		edit() {
			return wp.element.createElement(
				'div',
				wp.blockEditor.useBlockProps(),
				wp.element.createElement(
					wp.serverSideRender.default || wp.serverSideRender,
					{ block: 'camisama/home-price' }
				)
			);
		},
		save() {
			return null;
		},
	} );
} )();

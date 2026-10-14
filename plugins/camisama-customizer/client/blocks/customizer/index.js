/**
 * Registro del bloque en el editor.
 *
 * El bloque envuelve a la galería del producto: en los productos personalizables la reemplaza
 * por el editor, y en los demás se muestra la galería tal cual.
 */
import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import './style.scss';

const TEMPLATE = [ [ 'woocommerce/product-image-gallery' ] ];

function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Notice status="info" isDismissible={ false }>
				{ __(
					'Personalizador de prendas: en los productos personalizables reemplaza a lo que hay dentro (la galería).',
					'camisama-customizer'
				) }
			</Notice>
			<InnerBlocks template={ TEMPLATE } />
		</div>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => <InnerBlocks.Content />,
} );

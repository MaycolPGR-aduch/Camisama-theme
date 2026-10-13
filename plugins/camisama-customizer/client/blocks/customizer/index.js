/**
 * Registro del bloque en el editor.
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';
import './style.scss';

function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon="art"
				label={ __(
					'Personalizador de prendas',
					'camisama-customizer'
				) }
				instructions={ __(
					'El editor interactivo se muestra en la ficha del producto, en la tienda.',
					'camisama-customizer'
				) }
			/>
		</div>
	);
}

registerBlockType( metadata.name, {
	edit: Edit,
} );

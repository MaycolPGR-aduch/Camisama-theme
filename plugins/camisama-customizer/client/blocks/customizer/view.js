/**
 * Aplicación del personalizador en la tienda.
 *
 * Por ahora solo monta un marcador de posición; el editor con Fabric.js llega en la fase 3.
 */
import { createRoot } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

function CustomizerApp( { productId } ) {
	return (
		<p className="wp-block-camisama-customizer__status">
			{ sprintf(
				/* translators: %d: ID del producto. */
				__(
					'Personalizador en construcción (producto #%d).',
					'camisama-customizer'
				),
				productId
			) }
		</p>
	);
}

document
	.querySelectorAll( '.wp-block-camisama-customizer' )
	.forEach( ( node ) => {
		createRoot( node ).render(
			<CustomizerApp productId={ Number( node.dataset.productId ) } />
		);
	} );

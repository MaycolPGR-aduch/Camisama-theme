/**
 * Paso 1 del flujo móvil (docs/flujo-movil.md), junto al título del producto: el precio de
 * partida y la entrada al editor. El formulario de compra de WooCommerce queda reservado para
 * el paso de talla y pago, que se abre con la acción secundaria o desde el editor.
 */
import { __ } from '@wordpress/i18n';

export default function FlowEntry( {
	price,
	hasDesign,
	buying,
	onDesign,
	onBuy,
	openRef,
} ) {
	if ( buying ) {
		return (
			<button
				type="button"
				className="camisama-customizer__secondary"
				ref={ openRef }
				onClick={ onDesign }
			>
				{ hasDesign
					? __( 'Editar diseño', 'camisama-customizer' )
					: __( 'Personalizar', 'camisama-customizer' ) }
			</button>
		);
	}

	return (
		<>
			<p className="camisama-customizer__from">
				{ hasDesign ? (
					<>
						{ __( 'Tu diseño:', 'camisama-customizer' ) }{ ' ' }
						<strong>{ price }</strong>
					</>
				) : (
					<>
						{ __( 'Desde', 'camisama-customizer' ) }{ ' ' }
						<strong>{ price }</strong>{ ' ' }
						{ __(
							'+ impresión según el tamaño',
							'camisama-customizer'
						) }
					</>
				) }
			</p>
			<button
				type="button"
				className="camisama-customizer__cta"
				ref={ openRef }
				onClick={ onDesign }
			>
				{ hasDesign
					? __( 'Seguir diseñando', 'camisama-customizer' )
					: __( 'Personalizar', 'camisama-customizer' ) }
			</button>
			<button
				type="button"
				className="camisama-customizer__secondary"
				onClick={ onBuy }
			>
				{ hasDesign
					? __( 'Ir a talla y pago', 'camisama-customizer' )
					: __( 'Comprar sin diseño', 'camisama-customizer' ) }
			</button>
		</>
	);
}

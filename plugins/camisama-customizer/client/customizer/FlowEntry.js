/**
 * Paso 1 del flujo móvil (docs/flujo-movil.md), junto al título del producto: el precio de
 * partida y la entrada al editor o, con la acción secundaria, al paso de talla y pago.
 */
import { __ } from '@wordpress/i18n';

export default function FlowEntry( {
	price,
	hasDesign,
	onDesign,
	onBuy,
	openRef,
} ) {
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

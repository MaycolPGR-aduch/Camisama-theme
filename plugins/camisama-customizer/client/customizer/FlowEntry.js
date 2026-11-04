/**
 * Paso 1 del flujo móvil (docs/flujo-movil.md), junto al título del producto: el precio de
 * partida, unos estampados para empezar con un toque y la entrada al editor o, con la acción
 * secundaria, al paso de talla y cantidad.
 */
import { __ } from '@wordpress/i18n';

export default function FlowEntry( {
	price,
	hasDesign,
	starters,
	onStart,
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
			{ ! hasDesign && starters.length > 0 && (
				<div className="camisama-customizer__starters">
					<p id="camisama-starters-label">
						{ __( 'O empieza con uno:', 'camisama-customizer' ) }
					</p>
					<ul
						className="camisama-customizer__designs"
						aria-labelledby="camisama-starters-label"
					>
						{ starters.map( ( design ) => (
							<li key={ design.id }>
								<button
									type="button"
									onClick={ () => onStart( design ) }
								>
									<img
										src={ design.file.url }
										alt=""
										width={ design.file.width }
										height={ design.file.height }
									/>
									{ design.title }
								</button>
							</li>
						) ) }
					</ul>
				</div>
			) }
			<button
				type="button"
				className={
					hasDesign
						? 'camisama-customizer__secondary'
						: 'camisama-customizer__link'
				}
				onClick={ onBuy }
			>
				{ hasDesign
					? __( 'Elegir talla', 'camisama-customizer' )
					: __( 'Comprar sin diseño', 'camisama-customizer' ) }
			</button>
		</>
	);
}

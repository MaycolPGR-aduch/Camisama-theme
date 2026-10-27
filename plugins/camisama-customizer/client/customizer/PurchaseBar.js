/**
 * Pie del modo diseño en el móvil (docs/flujo-movil.md): el total a la vista mientras se diseña
 * y el paso siguiente, la talla y el pago. Solo existe en pantallas estrechas (style.scss).
 */
import { __ } from '@wordpress/i18n';

export default function PurchaseBar( {
	visible,
	total,
	inCart,
	cartUrl,
	onNext,
} ) {
	return (
		<div
			className="camisama-customizer__bar"
			hidden={ ! visible }
			role="region"
			aria-label={ __(
				'Precio y siguiente paso',
				'camisama-customizer'
			) }
		>
			<p className="camisama-customizer__bar-total">
				<span>
					{ inCart
						? __( 'En tu carrito', 'camisama-customizer' )
						: __( 'Total', 'camisama-customizer' ) }
				</span>
				<strong>{ total }</strong>
			</p>
			{ /* Ya añadido: el siguiente paso es el carrito, no añadirlo otra vez. */ }
			{ inCart ? (
				<a className="camisama-customizer__bar-button" href={ cartUrl }>
					{ __( 'Ver carrito', 'camisama-customizer' ) }
				</a>
			) : (
				<button
					type="button"
					className="camisama-customizer__bar-button"
					onClick={ onNext }
				>
					{ __( 'Siguiente: talla', 'camisama-customizer' ) }
				</button>
			) }
		</div>
	);
}

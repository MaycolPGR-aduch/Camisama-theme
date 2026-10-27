/**
 * Barra de compra del móvil: total y "Añadir al carrito" siempre a mano mientras se diseña.
 *
 * El formulario de compra queda debajo del editor; la barra lo sustituye hasta que aparece en
 * pantalla y vuelve a esconderse en cuanto se ve, para no duplicarlo. Solo se muestra en
 * pantallas estrechas (style.scss).
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function PurchaseBar( {
	target,
	total,
	options,
	invalid,
	busy,
	inCart,
	cartUrl,
	onBuy,
	onReview,
} ) {
	// Visible mientras el formulario de compra está por debajo de la pantalla.
	const [ visible, setVisible ] = useState( false );

	useEffect( () => {
		const observer = new window.IntersectionObserver( ( [ entry ] ) =>
			setVisible(
				! entry.isIntersecting && entry.boundingClientRect.top > 0
			)
		);
		observer.observe( target );
		return () => observer.disconnect();
	}, [ target ] );

	let action = __( 'Añadir al carrito', 'camisama-customizer' );
	if ( busy ) {
		action = __( 'Preparando tu diseño…', 'camisama-customizer' );
	} else if ( invalid ) {
		action = __( 'Revisar diseño', 'camisama-customizer' );
	} else if ( ! options ) {
		action = __( 'Elegir talla', 'camisama-customizer' );
	}

	return (
		<div
			className="camisama-customizer__bar"
			hidden={ ! visible }
			role="region"
			aria-label={ __( 'Compra', 'camisama-customizer' ) }
		>
			<p className="camisama-customizer__bar-total">
				<span>
					{ inCart
						? __( 'En tu carrito', 'camisama-customizer' )
						: ( options ?? __( 'Total', 'camisama-customizer' ) ) }
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
					disabled={ busy }
					onClick={ invalid ? onReview : onBuy }
				>
					{ action }
				</button>
			) }
		</div>
	);
}

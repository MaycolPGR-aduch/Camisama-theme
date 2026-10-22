/**
 * Resumen del precio: prenda + recargo de cada zona impresa + estampados premium.
 */
import { __, sprintf } from '@wordpress/i18n';

import { formatMoney } from './format';

export default function Summary( {
	garmentPrice,
	priceIsFrom,
	quote,
	error,
	currency,
} ) {
	const total = garmentPrice + ( quote?.total ?? 0 );

	return (
		<section
			className="camisama-customizer__summary"
			aria-label={ __( 'Precio', 'camisama-customizer' ) }
		>
			<dl>
				<div>
					<dt>{ __( 'Prenda', 'camisama-customizer' ) }</dt>
					<dd>
						{ priceIsFrom &&
							__( 'desde', 'camisama-customizer' ) + ' ' }
						{ formatMoney( garmentPrice, currency ) }
					</dd>
				</div>
				{ quote?.zones.map( ( zone ) => (
					<div key={ zone.zone }>
						<dt>
							{ sprintf(
								/* translators: 1: zona, 2: escalón de tamaño. */
								__( '%1$s · %2$s', 'camisama-customizer' ),
								zone.label,
								zone.tier
							) }
						</dt>
						<dd>+ { formatMoney( zone.amount, currency ) }</dd>
					</div>
				) ) }
				{ quote?.premiums.length > 0 && (
					<div>
						<dt>
							{ __(
								'Estampados premium',
								'camisama-customizer'
							) }
						</dt>
						<dd>
							+{ ' ' }
							{ formatMoney(
								quote.premiums.reduce(
									( sum, item ) => sum + item.amount,
									0
								),
								currency
							) }
						</dd>
					</div>
				) }
			</dl>
			{ /* Solo se anuncia el total (o el error), no el desglose entero en cada cambio. */ }
			<div aria-live="polite">
				{ error ? (
					<p className="camisama-customizer__error">{ error }</p>
				) : (
					<p className="camisama-customizer__total">
						<span>{ __( 'Total', 'camisama-customizer' ) }</span>
						<strong>
							{ priceIsFrom &&
								__( 'desde', 'camisama-customizer' ) + ' ' }
							{ formatMoney( total, currency ) }
						</strong>
					</p>
				) }
			</div>
		</section>
	);
}

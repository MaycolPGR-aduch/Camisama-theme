/**
 * Resumen del precio: prenda + recargo de cada zona impresa (con el límite de su escalón) +
 * estampados premium, y el total por la cantidad elegida.
 */
import { __, sprintf } from '@wordpress/i18n';

import { formatMoney } from './format';

export default function Summary( {
	garmentPrice,
	priceIsFrom,
	quote,
	quantity = 1,
	tiers = [],
	error,
	currency,
} ) {
	const unit = garmentPrice + ( quote?.total ?? 0 );
	const from = priceIsFrom ? __( 'desde', 'camisama-customizer' ) + ' ' : '';
	const tierLimit = ( label ) =>
		tiers.find( ( tier ) => tier.label === label )?.max_cm;

	return (
		<section
			className="camisama-customizer__summary"
			aria-label={ __( 'Precio', 'camisama-customizer' ) }
		>
			<dl>
				<div>
					<dt>{ __( 'Prenda', 'camisama-customizer' ) }</dt>
					<dd>
						{ from }
						{ formatMoney( garmentPrice, currency ) }
					</dd>
				</div>
				{ quote?.zones.map( ( zone ) => (
					<div key={ zone.zone }>
						<dt>
							{ tierLimit( zone.tier )
								? sprintf(
										/* translators: 1: zona, 2: escalón de tamaño, 3: su lado mayor en cm. */
										__(
											'%1$s · %2$s (hasta %3$s cm)',
											'camisama-customizer'
										),
										zone.label,
										zone.tier,
										String( tierLimit( zone.tier ) )
									)
								: sprintf(
										/* translators: 1: zona, 2: escalón de tamaño. */
										__(
											'%1$s · %2$s',
											'camisama-customizer'
										),
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
				{ quantity > 1 && (
					<div className="camisama-customizer__unit">
						<dt>{ __( 'Por unidad', 'camisama-customizer' ) }</dt>
						<dd>
							{ sprintf(
								/* translators: 1: cantidad, 2: precio de una unidad. */
								__( '%1$d × %2$s', 'camisama-customizer' ),
								quantity,
								from + formatMoney( unit, currency )
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
							{ from }
							{ formatMoney( unit * quantity, currency ) }
						</strong>
					</p>
				) }
			</div>
		</section>
	);
}

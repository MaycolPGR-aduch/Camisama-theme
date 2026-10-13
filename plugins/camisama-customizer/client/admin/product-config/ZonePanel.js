/**
 * Propiedades de la zona seleccionada: nombre, tamaño real y escalones de precio que alcanza.
 */
import { Button, TextControl } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

import { tiersWithin } from './utils';

function formatAmount( cents, currency ) {
	return `${ currency } ${ ( cents / 100 ).toFixed( 2 ) }`;
}

export default function ZonePanel( {
	zone,
	priceTiers,
	currency,
	settingsUrl,
	onLabel,
	onSize,
	onRemove,
} ) {
	if ( ! zone ) {
		return (
			<p className="camisama-zone-panel__hint">
				{ __(
					'Selecciona una zona en la imagen para editarla.',
					'camisama-customizer'
				) }
			</p>
		);
	}

	const tiers = tiersWithin(
		priceTiers,
		Math.max( zone.size_cm.w, zone.size_cm.h )
	);

	const sizeField = ( axis, label ) => (
		<TextControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			label={ label }
			type="number"
			min={ 1 }
			max={ 100 }
			step={ 0.5 }
			value={ zone.size_cm[ axis ] }
			onChange={ ( value ) => {
				const number = parseFloat( value );
				if ( number > 0 && number <= 100 ) {
					onSize( { ...zone.size_cm, [ axis ]: number } );
				}
			} }
		/>
	);

	return (
		<div className="camisama-zone-panel">
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Nombre de la zona', 'camisama-customizer' ) }
				value={ zone.label }
				onChange={ onLabel }
			/>

			<div className="camisama-zone-panel__size">
				{ sizeField(
					'w',
					__( 'Ancho máximo (cm)', 'camisama-customizer' )
				) }
				{ sizeField(
					'h',
					__( 'Alto máximo (cm)', 'camisama-customizer' )
				) }
			</div>

			<div className="camisama-zone-panel__tiers">
				<strong>
					{ __( 'Precios posibles', 'camisama-customizer' ) }
				</strong>
				{ tiers.length ? (
					<ul>
						{ tiers.map( ( tier ) => (
							<li key={ tier.label }>
								{ sprintf(
									/* translators: 1: nombre del escalón, 2: tamaño en cm, 3: precio. */
									__(
										'%1$s (hasta %2$s cm): + %3$s',
										'camisama-customizer'
									),
									tier.label,
									tier.max_cm,
									formatAmount( tier.amount, currency )
								) }
							</li>
						) ) }
					</ul>
				) : (
					<p>
						{ __(
							'Ningún escalón de precio cubre esta zona.',
							'camisama-customizer'
						) }
					</p>
				) }
				<a href={ settingsUrl }>
					{ __( 'Editar precios', 'camisama-customizer' ) }
				</a>
			</div>

			<Button variant="secondary" isDestructive onClick={ onRemove }>
				{ __( 'Eliminar zona', 'camisama-customizer' ) }
			</Button>
		</div>
	);
}

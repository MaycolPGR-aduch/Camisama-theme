/**
 * Personalizador de la tienda.
 *
 * El diseño (lista de elementos en cm de cada zona) es la fuente de verdad; el escenario lo
 * dibuja y el resumen lo cotiza con la misma regla que el servidor (pricing.js).
 */
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import Stage from './Stage';
import Summary from './Summary';
import { quote } from './pricing';
import { connectVariationForm } from './wc-bridge';

export default function App( { data } ) {
	const [ color, setColor ] = useState( data.colors[ 0 ].slug );
	const [ viewId, setViewId ] = useState( data.views[ 0 ]?.id );
	const [ activeZoneId, setActiveZoneId ] = useState(
		data.zones.find( ( zone ) => zone.view === data.views[ 0 ]?.id )?.id
	);
	const [ variationPrice, setVariationPrice ] = useState( null );
	const [ elements ] = useState( [] );
	const bridge = useRef( null );

	useEffect( () => {
		bridge.current = connectVariationForm( data.product_id, {
			onColor: setColor,
			onPrice: setVariationPrice,
		} );
		return () => bridge.current.disconnect();
	}, [ data.product_id ] );

	const view = data.views.find( ( item ) => item.id === viewId );
	const viewZones = data.zones.filter( ( zone ) => zone.view === viewId );
	const mockup =
		view?.mockups[ color ] ?? Object.values( view?.mockups ?? {} )[ 0 ];

	const priced = useMemo( () => {
		const premiums = Object.fromEntries(
			elements
				.filter( ( element ) => element.kind === 'design' )
				.map( ( element ) => [ element.design_id, element.premium ] )
		);
		try {
			return {
				quote: quote(
					data.zones,
					data.price_tiers,
					elements,
					premiums
				),
			};
		} catch {
			return {
				error: __(
					'El diseño es más grande que el tamaño máximo a la venta.',
					'camisama-customizer'
				),
			};
		}
	}, [ data.zones, data.price_tiers, elements ] );

	const chooseColor = ( slug ) => {
		setColor( slug );
		bridge.current?.setColor( slug );
	};

	const chooseView = ( id ) => {
		setViewId( id );
		setActiveZoneId(
			data.zones.find( ( zone ) => zone.view === id )?.id ?? null
		);
	};

	return (
		<div className="camisama-customizer">
			<div
				className="camisama-customizer__views"
				role="group"
				aria-label={ __( 'Vista de la prenda', 'camisama-customizer' ) }
			>
				{ data.views.map( ( item ) => (
					<button
						key={ item.id }
						type="button"
						aria-pressed={ item.id === viewId }
						onClick={ () => chooseView( item.id ) }
					>
						{ item.label }
					</button>
				) ) }
			</div>

			<Stage
				mockup={ mockup }
				zones={ viewZones }
				activeZoneId={ activeZoneId }
				onSelectZone={ setActiveZoneId }
			/>

			{ data.colors.length > 1 && (
				<fieldset className="camisama-customizer__colors">
					<legend>{ __( 'Color', 'camisama-customizer' ) }</legend>
					{ data.colors.map( ( item ) => (
						<button
							key={ item.slug }
							type="button"
							className="camisama-customizer__swatch"
							style={ { '--swatch': item.hex ?? '#ffffff' } }
							aria-pressed={ item.slug === color }
							aria-label={ item.name }
							title={ item.name }
							onClick={ () => chooseColor( item.slug ) }
						/>
					) ) }
				</fieldset>
			) }

			<Summary
				garmentPrice={ variationPrice ?? data.base_price }
				priceIsFrom={ variationPrice === null }
				quote={ priced.quote }
				error={ priced.error }
				currency={ data.currency }
			/>
		</div>
	);
}

/**
 * Talla (y demás opciones del producto aparte del color) en botones, sin ninguna marcada, con la
 * guía "¿Qué talla?". La usan el paso de talla y pago del móvil y la columna de compra del
 * escritorio; las elecciones manejan el formulario de WooCommerce a través de wc-bridge.js.
 */
import { useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import { formatMoney } from './format';

export default function SizeOptions( {
	options,
	choices,
	missing,
	guide,
	currency,
	onChoose,
} ) {
	const firstRef = useRef( null );

	// Al pedir comprar sin talla, el foco va a la primera para elegirla.
	useEffect( () => {
		if ( missing ) {
			firstRef.current?.focus();
		}
	}, [ missing ] );

	// Nombre visible de cada talla de la guía ("m" → "M").
	const sizeText = ( slug ) =>
		options[ 0 ]?.values.find( ( value ) => value.value === slug )?.text ??
		slug;

	return (
		<>
			{ options.map( ( option, index ) => (
				<fieldset
					key={ option.name }
					className="camisama-customizer__field camisama-customizer__sizes"
				>
					<legend>{ option.label }</legend>
					<div className="camisama-customizer__chips">
						{ option.values.map( ( value, position ) => (
							<button
								key={ value.value }
								type="button"
								ref={
									index === 0 && position === 0
										? firstRef
										: undefined
								}
								aria-pressed={
									choices[ option.name ] === value.value
								}
								onClick={ () =>
									onChoose( option.name, value.value )
								}
							>
								{ value.text }
								{ value.extra > 0 && (
									<small>
										+{ ' ' }
										{ formatMoney( value.extra, currency ) }
									</small>
								) }
							</button>
						) ) }
					</div>
					{ missing && ! choices[ option.name ] && (
						<p className="camisama-customizer__error" role="alert">
							{ sprintf(
								/* translators: %s: opción que falta, p. ej. talla. */
								__( 'Elige una %s.', 'camisama-customizer' ),
								option.label.toLowerCase()
							) }
						</p>
					) }
				</fieldset>
			) ) }

			{ guide.length > 0 && (
				<details className="camisama-customizer__guide">
					<summary>
						{ __( '¿Qué talla?', 'camisama-customizer' ) }
					</summary>
					<table>
						<thead>
							<tr>
								<th scope="col">
									{ __( 'Talla', 'camisama-customizer' ) }
								</th>
								<th scope="col">
									{ __(
										'Ancho de pecho',
										'camisama-customizer'
									) }
								</th>
								<th scope="col">
									{ __( 'Largo', 'camisama-customizer' ) }
								</th>
							</tr>
						</thead>
						<tbody>
							{ guide.map( ( row ) => (
								<tr key={ row.size }>
									<th scope="row">
										{ sizeText( row.size ) }
									</th>
									<td>{ row.width_cm } cm</td>
									<td>{ row.length_cm } cm</td>
								</tr>
							) ) }
						</tbody>
					</table>
					<p className="camisama-customizer__hint">
						{ __(
							'Medidas de referencia de la prenda extendida, de axila a axila y del hombro al borde. Pueden variar unos 2 cm.',
							'camisama-customizer'
						) }
					</p>
				</details>
			) }
		</>
	);
}

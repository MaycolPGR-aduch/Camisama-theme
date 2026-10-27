/**
 * Paso 3 del flujo móvil (docs/flujo-movil.md): talla y pago, en una pantalla propia.
 *
 * Miniaturas del diseño, desglose del precio, talla en botones sin ninguna marcada, guía de
 * tallas, cantidad y "Añadir al carrito". El pedido lo sigue enviando el formulario de
 * WooCommerce (oculto), que este paso maneja a través de wc-bridge.js.
 */
import { useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

const MAX_QUANTITY = 99;

export default function BuyStep( {
	previews,
	summary,
	options,
	choices,
	missing,
	guide,
	quantity,
	busy,
	error,
	inCart,
	editing,
	hasDesign,
	cartUrl,
	onChoose,
	onQuantity,
	onBuy,
	onEdit,
	onClose,
	onStartOver,
} ) {
	const rootRef = useRef( null );
	const closeRef = useRef( null );
	const firstSizeRef = useRef( null );

	useEffect( () => closeRef.current?.focus(), [] );
	useEffect( () => {
		if ( missing ) {
			firstSizeRef.current?.focus();
		}
	}, [ missing ] );

	// Diálogo: Esc lo cierra y el tabulador no sale de él.
	const onKeyDown = ( event ) => {
		if ( event.key === 'Escape' ) {
			event.stopPropagation();
			onClose();
			return;
		}
		if ( event.key !== 'Tab' ) {
			return;
		}
		event.stopPropagation();
		const focusable = [
			...rootRef.current.querySelectorAll(
				'button, [href], input, summary, [tabindex]:not([tabindex="-1"])'
			),
		].filter( ( node ) => ! node.disabled && node.offsetParent );
		const first = focusable[ 0 ];
		const last = focusable[ focusable.length - 1 ];
		if ( event.shiftKey && event.target === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && event.target === last ) {
			event.preventDefault();
			first.focus();
		}
	};

	// Nombre visible de cada talla de la guía ("m" → "M").
	const sizeOption = options[ 0 ];
	const sizeText = ( slug ) =>
		sizeOption?.values.find( ( value ) => value.value === slug )?.text ??
		slug;

	let action = __( 'Añadir al carrito', 'camisama-customizer' );
	if ( busy ) {
		action = __( 'Preparando tu diseño…', 'camisama-customizer' );
	} else if ( editing ) {
		action = __( 'Guardar cambios', 'camisama-customizer' );
	}

	return (
		// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions -- Esc y el tabulador del diálogo; cada control sigue siendo accesible por sí mismo.
		<div
			ref={ rootRef }
			className="camisama-customizer camisama-customizer__buy"
			role="dialog"
			aria-modal="true"
			aria-labelledby="camisama-buy-title"
			onKeyDown={ onKeyDown }
		>
			<div className="camisama-customizer__buy-head">
				<button
					type="button"
					className="camisama-customizer__close"
					ref={ closeRef }
					aria-label={ __( 'Cerrar', 'camisama-customizer' ) }
					title={ __( 'Cerrar', 'camisama-customizer' ) }
					onClick={ onClose }
				>
					<svg
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						strokeWidth="2"
						strokeLinecap="round"
						aria-hidden="true"
						focusable="false"
					>
						<path d="M6 6l12 12M18 6 6 18" />
					</svg>
				</button>
				<h2 id="camisama-buy-title">
					{ __( 'Talla y pago', 'camisama-customizer' ) }
				</h2>
			</div>

			<div className="camisama-customizer__buy-body">
				<div className="camisama-customizer__previews">
					{ previews ? (
						previews.map( ( preview ) => (
							<img
								key={ preview.id }
								src={ preview.src }
								alt={ sprintf(
									/* translators: %s: vista de la prenda, p. ej. Frente. */
									__(
										'Tu prenda, %s',
										'camisama-customizer'
									),
									preview.label.toLowerCase()
								) }
							/>
						) )
					) : (
						<p className="camisama-customizer__hint">
							{ __(
								'Preparando la vista previa…',
								'camisama-customizer'
							) }
						</p>
					) }
				</div>
				<button
					type="button"
					className="camisama-customizer__secondary"
					onClick={ onEdit }
				>
					{ hasDesign
						? __( 'Editar diseño', 'camisama-customizer' )
						: __( 'Personalizar', 'camisama-customizer' ) }
				</button>

				{ summary }

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
											? firstSizeRef
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
								</button>
							) ) }
						</div>
						{ missing && ! choices[ option.name ] && (
							<p
								className="camisama-customizer__error"
								role="alert"
							>
								{ sprintf(
									/* translators: %s: opción que falta, p. ej. talla. */
									__(
										'Elige una %s.',
										'camisama-customizer'
									),
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

				<div className="camisama-customizer__field">
					<span id="camisama-quantity-label">
						{ __( 'Cantidad', 'camisama-customizer' ) }
					</span>
					<div
						className="camisama-customizer__quantity"
						role="group"
						aria-labelledby="camisama-quantity-label"
					>
						<button
							type="button"
							aria-label={ __(
								'Una menos',
								'camisama-customizer'
							) }
							disabled={ quantity <= 1 }
							onClick={ () => onQuantity( quantity - 1 ) }
						>
							<svg
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								strokeWidth="2"
								strokeLinecap="round"
								aria-hidden="true"
								focusable="false"
							>
								<path d="M5 12h14" />
							</svg>
						</button>
						<output aria-live="polite">{ quantity }</output>
						<button
							type="button"
							aria-label={ __(
								'Una más',
								'camisama-customizer'
							) }
							disabled={ quantity >= MAX_QUANTITY }
							onClick={ () => onQuantity( quantity + 1 ) }
						>
							<svg
								viewBox="0 0 24 24"
								fill="none"
								stroke="currentColor"
								strokeWidth="2"
								strokeLinecap="round"
								aria-hidden="true"
								focusable="false"
							>
								<path d="M12 5v14M5 12h14" />
							</svg>
						</button>
					</div>
				</div>

				{ error && (
					<p className="camisama-customizer__error" role="alert">
						{ error }
					</p>
				) }
			</div>

			<div className="camisama-customizer__buy-foot">
				{ inCart ? (
					<div className="camisama-customizer__added" role="status">
						<p>
							{ editing
								? __(
										'Cambios guardados en tu carrito.',
										'camisama-customizer'
									)
								: __(
										'Tu diseño está en el carrito.',
										'camisama-customizer'
									) }
						</p>
						<a href={ cartUrl }>
							{ __( 'Ver carrito', 'camisama-customizer' ) }
						</a>
						<button type="button" onClick={ onStartOver }>
							{ __(
								'Empezar otro diseño',
								'camisama-customizer'
							) }
						</button>
					</div>
				) : (
					<button
						type="button"
						className="camisama-customizer__cta"
						disabled={ busy }
						onClick={ onBuy }
					>
						{ action }
					</button>
				) }
			</div>
		</div>
	);
}

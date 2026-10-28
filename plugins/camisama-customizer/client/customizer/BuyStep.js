/**
 * Paso 3 del flujo móvil (docs/flujo-movil.md): talla y pago, en una pantalla propia.
 *
 * Miniaturas del diseño, desglose del precio, talla en botones sin ninguna marcada, guía de
 * tallas, cantidad y "Añadir al carrito". El pedido lo sigue enviando el formulario de
 * WooCommerce (oculto), que este paso maneja a través de wc-bridge.js.
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import SizeOptions from './SizeOptions';

const MAX_QUANTITY = 99;

export default function BuyStep( {
	previews,
	risks,
	onFix,
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
	currency,
	onChoose,
	onQuantity,
	onBuy,
	onEdit,
	onClose,
	onStartOver,
} ) {
	const rootRef = useRef( null );
	// Miniatura ampliada (al tocarla) para revisar el diseño antes de pagar.
	const [ enlarged, setEnlarged ] = useState( null );
	const closeRef = useRef( null );

	useEffect( () => closeRef.current?.focus(), [] );

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
							<button
								key={ preview.id }
								type="button"
								className={
									enlarged === preview.id
										? 'is-enlarged'
										: undefined
								}
								aria-pressed={ enlarged === preview.id }
								aria-label={ sprintf(
									/* translators: %s: vista de la prenda, p. ej. frente. */
									__(
										'Ver en grande: tu prenda, %s',
										'camisama-customizer'
									),
									preview.label.toLowerCase()
								) }
								onClick={ () =>
									setEnlarged(
										enlarged === preview.id
											? null
											: preview.id
									)
								}
							>
								<img src={ preview.src } alt="" />
							</button>
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

				{ risks.length > 0 && (
					<section
						className="camisama-customizer__review"
						aria-labelledby="camisama-review-title"
					>
						<h3 id="camisama-review-title">
							{ __(
								'Revisa antes de añadir',
								'camisama-customizer'
							) }
						</h3>
						<ul>
							{ risks.map( ( risk ) => (
								<li key={ risk.key }>
									<p>
										<strong>{ risk.title }</strong>{ ' ' }
										{ risk.text }
									</p>
									<button
										type="button"
										onClick={ () => onFix( risk.id ) }
									>
										{ __(
											'Corregir',
											'camisama-customizer'
										) }
									</button>
								</li>
							) ) }
						</ul>
					</section>
				) }

				{ summary }

				<SizeOptions
					options={ options }
					choices={ choices }
					missing={ missing }
					guide={ guide }
					currency={ currency }
					onChoose={ onChoose }
				/>

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

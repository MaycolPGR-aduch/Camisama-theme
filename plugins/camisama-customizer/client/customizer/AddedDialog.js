/**
 * Confirmación al añadir al carrito (móvil y escritorio): el diseño en grande, lo que se ha
 * pedido y el siguiente paso. Es el final del recorrido por la ficha: se queda en la marca en
 * lugar de abrir el minicarrito de WooCommerce.
 *
 * Con <dialog> nativo: el navegador deja inerte el resto, atrapa el foco, cierra con Esc y
 * devuelve el foco a donde estaba.
 */
import { useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

export default function AddedDialog( {
	previews,
	details,
	total,
	editing,
	hasDesign,
	cartUrl,
	onClose,
	onStartOver,
} ) {
	const dialogRef = useRef( null );

	useEffect( () => {
		const dialog = dialogRef.current;
		dialog.showModal();
		return () => dialog.close();
	}, [] );

	let title = __( 'Tu prenda está en el carrito', 'camisama-customizer' );
	if ( editing ) {
		title = __( 'Cambios guardados en tu carrito', 'camisama-customizer' );
	} else if ( hasDesign ) {
		title = __( 'Tu diseño está en el carrito', 'camisama-customizer' );
	}

	return (
		// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions -- Un clic en el fondo (el propio <dialog>) lo cierra; Esc ya lo cierra el navegador.
		<dialog
			ref={ dialogRef }
			className="camisama-customizer camisama-customizer__confirm"
			aria-labelledby="camisama-confirm-title"
			onClose={ onClose }
			onClick={ ( event ) => {
				if ( event.target === dialogRef.current ) {
					onClose();
				}
			} }
			onKeyDown={ ( event ) => event.stopPropagation() }
		>
			<div className="camisama-customizer__confirm-images">
				{ previews.map( ( preview ) => (
					<img
						key={ preview.id }
						src={ preview.src }
						alt={ sprintf(
							/* translators: %s: vista de la prenda, p. ej. frente. */
							__( 'Tu prenda, %s', 'camisama-customizer' ),
							preview.label.toLowerCase()
						) }
					/>
				) ) }
			</div>
			<h2 id="camisama-confirm-title">{ title }</h2>
			<p className="camisama-customizer__confirm-details">
				{ details.join( ' · ' ) }
				<strong>{ total }</strong>
			</p>
			<p className="camisama-customizer__hint">
				{ __(
					'En el carrito revisas tu pedido y, al pagar, eliges cómo recibirlo.',
					'camisama-customizer'
				) }
			</p>
			<a
				className="camisama-customizer__cta"
				href={ cartUrl }
				// eslint-disable-next-line jsx-a11y/no-autofocus -- Es el siguiente paso: <dialog> enfoca lo marcado.
				autoFocus
			>
				{ __( 'Ir al carrito', 'camisama-customizer' ) }
			</a>
			<div className="camisama-customizer__confirm-more">
				<button
					type="button"
					className="camisama-customizer__secondary"
					onClick={ onClose }
				>
					{ hasDesign
						? __( 'Seguir con este diseño', 'camisama-customizer' )
						: __( 'Seguir aquí', 'camisama-customizer' ) }
				</button>
				{ hasDesign && (
					<button
						type="button"
						className="camisama-customizer__secondary"
						onClick={ onStartOver }
					>
						{ __( 'Empezar otro diseño', 'camisama-customizer' ) }
					</button>
				) }
			</div>
		</dialog>
	);
}

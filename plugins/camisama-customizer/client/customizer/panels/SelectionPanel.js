/**
 * Elemento seleccionado: tamaño, giro, capa, duplicar y borrar, sin necesidad de arrastrar
 * (accesible con teclado y cómodo en móvil). Para las fotos, aviso de calidad de impresión.
 */
import { __, sprintf } from '@wordpress/i18n';

import { imageQuality } from '../elements';
import { formatMoney } from '../format';

export function elementLabel( element ) {
	if ( element.kind === 'text' ) {
		return sprintf(
			/* translators: %s: texto del elemento. */
			__( 'Texto «%s»', 'camisama-customizer' ),
			element.text.length > 18
				? element.text.slice( 0, 18 ) + '…'
				: element.text
		);
	}
	if ( element.kind === 'image' ) {
		return __( 'Tu imagen', 'camisama-customizer' );
	}
	return element.title;
}

const QUALITY = {
	optimal: __( 'Calidad de impresión: muy buena.', 'camisama-customizer' ),
	acceptable: __(
		'Calidad de impresión: aceptable; puede verse un poco suave. Reduce el tamaño para mejorarla.',
		'camisama-customizer'
	),
	low: __(
		'Calidad de impresión: baja; se verá pixelada. Usa una imagen más grande o reduce el tamaño.',
		'camisama-customizer'
	),
};

export default function SelectionPanel( {
	element,
	zone,
	resolution,
	tier,
	currency,
	onResize,
	onRotate,
	onCenter,
	onDuplicate,
	layer,
	onLayer,
	onRemove,
	onDone,
} ) {
	const isText = element.kind === 'text';
	const maxSize = isText
		? Math.min( zone.size_cm.h, 30 )
		: Math.max( zone.size_cm.w, zone.size_cm.h );
	const size = isText ? element.font_size_cm : element.width_cm;
	const quality =
		element.kind === 'image' ? imageQuality( element, resolution ) : null;

	return (
		<section
			className="camisama-customizer__selection"
			aria-label={ __( 'Elemento seleccionado', 'camisama-customizer' ) }
		>
			{ /* "Listo" solo en el modo diseño del móvil: suelta el elemento y vuelve a las herramientas. */ }
			<div className="camisama-customizer__selection-head">
				<h3>{ elementLabel( element ) }</h3>
				{ onDone && (
					<button
						type="button"
						className="camisama-customizer__done"
						onClick={ onDone }
					>
						{ __( 'Listo', 'camisama-customizer' ) }
					</button>
				) }
			</div>

			<div className="camisama-customizer__field">
				<label htmlFor="camisama-size">
					{ isText
						? sprintf(
								/* translators: %s: tamaño de letra en cm. */
								__(
									'Tamaño de letra: %s cm',
									'camisama-customizer'
								),
								size.toFixed( 1 )
							)
						: sprintf(
								/* translators: 1: ancho en cm, 2: alto en cm. */
								__(
									'Tamaño: %1$s × %2$s cm',
									'camisama-customizer'
								),
								element.width_cm.toFixed( 1 ),
								element.height_cm.toFixed( 1 )
							) }
				</label>
				<input
					id="camisama-size"
					type="range"
					min={ 1 }
					max={ maxSize }
					step={ 0.1 }
					value={ size }
					aria-describedby={ tier ? 'camisama-size-tier' : undefined }
					onChange={ ( event ) =>
						onResize( parseFloat( event.target.value ) )
					}
				/>
				{ /* El precio de la zona va por escalones del lado mayor: dónde está y dónde salta. */ }
				{ tier && (
					<p
						id="camisama-size-tier"
						className="camisama-customizer__hint"
					>
						{ sprintf(
							/* translators: 1: zona, 2: escalón, 3: su recargo, 4: su límite en cm, 5: lado mayor actual en cm. */
							__(
								'%1$s: %2$s (+ %3$s) hasta %4$s cm de lado; ahora %5$s cm.',
								'camisama-customizer'
							),
							tier.zone,
							tier.current.label,
							formatMoney( tier.current.amount, currency ),
							String( tier.current.max_cm ),
							tier.longest.toFixed( 1 )
						) }{ ' ' }
						{ tier.next &&
							sprintf(
								/* translators: 1: escalón siguiente, 2: su recargo. */
								__(
									'Más grande: %1$s (+ %2$s).',
									'camisama-customizer'
								),
								tier.next.label,
								formatMoney( tier.next.amount, currency )
							) }
					</p>
				) }
			</div>

			<div className="camisama-customizer__field">
				<label htmlFor="camisama-rotate">
					{ sprintf(
						/* translators: %d: grados. */
						__( 'Giro: %d°', 'camisama-customizer' ),
						Math.round(
							element.angle > 180
								? element.angle - 360
								: element.angle
						)
					) }
				</label>
				<input
					id="camisama-rotate"
					type="range"
					min={ -180 }
					max={ 180 }
					step={ 1 }
					value={
						element.angle > 180
							? element.angle - 360
							: element.angle
					}
					onChange={ ( event ) =>
						onRotate(
							( parseFloat( event.target.value ) + 360 ) % 360
						)
					}
				/>
			</div>

			{ quality && (
				<p
					className={ `camisama-customizer__quality is-${ quality.level }` }
					role={ quality.level === 'low' ? 'alert' : undefined }
				>
					{ QUALITY[ quality.level ] }
				</p>
			) }

			<div className="camisama-customizer__actions">
				<button type="button" onClick={ onCenter }>
					{ __( 'Centrar', 'camisama-customizer' ) }
				</button>
				<button type="button" onClick={ onDuplicate }>
					{ __( 'Duplicar', 'camisama-customizer' ) }
				</button>
				{ /* Solo con algo más en la zona, que es cuando puede taparlo o quedar tapado. */ }
				{ layer && (
					<>
						<button
							type="button"
							disabled={ layer.top }
							title={ __(
								'Por encima de lo demás de esta zona',
								'camisama-customizer'
							) }
							onClick={ () => onLayer( 1 ) }
						>
							{ __( 'Poner encima', 'camisama-customizer' ) }
						</button>
						<button
							type="button"
							disabled={ layer.bottom }
							title={ __(
								'Por debajo de lo demás de esta zona',
								'camisama-customizer'
							) }
							onClick={ () => onLayer( -1 ) }
						>
							{ __( 'Poner debajo', 'camisama-customizer' ) }
						</button>
					</>
				) }
				<button
					type="button"
					className="is-destructive"
					onClick={ onRemove }
				>
					{ __( 'Eliminar', 'camisama-customizer' ) }
				</button>
			</div>
		</section>
	);
}

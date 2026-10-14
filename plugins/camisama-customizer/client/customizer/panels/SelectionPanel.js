/**
 * Elemento seleccionado: tamaño, giro, capa, duplicar y borrar, sin necesidad de arrastrar
 * (accesible con teclado y cómodo en móvil). Para las fotos, aviso de calidad de impresión.
 */
import { __, sprintf } from '@wordpress/i18n';

import { imageQuality } from '../elements';

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
	onResize,
	onRotate,
	onCenter,
	onDuplicate,
	onLayer,
	onRemove,
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
			<h3>{ elementLabel( element ) }</h3>

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
					onChange={ ( event ) =>
						onResize( parseFloat( event.target.value ) )
					}
				/>
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
				<button type="button" onClick={ () => onLayer( 1 ) }>
					{ __( 'Traer adelante', 'camisama-customizer' ) }
				</button>
				<button type="button" onClick={ () => onLayer( -1 ) }>
					{ __( 'Enviar atrás', 'camisama-customizer' ) }
				</button>
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

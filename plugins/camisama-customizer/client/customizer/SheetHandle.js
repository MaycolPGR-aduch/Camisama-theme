/**
 * Asa de la hoja de herramientas del modo diseño en el móvil (docs/flujo-movil.md).
 *
 * Tres alturas: media (al abrir), casi completa (para ver el catálogo) y mínima (para ver la
 * prenda entera). Un toque alterna media y completa (desde la mínima, vuelve a media); deslizar
 * el asa sube o baja un paso; con teclado, Intro y las flechas arriba y abajo.
 */
import { useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export const SHEET_STEPS = [ 'min', 'half', 'full' ];

// Desplazamiento en px a partir del cual el gesto cuenta como deslizar y no como toque.
const SWIPE = 12;

const LABELS = {
	min: __( 'Mostrar herramientas', 'camisama-customizer' ),
	half: __( 'Ampliar herramientas', 'camisama-customizer' ),
	full: __( 'Reducir herramientas', 'camisama-customizer' ),
};

export default function SheetHandle( { step, onChange } ) {
	const start = useRef( null );
	const index = SHEET_STEPS.indexOf( step );
	const move = ( delta ) =>
		onChange(
			SHEET_STEPS[
				Math.min( SHEET_STEPS.length - 1, Math.max( 0, index + delta ) )
			]
		);
	const toggle = () => onChange( step === 'half' ? 'full' : 'half' );

	return (
		<button
			type="button"
			className="camisama-customizer__handle"
			aria-expanded={ step !== 'min' }
			aria-label={ LABELS[ step ] }
			onPointerDown={ ( event ) => {
				start.current = event.clientY;
				event.currentTarget.setPointerCapture( event.pointerId );
			} }
			onPointerUp={ ( event ) => {
				if ( start.current === null ) {
					return;
				}
				const dy = event.clientY - start.current;
				start.current = null;
				if ( Math.abs( dy ) >= SWIPE ) {
					move( dy < 0 ? 1 : -1 );
				} else {
					toggle();
				}
			} }
			onPointerCancel={ () => {
				start.current = null;
			} }
			onClick={ ( event ) => {
				// Con ratón o dedo ya respondió onPointerUp; aquí solo el teclado (sin puntero).
				if ( event.detail === 0 ) {
					toggle();
				}
			} }
			onKeyDown={ ( event ) => {
				if ( event.key === 'ArrowUp' || event.key === 'ArrowDown' ) {
					event.preventDefault();
					event.stopPropagation();
					move( event.key === 'ArrowUp' ? 1 : -1 );
				}
			} }
		>
			{ step === 'min' && <span>{ LABELS.min }</span> }
		</button>
	);
}

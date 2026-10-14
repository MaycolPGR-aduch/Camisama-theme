/**
 * Texto: añadir uno nuevo o editar el seleccionado (contenido, fuente, color y alineación).
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { loadFont } from '../fonts';

const COLORS = [
	{ hex: '#16161A', name: __( 'Negro', 'camisama-customizer' ) },
	{ hex: '#FFFFFF', name: __( 'Blanco', 'camisama-customizer' ) },
	{ hex: '#D3261C', name: __( 'Rojo', 'camisama-customizer' ) },
	{ hex: '#26357F', name: __( 'Índigo', 'camisama-customizer' ) },
	{ hex: '#F5B700', name: __( 'Mostaza', 'camisama-customizer' ) },
	{ hex: '#2E8B57', name: __( 'Verde', 'camisama-customizer' ) },
];

const ALIGNS = [
	{ value: 'left', label: __( 'Izquierda', 'camisama-customizer' ) },
	{ value: 'center', label: __( 'Centro', 'camisama-customizer' ) },
	{ value: 'right', label: __( 'Derecha', 'camisama-customizer' ) },
];

export const MAX_TEXT = 60;

export default function TextPanel( { fonts, selected, onAdd, onChange } ) {
	const [ draft, setDraft ] = useState( {
		text: '',
		font: fonts[ 0 ].slug,
		color: COLORS[ 0 ].hex,
		align: 'center',
	} );

	// Las fuentes se cargan al abrir el panel para que el selector las muestre tal cual.
	useEffect( () => {
		fonts.forEach( ( font ) => loadFont( font ).catch( () => {} ) );
	}, [ fonts ] );

	const editing = selected?.kind === 'text';
	const values = editing ? selected : draft;
	const update = ( patch ) =>
		editing ? onChange( patch ) : setDraft( { ...draft, ...patch } );

	return (
		<div className="camisama-customizer__panel">
			<div className="camisama-customizer__field">
				<label htmlFor="camisama-text">
					{ __( 'Texto', 'camisama-customizer' ) }
				</label>
				<textarea
					id="camisama-text"
					rows={ 2 }
					maxLength={ MAX_TEXT }
					value={ values.text }
					placeholder={ __( 'Escribe aquí', 'camisama-customizer' ) }
					onChange={ ( event ) => {
						// Un texto vacío no se puede imprimir: se mantiene al menos un carácter al editar.
						if ( editing && ! event.target.value.trim() ) {
							return;
						}
						update( { text: event.target.value } );
					} }
				/>
			</div>

			<div className="camisama-customizer__field">
				<label htmlFor="camisama-font">
					{ __( 'Fuente', 'camisama-customizer' ) }
				</label>
				<select
					id="camisama-font"
					value={ values.font }
					onChange={ ( event ) =>
						update( { font: event.target.value } )
					}
				>
					{ fonts.map( ( font ) => (
						<option
							key={ font.slug }
							value={ font.slug }
							style={ { fontFamily: `"${ font.family }"` } }
						>
							{ font.name }
						</option>
					) ) }
				</select>
			</div>

			<fieldset className="camisama-customizer__field">
				<legend>{ __( 'Color', 'camisama-customizer' ) }</legend>
				<div className="camisama-customizer__palette">
					{ COLORS.map( ( color ) => (
						<button
							key={ color.hex }
							type="button"
							className="camisama-customizer__swatch"
							style={ { '--swatch': color.hex } }
							aria-pressed={
								values.color.toUpperCase() === color.hex
							}
							aria-label={ color.name }
							title={ color.name }
							onClick={ () => update( { color: color.hex } ) }
						/>
					) ) }
					<span className="camisama-customizer__custom-color">
						<label
							htmlFor="camisama-text-color"
							className="camisama-customizer__sr-only"
						>
							{ __( 'Otro color', 'camisama-customizer' ) }
						</label>
						<input
							id="camisama-text-color"
							type="color"
							value={ values.color }
							onChange={ ( event ) =>
								update( {
									color: event.target.value.toUpperCase(),
								} )
							}
						/>
					</span>
				</div>
			</fieldset>

			<fieldset className="camisama-customizer__field">
				<legend>{ __( 'Alineación', 'camisama-customizer' ) }</legend>
				<div className="camisama-customizer__chips">
					{ ALIGNS.map( ( align ) => (
						<button
							key={ align.value }
							type="button"
							aria-pressed={ values.align === align.value }
							onClick={ () => update( { align: align.value } ) }
						>
							{ align.label }
						</button>
					) ) }
				</div>
			</fieldset>

			{ ! editing && (
				<button
					type="button"
					className="camisama-customizer__button"
					disabled={ ! draft.text.trim() }
					onClick={ () => {
						onAdd( { ...draft, text: draft.text.trim() } );
						setDraft( { ...draft, text: '' } );
					} }
				>
					{ __( 'Añadir texto', 'camisama-customizer' ) }
				</button>
			) }
		</div>
	);
}

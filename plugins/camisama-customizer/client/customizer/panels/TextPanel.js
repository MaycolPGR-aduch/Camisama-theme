/**
 * Texto: escribir uno nuevo o editar el seleccionado (contenido, fuente, color y alineación).
 *
 * Lo escrito aparece en la prenda al teclear la primera letra y se edita en vivo; borrarlo todo
 * lo quita. Mientras el cliente no elige color, el texto nuevo toma el que más contrasta con la
 * prenda.
 */
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import { isReadable, mostReadable } from '../contrast';
import { loadFont } from '../fonts';

export const TEXT_COLORS = [
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

// Al partir una línea en dos, cada una mide la mitad: la letra puede casi doblarse.
const SPLIT_GROWTH = 1.8;

/**
 * El texto partido en dos líneas por el espacio más cercano a la mitad de su línea más larga, o
 * null si no hay dónde partirlo.
 *
 * @param {string} text Texto.
 * @return {?string} Texto con un salto de línea más.
 */
export function splitLines( text ) {
	const lines = text.split( '\n' );
	const longest = lines.reduce(
		( best, line, index ) =>
			line.length > lines[ best ].length ? index : best,
		0
	);
	const line = lines[ longest ];
	const middle = line.length / 2;
	let cut = -1;
	for ( let i = 0; i < line.length; i++ ) {
		if (
			line[ i ] === ' ' &&
			( cut < 0 || Math.abs( i - middle ) < Math.abs( cut - middle ) )
		) {
			cut = i;
		}
	}
	if ( cut <= 0 ) {
		return null;
	}
	lines[ longest ] = line.slice( 0, cut ) + '\n' + line.slice( cut + 1 );
	return lines.join( '\n' );
}

export default function TextPanel( {
	fonts,
	selected,
	garment,
	onAdd,
	onChange,
	onRemove,
	onNew,
	cramped,
} ) {
	const textRef = useRef( null );
	// color null: automático, el más legible sobre la prenda (y sigue al cambiarla).
	const [ draft, setDraft ] = useState( {
		text: '',
		font: fonts[ 0 ].slug,
		color: null,
		align: 'center',
	} );

	// Las fuentes se cargan al abrir el panel para que el selector las muestre tal cual.
	useEffect( () => {
		fonts.forEach( ( font ) => loadFont( font ).catch( () => {} ) );
	}, [ fonts ] );

	const editing = selected?.kind === 'text';

	// Al crearse el texto (o elegir otro) mientras se escribe, el panel del elemento aparece
	// encima y empuja el campo: se mantiene a la vista para seguir escribiendo.
	useEffect( () => {
		if (
			textRef.current === textRef.current?.ownerDocument.activeElement
		) {
			textRef.current.scrollIntoView( { block: 'nearest' } );
		}
	}, [ editing, selected?.id ] );

	const values = editing
		? selected
		: {
				...draft,
				color:
					draft.color ??
					mostReadable(
						garment,
						TEXT_COLORS.map( ( item ) => item.hex )
					),
			};
	const readable = isReadable( values.color, garment );
	const update = ( patch ) =>
		editing ? onChange( patch ) : setDraft( { ...draft, ...patch } );

	return (
		<div className="camisama-customizer__panel">
			<div className="camisama-customizer__field">
				<label htmlFor="camisama-text">
					{ __( 'Texto', 'camisama-customizer' ) }
				</label>
				<textarea
					ref={ textRef }
					id="camisama-text"
					rows={ 2 }
					maxLength={ MAX_TEXT }
					value={ values.text }
					aria-describedby="camisama-text-hint"
					placeholder={ __( 'Escribe aquí', 'camisama-customizer' ) }
					onChange={ ( event ) => {
						const text = event.target.value;
						if ( editing ) {
							// Sin nada que imprimir, el texto sale de la prenda; su estilo
							// queda para el siguiente.
							if ( ! text.trim() ) {
								setDraft( {
									text: '',
									font: selected.font,
									color: selected.color,
									align: selected.align,
								} );
								onRemove();
								return;
							}
							onChange( { text } );
							return;
						}
						// Con la primera letra nace el texto en la prenda; desde ahí se edita ese
						// texto y el borrador queda vacío para el siguiente.
						if ( text.trim() ) {
							setDraft( { ...draft, text: '' } );
							onAdd( { ...values, text } );
							return;
						}
						setDraft( { ...draft, text } );
					} }
				/>
				<p
					id="camisama-text-hint"
					className="camisama-customizer__hint"
				>
					{ editing
						? __(
								'Los cambios se ven al momento en la prenda.',
								'camisama-customizer'
							)
						: __(
								'Lo que escribas aparece en la prenda.',
								'camisama-customizer'
							) }{ ' ' }
					<span className="camisama-customizer__count">
						{ sprintf(
							/* translators: 1: caracteres escritos, 2: máximo. */
							__( '%1$d/%2$d', 'camisama-customizer' ),
							values.text.length,
							MAX_TEXT
						) }
					</span>
				</p>
				{ values.text.length >= MAX_TEXT && (
					<p className="camisama-customizer__contrast" role="status">
						{ sprintf(
							/* translators: %d: máximo de caracteres. */
							__(
								'Has llegado al máximo de %d caracteres.',
								'camisama-customizer'
							),
							MAX_TEXT
						) }
					</p>
				) }
				{ /* Una línea larga deja la letra pequeña: en dos líneas puede crecer (el lienzo la
				vuelve a encajar en la zona si se pasa). */ }
				{ editing && cramped && (
					<div
						className="camisama-customizer__contrast"
						role="status"
					>
						<p>
							{ sprintf(
								/* translators: %s: alto de la letra en cm. */
								__(
									'En una sola línea la letra se queda en %s cm.',
									'camisama-customizer'
								),
								selected.font_size_cm.toFixed( 1 )
							) }
						</p>
						{ splitLines( selected.text ) && (
							<button
								type="button"
								onClick={ () =>
									onChange( {
										text: splitLines( selected.text ),
										font_size_cm:
											selected.font_size_cm *
											SPLIT_GROWTH,
										width_cm: selected.width_cm * 0.9,
										height_cm: selected.height_cm * 3.6,
									} )
								}
							>
								{ __(
									'Partir en dos líneas y agrandar',
									'camisama-customizer'
								) }
							</button>
						) }
					</div>
				) }
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
					{ TEXT_COLORS.map( ( color ) => (
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
							title={ __( 'Otro color', 'camisama-customizer' ) }
							value={ values.color }
							onChange={ ( event ) =>
								update( {
									color: event.target.value.toUpperCase(),
								} )
							}
						/>
					</span>
				</div>
				{ ! readable && (
					<p className="camisama-customizer__contrast">
						{ __(
							'Este color casi no se verá sobre la prenda. Elige uno que contraste más.',
							'camisama-customizer'
						) }
					</p>
				) }
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

			{ /* Con un texto en la prenda, otro aparte (con el mismo estilo) sin tener que deseleccionar. */ }
			{ editing && (
				<button
					type="button"
					className="camisama-customizer__button"
					onClick={ () => {
						setDraft( {
							text: '',
							font: selected.font,
							color: selected.color,
							align: selected.align,
						} );
						onNew();
						textRef.current?.focus();
					} }
				>
					{ __( 'Nuevo texto', 'camisama-customizer' ) }
				</button>
			) }
		</div>
	);
}

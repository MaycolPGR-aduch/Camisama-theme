/**
 * Personalizador de la tienda.
 *
 * El diseño (lista de elementos en cm de cada zona) es la fuente de verdad y vive en un historial
 * para deshacer y rehacer; el lienzo lo dibuja y el resumen lo cotiza con la misma regla que el
 * servidor (pricing.js).
 */
import {
	createPortal,
	useEffect,
	useMemo,
	useReducer,
	useRef,
	useState,
} from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import FabricStage from './FabricStage';
import PurchaseBar from './PurchaseBar';
import Summary from './Summary';
import DesignsPanel from './panels/DesignsPanel';
import SelectionPanel, { elementLabel } from './panels/SelectionPanel';
import TextPanel, { TEXT_COLORS } from './panels/TextPanel';
import UploadPanel from './panels/UploadPanel';
import {
	designElement,
	fitToZone,
	imageElement,
	newId,
	textElement,
} from './elements';
import { isReadable, mostReadable } from './contrast';
import { loadDraft, saveDraft } from './draft';
import { formatMoney } from './format';
import { historyReducer, initialHistory } from './history';
import { quote } from './pricing';
import { renderPreview } from './renderer';
import { connectVariationForm } from './wc-bridge';

/** Campos de cada elemento que viajan al servidor; URLs, títulos y precios los pone él. */
const SENT_FIELDS = [
	'id',
	'zone',
	'kind',
	'cx',
	'cy',
	'width_cm',
	'height_cm',
	'angle',
	'design_id',
	'upload_id',
	'text',
	'font',
	'color',
	'align',
	'font_size_cm',
];

/**
 * Campos ocultos que acompañan al producto al añadirlo al carrito.
 *
 * @param {Object}  data     Datos del personalizador.
 * @param {Array}   elements Diseño.
 * @param {string}  color    Color de la prenda.
 * @param {?string} replace  Línea del carrito que se está editando (se sustituye), o null.
 * @return {Promise<Object>} { camisama_design, camisama_previews, camisama_replace }; sin diseño,
 *                           solo camisama_replace (si se edita) o nada.
 */
async function cartFields( data, elements, color, replace ) {
	const target = replace ? { camisama_replace: replace } : {};
	if ( ! elements.length ) {
		return target;
	}

	const previews = {};
	for ( const view of data.views ) {
		const zones = data.zones.filter( ( zone ) => zone.view === view.id );
		const inView = elements.filter( ( element ) =>
			zones.some( ( zone ) => zone.id === element.zone )
		);
		const mockup =
			view.mockups[ color ] ?? Object.values( view.mockups )[ 0 ];
		if ( inView.length && mockup ) {
			previews[ view.id ] = await renderPreview( {
				mockup,
				zones,
				elements: inView,
				fonts: data.fonts,
			} );
		}
	}

	return {
		camisama_design: JSON.stringify( {
			color,
			elements: elements.map( ( element ) =>
				Object.fromEntries(
					SENT_FIELDS.filter( ( key ) => key in element ).map(
						( key ) => [ key, element[ key ] ]
					)
				)
			),
		} ),
		camisama_previews: JSON.stringify( previews ),
		...target,
	};
}

/**
 * Estado inicial al editar una línea del carrito: su diseño, en la vista de su primer elemento.
 *
 * @param {Object} edit Línea (render.php: CartEdit::current).
 * @param {Object} data Datos del personalizador.
 * @return {Object} { elements, color, viewId, zoneId }.
 */
function fromCart( edit, data ) {
	const zone = data.zones.find(
		( item ) => item.id === edit.elements[ 0 ]?.zone
	);
	return {
		elements: edit.elements,
		color: edit.color,
		viewId: zone?.view ?? null,
		zoneId: zone?.id ?? null,
	};
}

/**
 * Cambia (o quita) ?camisama_edit= en la barra de direcciones, para que recargar abra lo mismo.
 *
 * @param {?string} key Línea que se edita, o null.
 */
function setEditParam( key ) {
	const url = new window.URL( window.location.href );
	if ( key ) {
		url.searchParams.set( 'camisama_edit', key );
	} else {
		url.searchParams.delete( 'camisama_edit' );
	}
	window.history.replaceState( window.history.state, '', url );
}

const TABS = [
	{ id: 'designs', label: __( 'Estampados', 'camisama-customizer' ) },
	{ id: 'text', label: __( 'Texto', 'camisama-customizer' ) },
	{ id: 'image', label: __( 'Tu imagen', 'camisama-customizer' ) },
];

/**
 * Flecha de deshacer (rehacer es la misma, reflejada). Decorativa: el botón lleva su etiqueta.
 *
 * @param {Object}  props      Propiedades.
 * @param {boolean} props.redo Reflejada, para rehacer.
 */
const HistoryIcon = ( { redo = false } ) => (
	<svg
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		strokeWidth="2"
		strokeLinecap="round"
		strokeLinejoin="round"
		aria-hidden="true"
		focusable="false"
		style={ redo ? { transform: 'scaleX(-1)' } : undefined }
	>
		<path d="M9 14 4 9l5-5" />
		<path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11" />
	</svg>
);

const isTyping = ( target ) =>
	[ 'INPUT', 'TEXTAREA', 'SELECT' ].includes( target.tagName );

export default function App( { data } ) {
	// Diseño de partida: la línea del carrito que se edita o, si no, el que se dejó a medias en
	// este dispositivo (draft.js). Null si no hay ninguno.
	const [ restored ] = useState( () =>
		data.edit
			? fromCart( data.edit, data )
			: loadDraft( data.product_id, data )
	);
	// Línea del carrito que se está editando: al guardar, el diseño la sustituye.
	const [ editKey, setEditKey ] = useState( data.edit?.key ?? null );
	const [ history, dispatch ] = useReducer(
		historyReducer,
		restored,
		( draft ) => ( { ...initialHistory, present: draft?.elements ?? [] } )
	);
	const elements = history.present;
	const [ selectedId, setSelectedId ] = useState( null );
	const [ color, setColor ] = useState(
		restored?.color ?? data.colors[ 0 ].slug
	);
	const [ viewId, setViewId ] = useState(
		restored?.viewId ?? data.views[ 0 ]?.id
	);
	const [ activeZoneId, setActiveZoneId ] = useState(
		restored?.zoneId ??
			data.zones.find( ( zone ) => zone.view === data.views[ 0 ]?.id )?.id
	);
	const [ restoredNotice, setRestoredNotice ] = useState(
		Boolean( restored ) && ! data.edit
	);
	// Diseño tal como se añadió al carrito: mientras no cambie, se da por comprado.
	const [ addedDesign, setAddedDesign ] = useState( null );
	const inCart = addedDesign !== null && addedDesign === elements;
	const [ tab, setTab ] = useState( 'designs' );
	const [ variationPrice, setVariationPrice ] = useState( null );
	const [ message, setMessage ] = useState( '' );
	const [ submitError, setSubmitError ] = useState( null );
	// Opciones elegidas en WooCommerce aparte del color ("Talla M"), o null si falta alguna.
	const [ chosenOptions, setChosenOptions ] = useState( null );
	const [ busy, setBusy ] = useState( false );
	// Hueco del resumen en el formulario de compra; sin él, el resumen va al final del editor.
	const [ purchaseNode, setPurchaseNode ] = useState( null );
	const bridge = useRef( null );
	// El puente se crea una vez; al enviar necesita el diseño y el color de ese momento.
	const latest = useRef( {} );
	latest.current = { elements, color, editKey };

	useEffect( () => {
		bridge.current = connectVariationForm( data.product_id, data.cart, {
			onColor: setColor,
			onPrice: setVariationPrice,
			onOptions: setChosenOptions,
			onBusy: setBusy,
			onSubmit: () => {
				setSubmitError( null );
				return cartFields(
					data,
					latest.current.elements,
					latest.current.color,
					latest.current.editKey
				);
			},
			onAdded: ( key ) => {
				setAddedDesign( latest.current.elements );
				setRestoredNotice( false );
				// Guardado al editar: la línea nueva es la que se sigue editando.
				if ( latest.current.editKey && key ) {
					setEditKey( key );
					setEditParam( key );
				}
			},
			onError: ( error ) => {
				setSubmitError(
					error?.notice
						? error.message
						: __(
								'No pudimos preparar tu diseño. Inténtalo de nuevo.',
								'camisama-customizer'
							)
				);
				// El error sale en el resumen: si se compró desde la barra del móvil, está lejos.
				bridge.current.summaryNode?.scrollIntoView( {
					block: 'nearest',
				} );
			},
		} );
		setPurchaseNode( bridge.current.summaryNode );
		// El formulario de WooCommerce arranca con su color por defecto: manda el del borrador.
		if ( restored?.color ) {
			bridge.current.setColor( restored.color );
		}
		if ( data.edit ) {
			bridge.current.prefill( data.edit.attributes, data.edit.quantity );
			bridge.current.setLabel(
				__( 'Guardar cambios', 'camisama-customizer' )
			);
		}
		return () => bridge.current.disconnect();
	}, [ data, restored ] );

	// Borrador: se guarda poco después de cada cambio (no en cada paso de un arrastre). Al editar
	// una línea del carrito no: salir sin guardar es descartar los cambios.
	useEffect( () => {
		if ( editKey ) {
			return;
		}
		const timer = setTimeout(
			() =>
				saveDraft( data.product_id, {
					elements,
					color,
					viewId,
					zoneId: activeZoneId,
					added: inCart,
				} ),
			300
		);
		return () => clearTimeout( timer );
	}, [
		data.product_id,
		elements,
		color,
		viewId,
		activeZoneId,
		inCart,
		editKey,
	] );

	const zoneById = ( id ) => data.zones.find( ( zone ) => zone.id === id );
	const view = data.views.find( ( item ) => item.id === viewId );
	const viewZones = useMemo(
		() => data.zones.filter( ( zone ) => zone.view === viewId ),
		[ data.zones, viewId ]
	);
	const viewElements = useMemo(
		() =>
			elements.filter( ( element ) =>
				viewZones.some( ( zone ) => zone.id === element.zone )
			),
		[ elements, viewZones ]
	);
	const mockup =
		view?.mockups[ color ] ?? Object.values( view?.mockups ?? {} )[ 0 ];
	const activeZone = zoneById( activeZoneId ) ?? viewZones[ 0 ];
	const selected =
		elements.find( ( element ) => element.id === selectedId ) ?? null;

	// --- Cambios en el diseño ---------------------------------------------------------

	const commit = ( update, options = {} ) =>
		dispatch( { type: 'set', update, ...options } );

	// `change` puede ser una función del elemento actual: así varios cambios seguidos (p. ej.
	// pulsaciones de flechas antes de que React vuelva a pintar) parten del último estado.
	const updateElement = ( id, change, options ) =>
		commit(
			( list ) =>
				list.map( ( element ) =>
					element.id === id
						? fitToZone(
								{
									...element,
									...( typeof change === 'function'
										? change( element )
										: change ),
								},
								zoneById( element.zone )
							)
						: element
				),
			options
		);

	const add = ( element ) => {
		const fitted = fitToZone( element, zoneById( element.zone ) );
		commit( ( list ) => [ ...list, fitted ] );
		setSelectedId( fitted.id );
		setMessage(
			sprintf(
				/* translators: 1: elemento, 2: zona. */
				__( '%1$s añadido en %2$s.', 'camisama-customizer' ),
				elementLabel( fitted ),
				activeZone.label
			)
		);
	};

	const remove = ( id ) => {
		commit( ( list ) => list.filter( ( element ) => element.id !== id ) );
		setSelectedId( null );
	};

	const moveLayer = ( id, direction ) =>
		commit( ( list ) => {
			const index = list.findIndex( ( element ) => element.id === id );
			const target = index + direction;
			if ( target < 0 || target >= list.length ) {
				return list;
			}
			const next = [ ...list ];
			[ next[ index ], next[ target ] ] = [
				next[ target ],
				next[ index ],
			];
			return next;
		} );

	const select = ( id ) => {
		setSelectedId( id );
		const element = elements.find( ( item ) => item.id === id );
		if ( element ) {
			setActiveZoneId( element.zone );
			if ( element.kind === 'text' ) {
				setTab( 'text' );
			}
		}
	};

	const resize = ( element, value ) => {
		const key = `resize-${ element.id }`;
		if ( element.kind === 'text' ) {
			const factor = value / element.font_size_cm;
			updateElement(
				element.id,
				{
					font_size_cm: value,
					width_cm: element.width_cm * factor,
					height_cm: element.height_cm * factor,
				},
				{ merge: true, key }
			);
			return;
		}
		updateElement(
			element.id,
			{
				width_cm: value,
				height_cm: ( value * element.height_cm ) / element.width_cm,
			},
			{ merge: true, key }
		);
	};

	// --- Selección de color y vista -----------------------------------------------------

	const chooseColor = ( slug ) => {
		setColor( slug );
		bridge.current?.setColor( slug );
	};

	const chooseView = ( id ) => {
		setViewId( id );
		setSelectedId( null );
		setActiveZoneId(
			data.zones.find( ( zone ) => zone.view === id )?.id ?? null
		);
	};

	// --- Teclado ----------------------------------------------------------------------

	const onKeyDown = ( event ) => {
		if ( isTyping( event.target ) ) {
			return;
		}
		const mod = event.ctrlKey || event.metaKey;
		const key = event.key.toLowerCase();

		if ( mod && key === 'z' ) {
			dispatch( { type: event.shiftKey ? 'redo' : 'undo' } );
		} else if ( mod && key === 'y' ) {
			dispatch( { type: 'redo' } );
		} else if ( selected && ( key === 'delete' || key === 'backspace' ) ) {
			remove( selected.id );
		} else if ( selected && key === 'escape' ) {
			setSelectedId( null );
		} else if ( selected && key.startsWith( 'arrow' ) ) {
			const step = event.shiftKey ? 1 : 0.2;
			const delta = {
				arrowleft: [ -step, 0 ],
				arrowright: [ step, 0 ],
				arrowup: [ 0, -step ],
				arrowdown: [ 0, step ],
			}[ key ];
			updateElement(
				selected.id,
				( element ) => ( {
					cx: element.cx + delta[ 0 ],
					cy: element.cy + delta[ 1 ],
				} ),
				{ merge: true, key: `nudge-${ selected.id }` }
			);
		} else {
			return;
		}
		event.preventDefault();
	};

	// --- Precio -----------------------------------------------------------------------

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

	// Empezar de cero (tras recuperar un borrador o añadir al carrito); se puede deshacer.
	const startOver = () => {
		setSelectedId( null );
		setRestoredNotice( false );
		setAddedDesign( null );
		// Un diseño nuevo ya no sustituye a la línea que se editaba.
		if ( editKey ) {
			setEditKey( null );
			setEditParam( null );
			bridge.current.setLabel( null );
		}
		commit( () => [] );
	};

	const summary = (
		<Summary
			garmentPrice={ variationPrice ?? data.base_price }
			priceIsFrom={ variationPrice === null }
			quote={ priced.quote }
			error={ priced.error ?? submitError }
			currency={ data.currency }
		/>
	);

	// Resumen y, tras añadir, la confirmación junto al botón que se acaba de pulsar. El
	// minicarrito muestra además la miniatura del diseño.
	const purchase = (
		<>
			{ summary }
			{ inCart && (
				<div className="camisama-customizer__added" role="status">
					<p>
						{ editKey
							? __(
									'Cambios guardados en tu carrito.',
									'camisama-customizer'
								)
							: __(
									'Tu diseño está en el carrito.',
									'camisama-customizer'
								) }
					</p>
					<a href={ data.cart.url }>
						{ __( 'Ver carrito', 'camisama-customizer' ) }
					</a>
					<button type="button" onClick={ startOver }>
						{ __( 'Empezar otro diseño', 'camisama-customizer' ) }
					</button>
				</div>
			) }
		</>
	);

	// Textos que casi no se ven sobre la prenda elegida (p. ej. negro sobre negro). No se
	// cambian solos: se avisa y se ofrece el color más legible en un toque.
	const garment = data.colors.find( ( item ) => item.slug === color );
	const hiddenTexts = elements.filter(
		( element ) =>
			element.kind === 'text' &&
			! isReadable( element.color, garment?.hex )
	);
	const readableColor = TEXT_COLORS.find(
		( item ) =>
			item.hex ===
			mostReadable(
				garment?.hex,
				TEXT_COLORS.map( ( option ) => option.hex )
			)
	);
	const fixContrast = () => {
		const ids = new Set( hiddenTexts.map( ( element ) => element.id ) );
		commit( ( list ) =>
			list.map( ( element ) =>
				ids.has( element.id )
					? { ...element, color: readableColor.hex }
					: element
			)
		);
	};

	const garmentPrice = variationPrice ?? data.base_price;
	const totalLabel =
		( variationPrice === null
			? __( 'desde', 'camisama-customizer' ) + ' '
			: '' ) +
		formatMoney(
			garmentPrice + ( priced.quote?.total ?? 0 ),
			data.currency
		);

	return (
		// eslint-disable-next-line jsx-a11y/no-static-element-interactions -- atajos de teclado del editor; cada control sigue siendo accesible por sí mismo.
		<div
			className={ `camisama-customizer${
				purchaseNode ? ' has-purchase-bar' : ''
			}` }
			onKeyDown={ onKeyDown }
		>
			{ /* Vistas, deshacer y lienzo: en el móvil quedan fijos arriba mientras se usan las herramientas. */ }
			<div className="camisama-customizer__canvas">
				<div className="camisama-customizer__topbar">
					<div
						className="camisama-customizer__views"
						role="group"
						aria-label={ __(
							'Vista de la prenda',
							'camisama-customizer'
						) }
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
					<div className="camisama-customizer__history">
						<button
							type="button"
							disabled={ ! history.past.length }
							onClick={ () => dispatch( { type: 'undo' } ) }
							aria-label={ __(
								'Deshacer',
								'camisama-customizer'
							) }
							title={ __(
								'Deshacer (Ctrl+Z)',
								'camisama-customizer'
							) }
						>
							<HistoryIcon />
						</button>
						<button
							type="button"
							disabled={ ! history.future.length }
							onClick={ () => dispatch( { type: 'redo' } ) }
							aria-label={ __(
								'Rehacer',
								'camisama-customizer'
							) }
							title={ __(
								'Rehacer (Ctrl+Y)',
								'camisama-customizer'
							) }
						>
							<HistoryIcon redo />
						</button>
					</div>
				</div>

				<FabricStage
					mockup={ mockup }
					zones={ viewZones }
					activeZoneId={ activeZone?.id }
					elements={ viewElements }
					fonts={ data.fonts }
					selectedId={ selectedId }
					label={ sprintf(
						/* translators: %s: vista de la prenda. */
						__(
							'Diseño de la prenda, vista %s. Selecciona un elemento para moverlo con el ratón, el dedo o las flechas del teclado.',
							'camisama-customizer'
						),
						view?.label ?? ''
					) }
					onSelect={ select }
					onSelectZone={ setActiveZoneId }
					onTransform={ ( id, geometry ) =>
						commit( ( list ) =>
							list.map( ( element ) =>
								element.id === id
									? { ...element, ...geometry }
									: element
							)
						)
					}
					onMeasure={ ( id, size ) =>
						dispatch( {
							type: 'patch',
							update: ( list ) =>
								list.map( ( element ) =>
									element.id === id
										? fitToZone(
												{ ...element, ...size },
												zoneById( element.zone )
											)
										: element
								),
						} )
					}
					onError={ ( id ) => {
						dispatch( {
							type: 'patch',
							update: ( list ) =>
								list.filter( ( element ) => element.id !== id ),
						} );
						setMessage(
							__(
								'No se pudo cargar un elemento. Inténtalo de nuevo.',
								'camisama-customizer'
							)
						);
					} }
				/>
				{ editKey && (
					<div
						className="camisama-customizer__restored"
						role="status"
					>
						<p>
							{ __(
								'Estás editando un diseño de tu carrito: al guardar, lo reemplazas.',
								'camisama-customizer'
							) }
						</p>
						<a href={ data.cart.url }>
							{ __( 'Volver al carrito', 'camisama-customizer' ) }
						</a>
					</div>
				) }
				{ restoredNotice && ! history.past.length && (
					<div
						className="camisama-customizer__restored"
						role="status"
					>
						<p>
							{ __(
								'Recuperamos el diseño que dejaste a medias.',
								'camisama-customizer'
							) }
						</p>
						<button type="button" onClick={ startOver }>
							{ __( 'Empezar de cero', 'camisama-customizer' ) }
						</button>
					</div>
				) }
				{ hiddenTexts.length > 0 && garment && (
					<div
						className="camisama-customizer__contrast"
						role="status"
					>
						<p>
							{ hiddenTexts.length === 1
								? sprintf(
										/* translators: 1: texto, p. ej. Texto «Feliz cumple», 2: color de la prenda. */
										__(
											'%1$s casi no se ve sobre una prenda de color %2$s.',
											'camisama-customizer'
										),
										elementLabel( hiddenTexts[ 0 ] ),
										garment.name.toLowerCase()
									)
								: sprintf(
										/* translators: 1: número de textos, 2: color de la prenda. */
										__(
											'%1$d textos casi no se ven sobre una prenda de color %2$s.',
											'camisama-customizer'
										),
										hiddenTexts.length,
										garment.name.toLowerCase()
									) }
						</p>
						<button type="button" onClick={ fixContrast }>
							{ sprintf(
								/* translators: %s: color del texto, p. ej. blanco. */
								__( 'Cambiar a %s', 'camisama-customizer' ),
								readableColor.name.toLowerCase()
							) }
						</button>
					</div>
				) }
			</div>

			<p className="camisama-customizer__sr-only" aria-live="polite">
				{ message }
			</p>

			<div
				className="camisama-customizer__zone-picker"
				role="group"
				aria-label={ __( 'Zona donde añadir', 'camisama-customizer' ) }
			>
				<span>{ __( 'Añadir en:', 'camisama-customizer' ) }</span>
				{ viewZones.map( ( zone ) => (
					<button
						key={ zone.id }
						type="button"
						aria-pressed={ zone.id === activeZone?.id }
						onClick={ () => setActiveZoneId( zone.id ) }
					>
						{ zone.label }
					</button>
				) ) }
			</div>

			{ data.colors.length > 1 && (
				<fieldset className="camisama-customizer__colors">
					<legend>
						{ __( 'Color de la prenda', 'camisama-customizer' ) }
					</legend>
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

			{ selected && (
				<SelectionPanel
					element={ selected }
					zone={ zoneById( selected.zone ) }
					resolution={ data.resolution }
					onResize={ ( value ) => resize( selected, value ) }
					onRotate={ ( angle ) =>
						updateElement(
							selected.id,
							{ angle },
							{ merge: true, key: `rotate-${ selected.id }` }
						)
					}
					onCenter={ () => {
						const zone = zoneById( selected.zone );
						updateElement( selected.id, {
							cx: zone.size_cm.w / 2,
							cy: zone.size_cm.h / 2,
						} );
					} }
					onDuplicate={ () =>
						add( {
							...selected,
							id: newId(),
							cx: selected.cx + 1,
							cy: selected.cy + 1,
						} )
					}
					onLayer={ ( direction ) =>
						moveLayer( selected.id, direction )
					}
					onRemove={ () => remove( selected.id ) }
				/>
			) }

			<div className="camisama-customizer__tools">
				<div
					className="camisama-customizer__tabs"
					role="tablist"
					aria-label={ __(
						'Añadir al diseño',
						'camisama-customizer'
					) }
				>
					{ TABS.map( ( item ) => (
						<button
							key={ item.id }
							id={ `camisama-tab-${ item.id }` }
							type="button"
							role="tab"
							aria-selected={ tab === item.id }
							aria-controls={ `camisama-panel-${ item.id }` }
							onClick={ () => setTab( item.id ) }
						>
							{ item.label }
						</button>
					) ) }
				</div>
				<div
					id={ `camisama-panel-${ tab }` }
					role="tabpanel"
					aria-labelledby={ `camisama-tab-${ tab }` }
				>
					{ tab === 'designs' && (
						<DesignsPanel
							api={ data.api }
							currency={ data.currency }
							onAdd={ ( design ) =>
								add( designElement( activeZone, design ) )
							}
						/>
					) }
					{ tab === 'text' && (
						<TextPanel
							fonts={ data.fonts }
							selected={ selected }
							garment={ garment?.hex }
							onAdd={ ( style ) =>
								add( textElement( activeZone, style ) )
							}
							onChange={ ( change ) =>
								updateElement( selected.id, change, {
									merge: true,
									key: `text-${ selected.id }`,
								} )
							}
						/>
					) }
					{ tab === 'image' && (
						<UploadPanel
							api={ data.api }
							onAdd={ ( upload ) =>
								add( imageElement( activeZone, upload ) )
							}
						/>
					) }
				</div>
			</div>

			{ viewElements.length > 0 && (
				<section className="camisama-customizer__layers">
					<h3>{ __( 'En esta vista', 'camisama-customizer' ) }</h3>
					<ul>
						{ [ ...viewElements ].reverse().map( ( element ) => (
							<li key={ element.id }>
								<button
									type="button"
									aria-pressed={ element.id === selectedId }
									onClick={ () => select( element.id ) }
								>
									{ elementLabel( element ) }
									<small>
										{ zoneById( element.zone )?.label }
									</small>
								</button>
							</li>
						) ) }
					</ul>
				</section>
			) }

			{ purchaseNode ? createPortal( purchase, purchaseNode ) : purchase }

			{ purchaseNode && (
				<PurchaseBar
					target={ purchaseNode }
					total={ totalLabel }
					options={ chosenOptions }
					invalid={ Boolean( priced.error ) }
					busy={ busy }
					inCart={ inCart }
					editing={ Boolean( editKey ) }
					cartUrl={ data.cart.url }
					onBuy={ () => bridge.current.buy() }
					onReview={ () =>
						purchaseNode.scrollIntoView( { block: 'center' } )
					}
				/>
			) }
		</div>
	);
}

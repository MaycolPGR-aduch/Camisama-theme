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

import BuyStep from './BuyStep';
import FabricStage from './FabricStage';
import FlowEntry from './FlowEntry';
import PurchaseBar from './PurchaseBar';
import SheetHandle from './SheetHandle';
import SizeOptions from './SizeOptions';
import Summary from './Summary';
import DesignsPanel from './panels/DesignsPanel';
import SelectionPanel, { elementLabel } from './panels/SelectionPanel';
import TextPanel, { TEXT_COLORS } from './panels/TextPanel';
import UploadPanel from './panels/UploadPanel';
import {
	designElement,
	fitToZone,
	imageQuality,
	imageElement,
	placeFree,
	newId,
	textElement,
} from './elements';
import { isImageVisible, isReadable, mostReadable } from './contrast';
import { imageTones } from './tones';
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

	const previews = await designPreviews( data, elements, color );

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
 * Vista previa (PNG en data URL) de cada vista con algo diseñado, sobre la maqueta del color.
 *
 * @param {Object} data     Datos del personalizador.
 * @param {Array}  elements Diseño.
 * @param {string} color    Color de la prenda.
 * @return {Promise<Object>} { vista: data URL }.
 */
async function designPreviews( data, elements, color ) {
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
	return previews;
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

/** Mismo corte que el bloque de columnas: por debajo, la ficha se apila y entra el flujo móvil. */
const MOBILE = '(max-width: 781px)';

/**
 * ¿Se cumple la media query? Se actualiza al girar el móvil o cambiar el tamaño de la ventana.
 *
 * @param {string} query Media query.
 * @return {boolean} Si se cumple.
 */
function useMediaQuery( query ) {
	const [ matches, setMatches ] = useState(
		() => window.matchMedia( query ).matches
	);
	useEffect( () => {
		const list = window.matchMedia( query );
		const update = () => setMatches( list.matches );
		list.addEventListener( 'change', update );
		return () => list.removeEventListener( 'change', update );
	}, [ query ] );
	return matches;
}

// Letra por debajo de este alto (cm) puede no leerse en la prenda.
const MIN_TEXT_CM = 0.6;

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
	// Tonos de cada imagen del diseño (src → luminancias), para avisar si no se verá en la prenda.
	const [ tones, setTones ] = useState( {} );
	const [ message, setMessage ] = useState( '' );
	const [ submitError, setSubmitError ] = useState( null );
	// Flujo móvil (docs/flujo-movil.md): ficha → modo diseño a pantalla completa → talla y pago.
	// Al editar desde el carrito se entra directamente en el modo diseño.
	const mobile = useMediaQuery( MOBILE );
	// Pantalla abierta en el móvil: 'design' (modo diseño) o 'buy' (talla y pago); null, la ficha.
	const [ screen, setScreen ] = useState( data.edit ? 'design' : null );
	const designing = screen === 'design';
	const buying = screen === 'buy';
	// Paso de talla y pago: opciones elegidas a conciencia (sin la de por defecto), cantidad,
	// miniaturas y si el envío está en marcha.
	const [ choices, setChoices ] = useState( () =>
		Object.fromEntries(
			Object.entries( data.edit?.attributes ?? {} ).filter(
				( [ name ] ) => name !== 'attribute_pa_color'
			)
		)
	);
	const [ quantity, setQuantity ] = useState( data.edit?.quantity ?? 1 );
	const [ buyOptions, setBuyOptions ] = useState( [] );
	const [ missingChoice, setMissingChoice ] = useState( false );
	const [ previews, setPreviews ] = useState( null );
	const [ busy, setBusy ] = useState( false );
	// Altura de la hoja de herramientas en el modo diseño (SheetHandle): min, half o full.
	const [ sheet, setSheet ] = useState( 'half' );
	const rootRef = useRef( null );
	const [ entryNode, setEntryNode ] = useState( null );
	const openRef = useRef( null );
	const closeRef = useRef( null );
	// Hueco del resumen en el formulario de compra; sin él, el resumen va al final del editor.
	const [ purchaseNode, setPurchaseNode ] = useState( null );
	const bridge = useRef( null );
	// El puente se crea una vez; al enviar necesita el diseño y el color de ese momento.
	const latest = useRef( {} );
	latest.current = { elements, color, editKey, choices };

	useEffect( () => {
		bridge.current = connectVariationForm( data.product_id, data.cart, {
			onColor: setColor,
			onPrice: setVariationPrice,
			onQuantity: setQuantity,
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

	// Tonos de las imágenes nuevas del diseño (una vez por imagen; tones.js guarda el resultado).
	useEffect( () => {
		const pending = [
			...new Set(
				elements
					.filter(
						( element ) => element.src && ! ( element.src in tones )
					)
					.map( ( element ) => element.src )
			),
		];
		let cancelled = false;
		pending.forEach( ( src ) =>
			imageTones( src ).then( ( found ) => {
				if ( ! cancelled ) {
					setTones( ( current ) => ( {
						...current,
						[ src ]: found,
					} ) );
				}
			} )
		);
		return () => {
			cancelled = true;
		};
	}, [ elements, tones ] );

	// La talla se elige a conciencia: se quita la que WooCommerce marca por defecto (así el precio
	// es el mismo, con su "desde", en la ficha, el pie y el resumen). En escritorio va en botones
	// en la columna de compra, con sus desplegables ocultos (style.scss); si al pulsar "Añadir al
	// carrito" falta, aviso en la página en lugar del alert() del navegador.
	useEffect( () => {
		if ( ! purchaseNode ) {
			return;
		}
		const options = bridge.current.options();
		setBuyOptions( options );
		options.forEach( ( option ) =>
			bridge.current.choose(
				option.name,
				latest.current.choices[ option.name ] ?? ''
			)
		);
		if ( mobile ) {
			return;
		}
		return bridge.current.guard( ( event ) => {
			const current = latest.current.choices;
			if ( options.some( ( option ) => ! current[ option.name ] ) ) {
				event.preventDefault();
				event.stopImmediatePropagation();
				setMissingChoice( true );
			}
		} );
	}, [ mobile, purchaseNode ] );

	// Hueco del paso 1 junto al formulario: view.js lo dejó con "Preparando el editor…".
	useEffect( () => {
		let node = document.querySelector(
			'.camisama-customizer__entry[data-placeholder]'
		);
		if ( node ) {
			node.replaceChildren();
			delete node.dataset.placeholder;
		} else {
			const form = document.querySelector( '.wp-block-add-to-cart-form' );
			if ( ! form ) {
				return;
			}
			node = document.createElement( 'div' );
			node.className = 'camisama-customizer camisama-customizer__entry';
			form.before( node );
		}
		setEntryNode( node );
		return () => node.remove();
	}, [] );

	// Con una pantalla abierta (diseño o talla y pago) la página de debajo no se desplaza.
	useEffect( () => {
		document.documentElement.classList.toggle(
			'camisama-designing',
			mobile && Boolean( screen )
		);
	}, [ mobile, screen ] );
	useEffect(
		() => () =>
			document.documentElement.classList.remove( 'camisama-designing' ),
		[]
	);

	// Cada pantalla se apunta en el historial: el "atrás" del móvil vuelve a la ficha en lugar de
	// salir de la página. Pasar del diseño a la talla (o al revés) sustituye la entrada.
	useEffect( () => {
		if ( ! mobile || ! screen ) {
			return;
		}
		const current = window.history.state?.camisamaScreen;
		if ( current === screen ) {
			return;
		}
		const state = { ...window.history.state, camisamaScreen: screen };
		if ( current ) {
			window.history.replaceState( state, '' );
		} else {
			window.history.pushState( state, '' );
		}
	}, [ mobile, screen ] );
	useEffect( () => {
		const onPop = () => {
			const next = window.history.state?.camisamaScreen ?? null;
			setScreen( next );
			if ( ! next ) {
				setSelectedId( null );
				window.setTimeout( () => openRef.current?.focus() );
			}
		};
		window.addEventListener( 'popstate', onPop );
		return () => window.removeEventListener( 'popstate', onPop );
	}, [] );

	useEffect( () => {
		if ( mobile && designing ) {
			closeRef.current?.focus();
		}
	}, [ mobile, designing ] );

	// Al entrar en talla y pago: las opciones del formulario (sin la talla por defecto de
	// WooCommerce, que se elige a conciencia) y las miniaturas del diseño.
	useEffect( () => {
		if ( ! mobile || ! buying ) {
			return;
		}
		const options = bridge.current.options();
		setBuyOptions( options );
		setMissingChoice( false );
		options.forEach( ( option ) =>
			bridge.current.choose( option.name, choices[ option.name ] ?? '' )
		);

		let cancelled = false;
		setPreviews( null );
		const views = data.views.filter( ( view ) =>
			elements.some( ( element ) =>
				data.zones.some(
					( zone ) =>
						zone.view === view.id && zone.id === element.zone
				)
			)
		);
		if ( ! views.length ) {
			// Prenda lisa: su maqueta de frente.
			const front = data.views[ 0 ];
			const mockup =
				front?.mockups[ color ] ??
				Object.values( front?.mockups ?? {} )[ 0 ];
			setPreviews(
				mockup
					? [ { id: front.id, label: front.label, src: mockup.url } ]
					: []
			);
		} else {
			designPreviews( data, elements, color ).then( ( images ) => {
				if ( ! cancelled ) {
					setPreviews(
						views.map( ( view ) => ( {
							id: view.id,
							label: view.label,
							src: images[ view.id ],
						} ) )
					);
				}
			} );
		}
		return () => {
			cancelled = true;
		};
		// Al entrar en el paso, no con cada elección dentro de él.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ mobile, buying ] );

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

	// Lo nuevo (también lo duplicado) va al hueco libre más cercano: no encima de lo anterior.
	const add = ( element ) => {
		const zone = zoneById( element.zone );
		const fitted = placeFree( fitToZone( element, zone ), zone, elements );
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

	// --- Flujo móvil -------------------------------------------------------------------

	// Modo diseño del móvil con un elemento seleccionado: el lienzo se acerca a su zona y la hoja
	// muestra solo sus ajustes (y, si es un texto, el panel para cambiarlo).
	const editingElement = mobile && designing && Boolean( selected );
	useEffect( () => {
		if ( ! editingElement ) {
			return;
		}
		setSheet( 'half' );
		if ( selected.kind === 'text' ) {
			setTab( 'text' );
		}
		rootRef.current
			?.querySelector( '.camisama-customizer__sheet' )
			?.scrollTo( { top: 0 } );
		// Solo al cambiar de elemento, no con cada ajuste de ese elemento.
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ editingElement, selectedId ] );

	const openDesign = () => {
		setSheet( 'half' );
		setScreen( 'design' );
	};

	// Cierra la pantalla abierta volviendo atrás en el historial (deshace el pushState de abrir).
	const closeScreen = () => {
		setSelectedId( null );
		if ( window.history.state?.camisamaScreen ) {
			window.history.back();
		} else {
			setScreen( null );
		}
		window.setTimeout( () => openRef.current?.focus() );
	};

	const goToBuy = () => {
		setSelectedId( null );
		setScreen( 'buy' );
	};

	const chooseOption = ( name, value ) => {
		setChoices( ( current ) => ( { ...current, [ name ]: value } ) );
		setMissingChoice( false );
		bridge.current.choose( name, value );
	};

	const buyNow = () => {
		if ( buyOptions.some( ( option ) => ! choices[ option.name ] ) ) {
			setMissingChoice( true );
			return;
		}
		bridge.current.buy( choices, quantity );
	};

	// --- Teclado ----------------------------------------------------------------------

	const onKeyDown = ( event ) => {
		// Modo diseño del móvil: el tabulador no sale del diálogo.
		if ( mobile && designing && event.key === 'Tab' ) {
			const focusable = [
				...rootRef.current.querySelectorAll(
					'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
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
			return;
		}
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
		} else if ( mobile && designing && key === 'escape' ) {
			closeScreen();
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

	// Sin talla elegida, la prenda más barata; "desde" solo si la talla cambia el precio.
	const garmentPrice = variationPrice ?? data.base_price;
	const priceIsFrom = variationPrice === null && Boolean( data.price_varies );

	const summary = (
		<Summary
			garmentPrice={ garmentPrice }
			priceIsFrom={ priceIsFrom }
			quote={ priced.quote }
			quantity={ quantity }
			tiers={ data.price_tiers }
			error={ priced.error ?? submitError }
			currency={ data.currency }
		/>
	);

	// Resumen y, tras añadir, la confirmación junto al botón que se acaba de pulsar. El
	// minicarrito muestra además la miniatura del diseño.
	const purchase = (
		<>
			{ ! mobile && (
				<SizeOptions
					options={ buyOptions }
					choices={ choices }
					missing={ missingChoice }
					guide={ data.size_guide ?? [] }
					currency={ data.currency }
					onChoose={ chooseOption }
				/>
			) }
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
	// Estampados y fotos que casi no se ven (azul marino sobre negro). Aquí no hay un color mejor
	// para la imagen: se ofrece la prenda del color donde todo el diseño se ve.
	const isPicture = ( element ) =>
		element.kind === 'design' || element.kind === 'image';
	const hiddenImages = elements.filter(
		( element ) =>
			isPicture( element ) &&
			! isImageVisible( tones[ element.src ], garment?.hex )
	);
	const visibleGarment = hiddenImages.length
		? data.colors.find(
				( item ) =>
					item.hex &&
					elements.every( ( element ) =>
						isPicture( element )
							? isImageVisible( tones[ element.src ], item.hex )
							: element.kind !== 'text' ||
								isReadable( element.color, item.hex )
					)
			)
		: null;
	const readableColor = TEXT_COLORS.find(
		( item ) =>
			item.hex ===
			mostReadable(
				garment?.hex,
				TEXT_COLORS.map( ( option ) => option.hex )
			)
	);
	// Riesgos que el cliente debe ver antes de pagar (paso de talla y pago y lista de capas):
	// foto que se verá pixelada, texto que casi no se ve sobre la prenda y letra diminuta.
	const risks = elements.flatMap( ( element ) => {
		const zone = zoneById( element.zone );
		const found = [];
		if ( element.kind === 'image' ) {
			const quality = imageQuality( element, data.resolution );
			if ( quality.level === 'low' ) {
				found.push(
					sprintf(
						/* translators: 1: zona, 2: ancho máximo en cm. */
						__(
							'Se verá pixelada en %1$s. Redúcela a %2$s cm de ancho o menos, o usa una foto más grande.',
							'camisama-customizer'
						),
						zone?.label.toLowerCase() ?? '',
						(
							( element.pixel_width / data.resolution.min_dpi ) *
							2.54
						).toFixed( 1 )
					)
				);
			}
		}
		if (
			isPicture( element ) &&
			! isImageVisible( tones[ element.src ], garment?.hex )
		) {
			found.push(
				__(
					'Casi no se ve sobre el color de la prenda.',
					'camisama-customizer'
				)
			);
		}
		if ( element.kind === 'text' ) {
			if ( ! isReadable( element.color, garment?.hex ) ) {
				found.push(
					__(
						'Casi no se ve sobre el color de la prenda.',
						'camisama-customizer'
					)
				);
			}
			if ( element.font_size_cm < MIN_TEXT_CM ) {
				found.push(
					sprintf(
						/* translators: %s: alto de la letra en cm. */
						__(
							'La letra mide %s cm y puede no leerse: hazla más grande o acorta el texto.',
							'camisama-customizer'
						),
						element.font_size_cm.toFixed( 1 )
					)
				);
			}
		}
		return found.map( ( text, index ) => ( {
			key: `${ element.id }-${ index }`,
			id: element.id,
			title: elementLabel( element ),
			text,
		} ) );
	} );
	const riskyIds = new Set( risks.map( ( risk ) => risk.id ) );

	// "Corregir" desde talla y pago: vuelve al diseño con ese elemento seleccionado (y acercado).
	const fixRisk = ( id ) => {
		const element = elements.find( ( item ) => item.id === id );
		const zone = element && zoneById( element.zone );
		if ( ! zone ) {
			return;
		}
		setViewId( zone.view );
		setActiveZoneId( zone.id );
		setScreen( 'design' );
		setSelectedId( id );
	};

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

	// Escalón de precio de la zona del elemento seleccionado y el siguiente (junto a "Tamaño").
	const selectedTier = ( () => {
		const charged = selected
			? priced.quote?.zones.find(
					( item ) => item.zone === selected.zone
				)
			: null;
		const tiers = charged
			? ( zoneById( selected.zone )?.tiers ?? data.price_tiers )
			: [];
		const index = tiers.findIndex(
			( item ) => item.label === charged.tier
		);
		return index < 0
			? null
			: {
					zone: charged.label,
					longest: charged.longest_cm,
					current: tiers[ index ],
					next: tiers[ index + 1 ] ?? null,
				};
	} )();

	// Mismo total y misma regla de "desde" en la ficha, el pie del modo diseño y el resumen.
	const totalLabel =
		( priceIsFrom ? __( 'desde', 'camisama-customizer' ) + ' ' : '' ) +
		formatMoney(
			( garmentPrice + ( priced.quote?.total ?? 0 ) ) * quantity,
			data.currency
		);

	// Avisos del diseño (editar desde el carrito, borrador recuperado, texto que no se ve). En el
	// modo diseño del móvil van en una franja fija bajo el asa de la hoja: en la columna del lienzo
	// quedaban tapados por la hoja sin pista de que hubiera algo debajo.
	const hasNotices =
		Boolean( editKey ) ||
		( restoredNotice && ! history.past.length ) ||
		( hiddenTexts.length > 0 && Boolean( garment ) ) ||
		( hiddenImages.length > 0 && Boolean( garment ) );
	const notices = (
		<>
			{ editKey && (
				<div className="camisama-customizer__restored" role="status">
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
				<div className="camisama-customizer__restored" role="status">
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
				<div className="camisama-customizer__contrast" role="status">
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
			{ hiddenImages.length > 0 && garment && (
				<div className="camisama-customizer__contrast" role="status">
					<p>
						{ hiddenImages.length === 1
							? sprintf(
									/* translators: 1: estampado o foto, 2: color de la prenda. */
									__(
										'%1$s casi no se ve sobre una prenda de color %2$s.',
										'camisama-customizer'
									),
									elementLabel( hiddenImages[ 0 ] ),
									garment.name.toLowerCase()
								)
							: sprintf(
									/* translators: 1: número de estampados y fotos, 2: color de la prenda. */
									__(
										'%1$d imágenes casi no se ven sobre una prenda de color %2$s.',
										'camisama-customizer'
									),
									hiddenImages.length,
									garment.name.toLowerCase()
								) }
						{ ! visibleGarment &&
							' ' +
								__(
									'Prueba otro estampado u otro color de prenda.',
									'camisama-customizer'
								) }
					</p>
					{ visibleGarment && (
						<button
							type="button"
							onClick={ () => chooseColor( visibleGarment.slug ) }
						>
							{ sprintf(
								/* translators: %s: color de la prenda, p. ej. blanco. */
								__( 'Prenda en %s', 'camisama-customizer' ),
								visibleGarment.name.toLowerCase()
							) }
						</button>
					) }
				</div>
			) }
		</>
	);

	return (
		<div
			ref={ rootRef }
			className={ `camisama-customizer is-flow${
				purchaseNode ? ' has-purchase-bar' : ''
			}${ designing ? ` is-designing is-sheet-${ sheet }` : '' }${
				editingElement ? ' has-selection' : ''
			}${ editingElement && selected.kind === 'text' ? ' is-text-selected' : '' }` }
			role={ mobile && designing ? 'dialog' : undefined }
			aria-modal={ mobile && designing ? 'true' : undefined }
			aria-label={
				mobile && designing
					? __( 'Diseña tu prenda', 'camisama-customizer' )
					: undefined
			}
			onKeyDown={ onKeyDown }
		>
			{ /* Vistas, deshacer y lienzo: en el móvil quedan fijos arriba mientras se usan las herramientas. */ }
			<div className="camisama-customizer__canvas">
				<div className="camisama-customizer__topbar">
					{ mobile && designing && (
						<button
							type="button"
							className="camisama-customizer__close"
							ref={ closeRef }
							aria-label={ __( 'Cerrar', 'camisama-customizer' ) }
							title={ __( 'Cerrar', 'camisama-customizer' ) }
							onClick={ closeScreen }
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
					) }
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
					focusZone={
						editingElement ? zoneById( selected.zone ) : null
					}
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
				>
					{ ! elements.length && (
						<p className="camisama-customizer__invite">
							{ __(
								'Elige un estampado, escribe tu frase o sube tu foto',
								'camisama-customizer'
							) }
						</p>
					) }
				</FabricStage>
				{ ! ( mobile && designing ) && notices }
			</div>

			<p className="camisama-customizer__sr-only" aria-live="polite">
				{ message }
			</p>

			{ /* Herramientas. En el modo diseño del móvil, una hoja bajo el lienzo con tres alturas;
			en escritorio y en la ficha no cambia nada (style.scss: display: contents). */ }
			<div className="camisama-customizer__sheet">
				{ mobile && designing && (
					<SheetHandle step={ sheet } onChange={ setSheet } />
				) }
				{ mobile && designing && hasNotices && (
					<div className="camisama-customizer__notices">
						{ notices }
					</div>
				) }

				<div
					className="camisama-customizer__zone-picker"
					role="group"
					aria-label={ __(
						'Zona donde añadir',
						'camisama-customizer'
					) }
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
							{ __(
								'Color de la prenda',
								'camisama-customizer'
							) }
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
						tier={ selectedTier }
						currency={ data.currency }
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
						onDone={
							editingElement
								? () => setSelectedId( null )
								: undefined
						}
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
						<h3>
							{ __( 'En esta vista', 'camisama-customizer' ) }
						</h3>
						<ul>
							{ [ ...viewElements ]
								.reverse()
								.map( ( element ) => (
									<li key={ element.id }>
										<button
											type="button"
											aria-pressed={
												element.id === selectedId
											}
											onClick={ () =>
												select( element.id )
											}
										>
											{ elementLabel( element ) }
											<small>
												{ riskyIds.has(
													element.id
												) && (
													<span className="camisama-customizer__risk">
														{ __(
															'Revisar',
															'camisama-customizer'
														) }
													</span>
												) }
												{
													zoneById( element.zone )
														?.label
												}
											</small>
										</button>
									</li>
								) ) }
						</ul>
					</section>
				) }

				{ purchaseNode
					? createPortal( purchase, purchaseNode )
					: purchase }
			</div>

			{ purchaseNode && (
				<PurchaseBar
					visible={ mobile && designing }
					total={ totalLabel }
					inCart={ inCart }
					cartUrl={ data.cart.url }
					onNext={ goToBuy }
				/>
			) }

			{ entryNode &&
				createPortal(
					<FlowEntry
						price={
							// Sin diseño, FlowEntry ya dice "Desde … + impresión".
							elements.length
								? totalLabel
								: formatMoney( garmentPrice, data.currency )
						}
						hasDesign={ elements.length > 0 }
						onDesign={ openDesign }
						onBuy={ goToBuy }
						openRef={ openRef }
					/>,
					entryNode
				) }

			{ mobile &&
				buying &&
				createPortal(
					<BuyStep
						previews={ previews }
						risks={ risks }
						onFix={ fixRisk }
						summary={ summary }
						options={ buyOptions }
						choices={ choices }
						missing={ missingChoice }
						guide={ data.size_guide ?? [] }
						currency={ data.currency }
						quantity={ quantity }
						busy={ busy }
						error={ submitError }
						inCart={ inCart }
						editing={ Boolean( editKey ) }
						hasDesign={ elements.length > 0 }
						cartUrl={ data.cart.url }
						onChoose={ chooseOption }
						onQuantity={ setQuantity }
						onBuy={ buyNow }
						onEdit={ openDesign }
						onClose={ closeScreen }
						onStartOver={ () => {
							startOver();
							closeScreen();
						} }
					/>,
					document.body
				) }
		</div>
	);
}

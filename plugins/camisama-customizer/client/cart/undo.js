/**
 * "Deshacer" al quitar una prenda, en el carrito y en el carrito lateral.
 *
 * Los dos los pinta WooCommerce con tecnologías distintas, pero ambos usan la misma papelera y avisan
 * con el mismo evento cuando el servidor ya la quitó. Lo recupera Cart\CartUndo en PHP.
 */
import { __, sprintf } from '@wordpress/i18n';

import './style.scss';

const VISIBLE_MS = 10000;
const SYNC_EVENT = 'wc-blocks_store_sync_required';
const SYNC_DETAIL = { type: 'from_@wordpress/data' };

const { api, action } = window.camisamaCartUndo;

const bar = document.createElement( 'div' );
bar.className = 'camisama-undo';
bar.hidden = true;

const message = document.createElement( 'span' );

const undo = document.createElement( 'button' );
undo.type = 'button';
undo.textContent = __( 'Deshacer', 'camisama-customizer' );

bar.append( message, undo );

// Para los lectores de pantalla. Aparte y fijo en la página: un aviso que se mueve no se anuncia.
const live = document.createElement( 'div' );
live.className = 'camisama-undo-live';
live.setAttribute( 'role', 'status' );
document.body.append( live );

let removing = null;
let timer = null;

/**
 * Oculta el aviso, salvo que el cliente lo esté usando: entonces espera a que termine.
 */
function hideWhenIdle() {
	if ( bar.matches( ':hover, :focus-within' ) ) {
		timer = window.setTimeout( hideWhenIdle, 1000 );
		return;
	}
	bar.hidden = true;
}

/**
 * Muestra el aviso. En el carrito lateral va dentro de él: fuera quedaría detrás y sin foco.
 *
 * @param {string}       text    Mensaje.
 * @param {boolean}      canUndo Si se ofrece deshacer.
 * @param {Element|null} drawer  Carrito lateral abierto.
 */
function show( text, canUndo, drawer ) {
	( drawer ?? document.body ).append( bar );
	bar.classList.toggle( 'is-in-drawer', Boolean( drawer ) );
	message.textContent = text;
	undo.hidden = ! canUndo;
	undo.disabled = false;
	bar.hidden = false;
	live.textContent = text;

	// La papelera desaparece enseguida: sin esto, quien usa el teclado volvería al principio de la
	// página. Se espera un momento porque el carrito aún la está quitando.
	window.setTimeout( () => {
		const { activeElement, body } = undo.ownerDocument;
		if ( canUndo && activeElement === body ) {
			undo.focus();
		}
	}, 100 );

	window.clearTimeout( timer );
	timer = window.setTimeout( hideWhenIdle, VISIBLE_MS );
}

/**
 * Pide a la tienda que recupere la última prenda quitada y actualiza lo que se ve.
 *
 * @return {Promise<void>}
 */
async function restore() {
	const current = await window.fetch( `${ api }cart`, {
		credentials: 'same-origin',
	} );
	const response = await window.fetch( `${ api }cart/extensions`, {
		method: 'POST',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json',
			Nonce: current.headers.get( 'Nonce' ),
		},
		body: JSON.stringify( { namespace: action, data: {} } ),
	} );

	if ( ! response.ok ) {
		throw new Error( `Respuesta ${ response.status }` );
	}

	const cart = await response.json();
	window.wp?.data?.dispatch( 'wc/store/cart' )?.receiveCart?.( cart );
	window.dispatchEvent(
		new window.CustomEvent( SYNC_EVENT, { detail: SYNC_DETAIL } )
	);
}

undo.addEventListener( 'click', async () => {
	const drawer = bar.closest( '.wc-block-mini-cart__drawer' );
	undo.disabled = true;

	try {
		await restore();
		bar.hidden = true;
		live.textContent = __(
			'Prenda de vuelta en el carrito.',
			'camisama-customizer'
		);
		window.setTimeout( () => {
			( drawer ?? document )
				.querySelector( '.wc-block-components-product-name' )
				?.focus();
		}, 100 );
	} catch {
		show(
			__(
				'No pudimos recuperarla. Vuelve a añadirla desde su ficha.',
				'camisama-customizer'
			),
			false,
			drawer
		);
	}
} );

// La papelera desaparece al quitar la prenda: el nombre hay que leerlo al pulsarla.
document.addEventListener(
	'click',
	( event ) => {
		const trash = event.target.closest?.(
			'.wc-block-cart-item__remove-link'
		);
		if ( trash ) {
			removing = {
				name: trash
					.closest( 'tr, li' )
					?.querySelector( '.wc-block-components-product-name' )
					?.textContent.trim(),
				drawer: trash.closest( '.wc-block-mini-cart__drawer' ),
			};
		}
	},
	true
);

window.addEventListener( SYNC_EVENT, () => {
	if ( ! removing ) {
		return;
	}

	const { name, drawer } = removing;
	removing = null;
	show(
		name
			? /* translators: %s: nombre de la prenda. */
				sprintf( __( 'Quitaste %s.', 'camisama-customizer' ), name )
			: __( 'Quitaste la prenda.', 'camisama-customizer' ),
		true,
		drawer
	);
} );

/**
 * Combos encadenados de departamento, provincia y distrito en el formulario de pago.
 *
 * WooCommerce no tiene listas que dependan de otras. Estos combos se añaden junto a un campo suyo
 * (Checkout\Delivery en PHP), que queda oculto y recibe el ubigeo del distrito: así su validación,
 * su guardado y sus mensajes de error siguen funcionando igual. Sus bloques pintan y repintan por
 * su cuenta, de modo que se revisa con cada cambio del DOM. Los datos se piden al cargar el pago.
 */
import { __ } from '@wordpress/i18n';

import { watchMessages, wireContact } from './contact';
import { wireStreet } from './street';
import {
	byDelivery,
	districtsOf,
	outsideDelivery,
	partsOf,
	provincesOf,
	withRate,
} from './tree';
import './style.scss';

const FIELD = 'input[id$="-camisama-district"]';
const WRAPPER = '.wc-block-components-text-input';
const ERROR = '.wc-block-components-validation-error';

let tree = null;
const rates = window.camisamaUbigeo?.rates ?? {};

/**
 * Da un valor al campo de WooCommerce como si lo escribiera el cliente.
 *
 * @param {HTMLInputElement} input Campo oculto.
 * @param {string}           value Ubigeo, o vacío.
 */
function setCode( input, value ) {
	if ( input.value === value ) {
		return;
	}
	Object.getOwnPropertyDescriptor(
		window.HTMLInputElement.prototype,
		'value'
	).set.call( input, value );
	input.dispatchEvent( new window.Event( 'input', { bubbles: true } ) );
	input.dispatchEvent( new window.Event( 'change', { bubbles: true } ) );
	input.dispatchEvent(
		new window.FocusEvent( 'focusout', { bubbles: true } )
	);
}

/**
 * Rellena un combo; sin opciones queda desactivado, con lo que falta elegir antes.
 *
 * @param {HTMLSelectElement} select      Combo.
 * @param {Array}             groups      Grupos: [ título o vacío, [ [ código, nombre ] ] ].
 * @param {string}            placeholder Primera opción.
 * @param {string}            value       Opción elegida.
 */
function fill( select, groups, placeholder, value = '' ) {
	const option = ( item ) => new window.Option( item[ 1 ], item[ 0 ] );
	const nodes = groups
		.filter( ( [ , items ] ) => items.length )
		.flatMap( ( [ label, items ] ) => {
			if ( ! label ) {
				return items.map( option );
			}
			const optgroup = document.createElement( 'optgroup' );
			optgroup.label = label;
			optgroup.append( ...items.map( option ) );
			return [ optgroup ];
		} );
	select.replaceChildren( new window.Option( placeholder, '' ), ...nodes );
	select.value = value;
	select.disabled = ! nodes.length;
}

/**
 * En la dirección de entrega, lo que tiene envío a domicilio va primero y aparte del resto.
 *
 * @param {Array}   items    Departamentos o provincias.
 * @param {boolean} shipping Si es la dirección de entrega.
 * @return {Array} Grupos para fill().
 */
function deliveryGroups( items, shipping ) {
	const groups = byDelivery( items );
	if ( ! shipping || ! groups[ 0 ].length ) {
		return [ [ '', items ] ];
	}
	return [
		[ __( 'Con envío a domicilio', 'camisama-customizer' ), groups[ 0 ] ],
		[
			__( 'Solo recojo en el taller', 'camisama-customizer' ),
			groups[ 1 ],
		],
	];
}

/**
 * Combo con el mismo marcado que los de WooCommerce, para que tome sus estilos.
 *
 * @param {string} id    Identificador.
 * @param {string} label Etiqueta.
 * @return {{root: HTMLElement, select: HTMLSelectElement}} Partes.
 */
function comboField( id, label ) {
	const root = document.createElement( 'div' );
	root.className = 'wc-block-components-select-input';
	root.innerHTML = `<div class="wc-blocks-components-select"><div class="wc-blocks-components-select__container"><label class="wc-blocks-components-select__label"></label><select class="wc-blocks-components-select__select"></select><svg class="wc-blocks-components-select__expand" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M17.5 11.6 12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path></svg></div></div>`;
	const select = root.querySelector( 'select' );
	select.id = id;
	const labelNode = root.querySelector( 'label' );
	labelNode.htmlFor = id;
	labelNode.textContent = label;
	return { root, select };
}

/**
 * Crea los combos de una dirección junto a su campo oculto.
 *
 * @param {HTMLInputElement} input Campo oculto.
 * @return {HTMLElement} Contenedor.
 */
function createBox( input ) {
	const group = input.id.split( '-' )[ 0 ];
	const shipping = group === 'shipping';
	const box = document.createElement( 'div' );
	box.className = 'camisama-ubigeo';
	box.dataset.group = group;

	const department = comboField(
		`${ group }-camisama-department`,
		__( 'Departamento', 'camisama-customizer' )
	);
	const province = comboField(
		`${ group }-camisama-province`,
		__( 'Provincia', 'camisama-customizer' )
	);
	const district = comboField(
		`${ group }-camisama-town`,
		__( 'Distrito', 'camisama-customizer' )
	);
	box.append( department.root, province.root, district.root );

	// El aviso de los envíos solo tiene sentido en la dirección de entrega.
	let note = null;
	if ( shipping ) {
		note = document.createElement( 'p' );
		note.className = 'camisama-ubigeo__note';
		note.setAttribute( 'role', 'status' );
		box.prepend( note );
	}

	const updateNote = () => {
		if ( ! note ) {
			return;
		}
		const outside = outsideDelivery(
			department.select.value,
			province.select.value
		);
		note.classList.toggle( 'is-outside', outside );
		note.textContent = outside
			? __(
					'A esta zona todavía no hacemos envíos: por ahora solo a Lima Metropolitana y el Callao. Puedes elegir “Recojo en el taller”.',
					'camisama-customizer'
				)
			: __(
					'Por ahora solo hacemos envíos a Lima Metropolitana y el Callao.',
					'camisama-customizer'
				);
	};

	const fillProvinces = ( value = '' ) =>
		fill(
			province.select,
			deliveryGroups(
				provincesOf( tree, department.select.value ),
				shipping
			),
			department.select.value
				? __( 'Elige la provincia', 'camisama-customizer' )
				: __( 'Primero, el departamento', 'camisama-customizer' ),
			value
		);
	const fillDistricts = ( value = '' ) =>
		fill(
			district.select,
			[
				[
					'',
					districtsOf( tree, province.select.value ).map( ( item ) =>
						shipping ? withRate( item, rates ) : item
					),
				],
			],
			province.select.value
				? __( 'Elige el distrito', 'camisama-customizer' )
				: __( 'Primero, la provincia', 'camisama-customizer' ),
			value
		);

	// Pone los combos en lo que diga el campo (al cargar o al volver a editar la dirección).
	box.sync = () => {
		const code = box.input.value;
		if ( box.dataset.code === code ) {
			return;
		}
		box.dataset.code = code;
		const parts = partsOf( code );
		department.select.value = parts.department;
		fillProvinces( parts.province );
		fillDistricts( code );
		updateNote();
	};

	department.select.addEventListener( 'change', () => {
		fillProvinces();
		fillDistricts();
		updateNote();
		box.dataset.code = '';
		setCode( box.input, '' );
	} );
	province.select.addEventListener( 'change', () => {
		fillDistricts();
		updateNote();
		box.dataset.code = '';
		setCode( box.input, '' );
	} );
	district.select.addEventListener( 'change', () => {
		box.dataset.code = district.select.value;
		setCode( box.input, district.select.value );
	} );

	fill(
		department.select,
		deliveryGroups( tree, shipping ),
		__( 'Elige el departamento', 'camisama-customizer' )
	);
	fillProvinces();
	fillDistricts();
	updateNote();

	// El aviso de WooCommerce queda junto a su campo oculto, lejos de los combos: se repite aquí.
	box.error = document.createElement( 'div' );
	box.error.className =
		'wc-block-components-validation-error camisama-ubigeo__error';
	box.error.id = `${ input.id }-camisama-error`;
	box.error.setAttribute( 'role', 'alert' );
	box.error.hidden = true;
	box.append( box.error );

	box.selects = [ department.select, province.select, district.select ];
	return box;
}

/**
 * Coloca o actualiza los combos de cada dirección visible.
 */
function wire() {
	// Si WooCommerce oculta el campo (recojo en el taller), sus combos sobran.
	document.querySelectorAll( '.camisama-ubigeo' ).forEach( ( box ) => {
		if ( box.input && ! box.input.isConnected ) {
			box.remove();
		}
	} );

	document.querySelectorAll( FIELD ).forEach( ( input ) => {
		const wrapper = input.closest( WRAPPER );
		if ( ! wrapper ) {
			return;
		}

		// Al principio de la dirección: dónde se entrega decide si hay envío, antes de escribir la
		// calle. Si WooCommerce repinta el formulario y lo quita, se vuelve a poner.
		const form = wrapper.closest( '.wc-block-components-address-form' );
		let box = form?.querySelector( ':scope > .camisama-ubigeo' );
		if ( ! box ) {
			box = createBox( input );
			( form ?? wrapper.parentElement ).prepend( box );
			box.dataset.code = '-';
		}
		box.input = input;
		box.sync();

		// El campo oculto no recibe el foco: si WooCommerce lo enfoca por un error, va al combo.
		if ( ! input.dataset.camisamaWired ) {
			input.dataset.camisamaWired = 'yes';
			input.tabIndex = -1;
			input.setAttribute( 'aria-hidden', 'true' );
			input.addEventListener( 'focus', () => {
				const empty = box.selects.find(
					( select ) => ! select.disabled && ! select.value
				);
				( empty ?? box.selects[ 0 ] ).focus();
			} );
		}

		// Con error, el mensaje sale bajo los combos y el que falta queda marcado y ligado a él.
		const error = wrapper.querySelector( ERROR );
		const message = error?.textContent.trim() ?? '';
		if ( box.error.textContent !== message ) {
			// Igual que el de WooCommerce: su icono y el texto.
			const line = document.createElement( 'p' );
			const icon = error?.querySelector( 'svg' );
			if ( icon ) {
				line.append( icon.cloneNode( true ) );
			}
			const text = document.createElement( 'span' );
			text.textContent = message;
			line.append( text );
			box.error.replaceChildren( ...( message ? [ line ] : [] ) );
		}
		box.error.hidden = ! message;
		box.selects.forEach( ( select ) => {
			const missing =
				Boolean( message ) && ! select.disabled && ! select.value;
			select.setAttribute( 'aria-invalid', String( missing ) );
			if ( missing ) {
				select.setAttribute( 'aria-describedby', box.error.id );
			} else {
				select.removeAttribute( 'aria-describedby' );
			}
		} );
	} );
}

// Correo, teléfono y calle: no esperan a la lista de distritos.
function wireFields() {
	wireContact();
	wireStreet();
}
watchMessages();
wireFields();
window.wp?.data?.subscribe( wireStreet );
new window.MutationObserver( wireFields ).observe( document.body, {
	subtree: true,
	childList: true,
} );

window
	.fetch( window.camisamaUbigeo.url )
	.then( ( response ) => response.json() )
	.then( ( data ) => {
		tree = data;
		wire();
		new window.MutationObserver( wire ).observe( document.body, {
			subtree: true,
			childList: true,
		} );
	} )
	.catch( () => {
		// Sin la lista no se puede elegir el distrito, y el pedido no pasaría: mejor decirlo.
		document.querySelectorAll( FIELD ).forEach( ( input ) => {
			const note = document.createElement( 'p' );
			note.className = 'camisama-ubigeo__note is-outside';
			note.setAttribute( 'role', 'alert' );
			note.textContent = __(
				'No pudimos cargar la lista de distritos. Recarga la página para continuar.',
				'camisama-customizer'
			);
			input.closest( WRAPPER )?.before( note );
		} );
	} );

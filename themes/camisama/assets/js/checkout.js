/**
 * Pago: liga cada error a su campo (aria-describedby), que los bloques de WooCommerce no hacen, para
 * que un lector de pantalla lo lea al llegar al campo, y da flechas a "Envío / Recojo".
 *
 * Los bloques pintan y repintan por su cuenta: se revisa con cada cambio del DOM.
 */
( function () {
	const FIELD = '.wc-block-components-text-input, .wc-block-components-select-input, .wc-block-components-country-input';

	function linkErrors() {
		document.querySelectorAll( 'input[aria-invalid="true"], select[aria-invalid="true"]' ).forEach( ( field ) => {
			const error = field.closest( FIELD )?.querySelector( '.wc-block-components-validation-error' );
			if ( ! error ) {
				return;
			}
			if ( ! error.id ) {
				error.id = `${ field.id }-error`;
			}
			if ( field.getAttribute( 'aria-describedby' ) !== error.id ) {
				field.setAttribute( 'aria-describedby', error.id );
			}
		} );
	}

	// "Envío / Recojo" es un grupo de radios (role="radio"), pero WooCommerce deja las dos fichas en el
	// orden de tabulación y sin flechas. Como en cualquier grupo de radios: Tab entra en la elegida y
	// las flechas cambian de opción.
	const OPTION = '.wc-block-checkout__shipping-method-option[role="radio"]';

	function rovingTabs() {
		document.querySelectorAll( OPTION ).forEach( ( option ) => {
			const tab = 'true' === option.getAttribute( 'aria-checked' ) ? '0' : '-1';
			if ( option.getAttribute( 'tabindex' ) !== tab ) {
				option.setAttribute( 'tabindex', tab );
			}
		} );
	}

	document.addEventListener( 'keydown', ( event ) => {
		const option = event.target.closest?.( OPTION );
		const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[ event.key ];
		if ( ! option || ! step ) {
			return;
		}
		const options = [ ...option.parentElement.querySelectorAll( ':scope > [role="radio"]' ) ];
		const next = options[ ( options.indexOf( option ) + step + options.length ) % options.length ];
		event.preventDefault();
		next.click();
		next.focus();
	} );

	new window.MutationObserver( () => {
		linkErrors();
		rovingTabs();
	} ).observe( document.body, {
		subtree: true,
		childList: true,
		attributes: true,
		attributeFilter: [ 'aria-invalid', 'aria-checked' ],
	} );
	rovingTabs();

	// Al elegir "Recojo", WooCommerce desmarca "Usar la misma dirección para facturación" (sin envío
	// hace falta la de facturación) y al volver a "Envío" no la restaura: quedaba un segundo
	// formulario entero. Se deja como estaba, pulsando la propia casilla para no usar su API interna.
	const data = window.wp?.data;
	if ( ! data ) {
		return;
	}

	function restoreSameAddress( tries ) {
		const box = document.querySelector( '.wc-block-checkout__use-address-for-billing input' );
		if ( box ) {
			if ( ! box.checked ) {
				box.click();
			}
		} else if ( tries > 0 ) {
			window.requestAnimationFrame( () => restoreSameAddress( tries - 1 ) );
		}
	}

	let collecting = null;
	let sameBefore = false;
	data.subscribe( () => {
		const checkout = data.select( 'wc/store/checkout' );
		if ( ! checkout?.prefersCollection ) {
			return;
		}
		const now = checkout.prefersCollection();
		if ( collecting !== null && now !== collecting ) {
			if ( now ) {
				sameBefore = checkout.getUseShippingAsBilling();
			} else if ( sameBefore ) {
				restoreSameAddress( 30 );
			}
		}
		collecting = now;
	} );
}() );

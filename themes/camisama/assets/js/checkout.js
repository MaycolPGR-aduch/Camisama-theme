/**
 * Pago: liga cada error a su campo (aria-describedby), que los bloques de WooCommerce no hacen, para
 * que un lector de pantalla lo lea al llegar al campo.
 *
 * Los bloques pintan y repintan por su cuenta: se revisa con cada cambio del DOM.
 */
( function () {
	const FIELD = '.wc-block-components-text-input, .wc-blocks-components-select, .wc-block-components-country-input';

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

	new window.MutationObserver( linkErrors ).observe( document.body, {
		subtree: true,
		childList: true,
		attributes: true,
		attributeFilter: [ 'aria-invalid' ],
	} );
}() );

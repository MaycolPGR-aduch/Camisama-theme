import { setDesignFields } from './wc-bridge';

describe( 'envíos sucesivos desde el personalizador', () => {
	it( 'no cobra ni envía la impresión anterior al comprar después una prenda lisa', () => {
		const form = document.createElement( 'form' );
		form.innerHTML =
			'<input name="quantity" value="2"><input name="attribute_pa_color" value="negro">';
		setDesignFields( form, {
			camisama_design: 'diseño anterior',
			camisama_previews: 'miniatura anterior',
		} );
		setDesignFields( form, {} );
		expect( Object.fromEntries( new FormData( form ) ) ).toEqual( {
			quantity: '2',
			attribute_pa_color: 'negro',
		} );
	} );

	it( 'permite quitar la impresión al editar una línea y luego empezar otra compra', () => {
		const form = document.createElement( 'form' );
		setDesignFields( form, {
			camisama_design: 'anterior',
			camisama_previews: 'anterior',
			camisama_replace: 'linea-a',
		} );
		setDesignFields( form, { camisama_replace: 'linea-a' } );
		expect( Object.fromEntries( new FormData( form ) ) ).toEqual( {
			camisama_replace: 'linea-a',
		} );
		setDesignFields( form, {
			camisama_design: 'nuevo',
			camisama_previews: 'nueva',
		} );
		expect( Object.fromEntries( new FormData( form ) ) ).toEqual( {
			camisama_design: 'nuevo',
			camisama_previews: 'nueva',
		} );
	} );
} );

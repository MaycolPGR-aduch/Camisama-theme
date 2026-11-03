import { splitLines } from './TextPanel';

describe( 'splitLines', () => {
	it( 'parte por el espacio más cercano a la mitad', () => {
		expect( splitLines( 'Feliz cumple mamá querida' ) ).toBe(
			'Feliz cumple\nmamá querida'
		);
	} );

	it( 'parte la línea más larga si ya hay varias', () => {
		expect( splitLines( 'Hola\ndesde Arequipa hasta Lima' ) ).toBe(
			'Hola\ndesde Arequipa\nhasta Lima'
		);
	} );

	it( 'sin espacios no hay dónde partir', () => {
		expect( splitLines( 'Supercalifragilístico' ) ).toBeNull();
	} );
} );

import { needsStreet } from './street';

describe( 'calle de la dirección', () => {
	it( 'hace falta con envío, y al recoger no', () => {
		expect( needsStreet( 'shipping', false ) ).toBe( true );
		expect( needsStreet( 'billing', false ) ).toBe( true );
		expect( needsStreet( 'billing', true ) ).toBe( false );
	} );
} );

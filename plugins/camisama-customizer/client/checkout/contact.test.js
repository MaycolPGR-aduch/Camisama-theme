import {
	CONTACT_FIELDS,
	EMAIL_PATTERN,
	PHONE_PATTERN,
	matches,
	messageFor,
} from './contact';

describe( 'teléfono del pago', () => {
	it.each( [
		'987654321',
		'987 654 321',
		'+51 987 654 321',
		'51987654321',
		'+51-987-654-321',
		'01 445 1234',
		'(01) 445-1234',
		'4451234',
		'044 123456',
		'+34 612 345 678',
		'+1 (305) 555-0100',
	] )( 'acepta %s', ( value ) => {
		expect( matches( PHONE_PATTERN, value ) ).toBe( true );
	} );

	it.each( [
		'abc',
		'98765432',
		'9876543210',
		'+51 98765432',
		'987 654 32a',
		'12345',
		'+34 612',
	] )( 'no acepta %s', ( value ) => {
		expect( matches( PHONE_PATTERN, value ) ).toBe( false );
	} );
} );

describe( 'correo del pago', () => {
	it.each( [ 'maria@gmail.com', 'ana.p+1@uni.edu.pe', 'josé@ñandú.pe' ] )(
		'acepta %s',
		( value ) => {
			expect( matches( EMAIL_PATTERN, value ) ).toBe( true );
		}
	);

	it.each( [
		'maria@gmail',
		'maria@',
		'maria gmail.com',
		'maria@gmail.c',
		'maria@gmail..com',
		'maria@gmail.com.',
	] )( 'no acepta %s', ( value ) => {
		expect( matches( EMAIL_PATTERN, value ) ).toBe( false );
	} );
} );

describe( 'mensaje', () => {
	const phone = CONTACT_FIELDS.find( ( { id } ) => id === 'shipping-phone' );

	it( 'dice cómo corregirlo, y calla si está vacío o bien', () => {
		expect( messageFor( phone, '98765432' ) ).toContain( '9 dígitos' );
		expect( messageFor( phone, '  ' ) ).toBeNull();
		expect( messageFor( phone, '987654321' ) ).toBeNull();
	} );

	it( 'los patrones valen con la regla nueva del navegador (bandera v)', () => {
		CONTACT_FIELDS.forEach( ( { pattern } ) => {
			expect( () => new RegExp( pattern, 'v' ) ).not.toThrow();
		} );
	} );
} );

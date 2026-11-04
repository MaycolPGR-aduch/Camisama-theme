import { districtsOf, outsideDelivery, partsOf, provincesOf } from './tree';

const TREE = [
	[
		'04',
		'Arequipa',
		[ [ '0401', 'Arequipa', [ [ '040101', 'Arequipa' ] ] ] ],
	],
	[
		'15',
		'Lima',
		[
			[ '1501', 'Lima', [ [ '150122', 'Miraflores' ] ] ],
			[ '1505', 'Cañete', [ [ '150501', 'San Vicente de Cañete' ] ] ],
		],
	],
];

describe( 'combos del ubigeo', () => {
	it( 'cada nivel carga los hijos del anterior', () => {
		expect(
			provincesOf( TREE, '15' ).map( ( item ) => item[ 1 ] )
		).toEqual( [ 'Lima', 'Cañete' ] );
		expect( districtsOf( TREE, '1501' ) ).toEqual( [
			[ '150122', 'Miraflores' ],
		] );
	} );

	it( 'sin elección no hay hijos', () => {
		expect( provincesOf( TREE, '' ) ).toEqual( [] );
		expect( districtsOf( TREE, '' ) ).toEqual( [] );
	} );

	it( 'un distrito guardado dice su departamento y provincia', () => {
		expect( partsOf( '150122' ) ).toEqual( {
			department: '15',
			province: '1501',
		} );
		expect( partsOf( '' ) ).toEqual( { department: '', province: '' } );
		expect( partsOf( undefined ) ).toEqual( {
			department: '',
			province: '',
		} );
	} );
} );

describe( 'zona de envíos', () => {
	it( 'sin elegir nada no avisa', () => {
		expect( outsideDelivery( '', '' ) ).toBe( false );
	} );

	it( 'Lima y Callao pueden ser zona hasta saber la provincia', () => {
		expect( outsideDelivery( '15', '' ) ).toBe( false );
		expect( outsideDelivery( '07', '' ) ).toBe( false );
		expect( outsideDelivery( '15', '1501' ) ).toBe( false );
		expect( outsideDelivery( '07', '0701' ) ).toBe( false );
	} );

	it( 'el resto del Perú y de Lima quedan fuera', () => {
		expect( outsideDelivery( '04', '' ) ).toBe( true );
		expect( outsideDelivery( '15', '1505' ) ).toBe( true );
	} );
} );

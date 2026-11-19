import {
	byDelivery,
	districtsOf,
	outsideDelivery,
	partsOf,
	provincesOf,
	withRate,
} from './tree';

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

	it( 'Lima va antes que el Callao', () => {
		const callao = [ '07', 'Callao', [ [ '0701', 'Callao', [] ] ] ];
		expect(
			byDelivery( [ callao, ...TREE ] )[ 0 ].map( ( item ) => item[ 1 ] )
		).toEqual( [ 'Lima', 'Callao' ] );
	} );

	it( 'lo que tiene envío va primero, y el resto aparte', () => {
		expect( byDelivery( TREE ) ).toEqual( [
			[ TREE[ 1 ] ],
			[ TREE[ 0 ] ],
		] );
		expect( byDelivery( provincesOf( TREE, '15' ) ) ).toEqual( [
			[ provincesOf( TREE, '15' )[ 0 ] ],
			[ provincesOf( TREE, '15' )[ 1 ] ],
		] );
		// Donde no hay envío no se separa: todo es "solo recojo".
		expect( byDelivery( provincesOf( TREE, '04' ) ) ).toEqual( [
			[],
			provincesOf( TREE, '04' ),
		] );
	} );

	it( 'cada distrito con envío lleva su tarifa', () => {
		const rates = { 150122: 1000 };
		expect( withRate( [ '150122', 'Miraflores' ], rates ) ).toEqual( [
			'150122',
			'Miraflores · envío S/ 10.00',
		] );
		expect(
			withRate( [ '150501', 'San Vicente de Cañete' ], rates )
		).toEqual( [ '150501', 'San Vicente de Cañete' ] );
	} );
} );

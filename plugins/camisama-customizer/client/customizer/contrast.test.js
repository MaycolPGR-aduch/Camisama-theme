import {
	contrastRatio,
	hiddenShare,
	isImageVisible,
	isReadable,
	MIN_CONTRAST,
	mostReadable,
	rgbLuminance,
} from './contrast';

const TEXT = [ '#16161A', '#FFFFFF', '#D3261C', '#26357F', '#F5B700' ];

describe( 'contrastRatio', () => {
	it( 'va de 1 (iguales) a 21 (negro sobre blanco), en cualquier orden', () => {
		expect( contrastRatio( '#000000', '#FFFFFF' ) ).toBeCloseTo( 21 );
		expect( contrastRatio( '#FFFFFF', '#000000' ) ).toBeCloseTo( 21 );
		expect( contrastRatio( '#D3261C', '#D3261C' ) ).toBeCloseTo( 1 );
	} );

	it( 'acepta #RGB y no distingue mayúsculas', () => {
		expect( contrastRatio( '#fff', '#FFFFFF' ) ).toBeCloseTo( 1 );
	} );

	it( 'devuelve null si un color no es válido', () => {
		expect( contrastRatio( 'rojo', '#FFFFFF' ) ).toBeNull();
		expect( contrastRatio( '#FFFFFF', undefined ) ).toBeNull();
	} );
} );

describe( 'isReadable', () => {
	it( 'detecta el texto que se pierde sobre la prenda', () => {
		expect( isReadable( '#16161A', '#16161A' ) ).toBe( false );
		expect( isReadable( '#F5B700', '#FFFFFF' ) ).toBe( false );
		expect( isReadable( '#26357F', '#1C2541' ) ).toBe( false );
	} );

	it( 'da por buenos los textos que se ven', () => {
		expect( isReadable( '#FFFFFF', '#16161A' ) ).toBe( true );
		expect( isReadable( '#D3261C', '#FFFFFF' ) ).toBe( true );
	} );

	it( 'no avisa si no se conoce el color de la prenda', () => {
		expect( isReadable( '#16161A', undefined ) ).toBe( true );
	} );

	it( 'usa el umbral de texto grande de las WCAG', () => {
		expect( MIN_CONTRAST ).toBe( 3 );
	} );
} );

describe( 'mostReadable', () => {
	it( 'elige negro sobre prendas claras y blanco sobre oscuras', () => {
		expect( mostReadable( '#FFFFFF', TEXT ) ).toBe( '#16161A' );
		expect( mostReadable( '#16161A', TEXT ) ).toBe( '#FFFFFF' );
		expect( mostReadable( '#1C2541', TEXT ) ).toBe( '#FFFFFF' );
	} );

	it( 'devuelve el primero si no se conoce la prenda', () => {
		expect( mostReadable( undefined, TEXT ) ).toBe( '#16161A' );
	} );
} );

describe( 'isImageVisible', () => {
	const tonesOf = ( ...colors ) =>
		colors.map( ( [ r, g, b ] ) => rgbLuminance( r, g, b ) );
	const NAVY = [ 31, 42, 68 ];
	const WHITE = [ 255, 255, 255 ];

	it( 'un estampado oscuro sobre prenda oscura no se ve', () => {
		const navy = tonesOf( NAVY, NAVY, NAVY, NAVY );
		expect( hiddenShare( navy, '#16161A' ) ).toBe( 1 );
		expect( isImageVisible( navy, '#16161A' ) ).toBe( false );
		expect( isImageVisible( navy, '#FFFFFF' ) ).toBe( true );
	} );

	it( 'basta con que la mayor parte se distinga', () => {
		const wave = tonesOf( NAVY, WHITE, WHITE, WHITE );
		expect( isImageVisible( wave, '#16161A' ) ).toBe( true );
	} );

	it( 'sin muestra o sin color de prenda, se da por buena', () => {
		expect( hiddenShare( null, '#16161A' ) ).toBeNull();
		expect( isImageVisible( [], '#16161A' ) ).toBe( true );
		expect( isImageVisible( tonesOf( NAVY ), undefined ) ).toBe( true );
	} );
} );

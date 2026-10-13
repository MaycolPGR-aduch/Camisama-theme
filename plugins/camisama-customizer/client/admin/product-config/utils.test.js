import {
	fitRect,
	heightFor,
	normalizeConfig,
	slugify,
	tiersWithin,
	uniqueId,
} from './utils';

const tiers = [
	{ label: 'Pequeño', max_cm: 10, amount: 800 },
	{ label: 'Mediano', max_cm: 20, amount: 1400 },
	{ label: 'Grande', max_cm: 30, amount: 2000 },
	{ label: 'Máximo', max_cm: 40, amount: 2500 },
];

describe( 'slugify', () => {
	it( 'quita tildes, mayúsculas y espacios', () => {
		expect( slugify( 'Manga Izquierda' ) ).toBe( 'manga-izquierda' );
		expect( slugify( '  Ñandú & Co. ' ) ).toBe( 'nandu-co' );
	} );

	it( 'usa el valor por defecto si no queda nada', () => {
		expect( slugify( '¡¿?!' ) ).toBe( 'zona' );
		expect( slugify( '', 'vista' ) ).toBe( 'vista' );
	} );
} );

describe( 'uniqueId', () => {
	it( 'añade un sufijo si ya existe', () => {
		expect( uniqueId( 'frente', [] ) ).toBe( 'frente' );
		expect( uniqueId( 'frente', [ 'frente', 'frente-2' ] ) ).toBe(
			'frente-3'
		);
	} );
} );

describe( 'heightFor / fitRect', () => {
	it( 'conserva la proporción física en una imagen cuadrada', () => {
		expect( heightFor( 0.3, { w: 30, h: 40 }, 1 ) ).toBeCloseTo( 0.4 );
	} );

	it( 'tiene en cuenta imágenes apaisadas', () => {
		// Imagen el doble de ancha que alta: el mismo ancho en fracción ocupa el doble de alto.
		expect( heightFor( 0.2, { w: 10, h: 10 }, 2 ) ).toBeCloseTo( 0.4 );
	} );

	it( 'recoloca la zona centrada y dentro de la imagen', () => {
		const rect = fitRect(
			{ x: 0.8, y: 0.1, w: 0.2, h: 0.2 },
			{ w: 10, h: 20 },
			1
		);

		expect( rect.h ).toBeCloseTo( 0.4 );
		expect( rect.x + rect.w ).toBeLessThanOrEqual( 1 );
		expect( rect.y ).toBeGreaterThanOrEqual( 0 );
	} );

	it( 'encoge la zona si no cabe de alto', () => {
		const rect = fitRect(
			{ x: 0.1, y: 0.1, w: 0.8, h: 0.8 },
			{ w: 10, h: 40 },
			1
		);

		expect( rect.h ).toBe( 1 );
		expect( rect.w ).toBeCloseTo( 0.25 );
	} );
} );

describe( 'tiersWithin', () => {
	it( 'una manga de 9 cm solo alcanza el escalón pequeño', () => {
		expect( tiersWithin( tiers, 9 ).map( ( t ) => t.label ) ).toEqual( [
			'Pequeño',
		] );
	} );

	it( 'el frente de 40 cm alcanza todos', () => {
		expect( tiersWithin( tiers, 40 ) ).toHaveLength( 4 );
	} );
} );

describe( 'normalizeConfig', () => {
	it( 'convierte las maquetas vacías de PHP en objetos', () => {
		const config = normalizeConfig( {
			enabled: 1,
			views: [ { id: 'front', label: 'Frente', mockups: [] } ],
		} );

		expect( config.enabled ).toBe( true );
		expect( config.views[ 0 ].mockups ).toEqual( {} );
		expect( config.zones ).toEqual( [] );
	} );
} );

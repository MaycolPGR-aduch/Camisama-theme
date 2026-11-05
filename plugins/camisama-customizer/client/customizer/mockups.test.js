import { mockupFor, requireColorMockups } from './mockups';

const image = { url: '/black.webp', width: 1254, height: 1254 };
const view = { id: 'front', mockups: { negro: image } };

describe( 'maquetas por color', () => {
	it( 'usa exclusivamente la imagen del color solicitado', () => {
		expect( mockupFor( view, 'negro' ) ).toBe( image );
		expect( mockupFor( view, 'blanco' ) ).toBeNull();
		expect( mockupFor( undefined, 'negro' ) ).toBeNull();
	} );

	it( 'mantiene la maqueta default de productos sin atributo color', () => {
		expect( mockupFor( { mockups: { default: image } }, 'default' ) ).toBe(
			image
		);
	} );

	it.each( [ 0, -1, NaN, Infinity, '1254' ] )(
		'rechaza dimensiones inválidas: %s',
		( width ) => {
			expect(
				mockupFor(
					{ mockups: { negro: { ...image, width } } },
					'negro'
				)
			).toBeNull();
		}
	);

	it( 'exige el color en todas las vistas antes de comprar', () => {
		expect( () =>
			requireColorMockups( { views: [ view ] }, 'negro' )
		).not.toThrow();
		expect( () =>
			requireColorMockups(
				{ views: [ view, { id: 'back', mockups: {} } ] },
				'negro'
			)
		).toThrow();
		expect( () => requireColorMockups( { views: [] }, 'negro' ) ).toThrow();
	} );
} );

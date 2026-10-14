import fixture from '../../tests/fixtures/pricing-cases.json';
import { elementBounds, quote } from './pricing';

const run = ( testCase ) =>
	quote( fixture.zones, fixture.tiers, testCase.elements, fixture.premiums );

const valid = fixture.cases.filter( ( testCase ) => ! testCase.error );
const invalid = fixture.cases.filter( ( testCase ) => testCase.error );

describe( 'quote (casos compartidos con PHP)', () => {
	it.each( valid.map( ( testCase ) => [ testCase.name, testCase ] ) )(
		'%s',
		( name, testCase ) => {
			const result = run( testCase );

			expect(
				result.zones.map( ( { zone, tier, amount } ) => ( {
					zone,
					tier,
					amount,
				} ) )
			).toEqual( testCase.expected.zones );
			expect( result.premiums ).toEqual(
				testCase.expected.premiums ?? []
			);
			expect( result.total ).toBe( testCase.expected.total );
		}
	);

	it.each( invalid.map( ( testCase ) => [ testCase.name, testCase ] ) )(
		'%s',
		( name, testCase ) => {
			expect( () => run( testCase ) ).toThrow();
		}
	);
} );

describe( 'elementBounds', () => {
	it( 'un elemento girado 90° intercambia ancho y alto', () => {
		const bounds = elementBounds( {
			cx: 10,
			cy: 10,
			width_cm: 4,
			height_cm: 2,
			angle: 90,
		} );

		expect( bounds.left ).toBeCloseTo( 9 );
		expect( bounds.top ).toBeCloseTo( 8 );
		expect( bounds.right ).toBeCloseTo( 11 );
		expect( bounds.bottom ).toBeCloseTo( 12 );
	} );
} );

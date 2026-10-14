import {
	designElement,
	fitToZone,
	imageElement,
	imageQuality,
	initialSize,
} from './elements';
import {
	fitsInside,
	fromCanvas,
	pushInside,
	toCanvas,
	zoneBox,
} from './geometry';
import { historyReducer, initialHistory } from './history';

const front = {
	id: 'front',
	rect: { x: 0.38, y: 0.33, w: 0.24, h: 0.32 },
	size_cm: { w: 30, h: 40 },
};

describe( 'geometría', () => {
	const box = zoneBox( front, 500, 500 );

	it( 'calcula la zona en píxeles y su escala', () => {
		expect( box.x ).toBeCloseTo( 190 );
		expect( box.w ).toBeCloseTo( 120 );
		expect( box.ppc ).toBeCloseTo( 4 );
	} );

	it( 'ida y vuelta entre cm y píxeles', () => {
		const element = { cx: 10, cy: 20, width_cm: 8, angle: 30 };
		const t = toCanvas( element, box, 200 );

		expect( t.scale ).toBeCloseTo( 0.16 );
		const back = fromCanvas(
			{ x: t.left, y: t.top },
			{ width: 200 * t.scale, height: 100 * t.scale, angle: 30 },
			box
		);
		expect( back ).toEqual( {
			cx: 10,
			cy: 20,
			width_cm: 8,
			height_cm: 4,
			angle: 30,
		} );
	} );

	it( 'normaliza el ángulo entre 0 y 360', () => {
		expect(
			fromCanvas(
				{ x: 0, y: 0 },
				{ width: 4, height: 4, angle: -90 },
				box
			).angle
		).toBe( 270 );
	} );

	it( 'empuja dentro de la zona lo que se sale', () => {
		const zone = { x: 100, y: 100, w: 200, h: 200 };

		expect(
			pushInside( { left: 80, top: 150, width: 50, height: 50 }, zone )
		).toEqual( { dx: 20, dy: 0 } );
		expect(
			pushInside( { left: 280, top: 290, width: 50, height: 50 }, zone )
		).toEqual( { dx: -30, dy: -40 } );
		expect(
			fitsInside( { left: 100, top: 100, width: 200, height: 200 }, zone )
		).toBe( true );
		expect(
			fitsInside( { left: 100, top: 100, width: 210, height: 200 }, zone )
		).toBe( false );
	} );
} );

describe( 'elementos', () => {
	it( 'el tamaño inicial respeta la proporción y cabe en la zona', () => {
		expect( initialSize( front, 2 ) ).toEqual( {
			width_cm: 18,
			height_cm: 9,
		} );
		expect( initialSize( front, 0.5 ) ).toEqual( {
			width_cm: 12,
			height_cm: 24,
		} );
	} );

	it( 'un estampado nuevo queda centrado con su recargo', () => {
		const element = designElement( front, {
			id: 7,
			title: 'Llama',
			premium: 500,
			file: {
				url: 'x.svg',
				mime: 'image/svg+xml',
				width: 200,
				height: 220,
			},
		} );

		expect( element ).toMatchObject( {
			zone: 'front',
			kind: 'design',
			design_id: 7,
			premium: 500,
			cx: 15,
			cy: 20,
		} );
	} );

	it( 'avisa cuando una foto se va a ver pixelada', () => {
		const resolution = { target_dpi: 300, min_dpi: 150 };
		const photo = imageElement( front, {
			id: 'abc',
			url: 'x.jpg',
			width: 1200,
			height: 900,
		} );

		expect(
			imageQuality( { ...photo, width_cm: 10 }, resolution )
		).toEqual( {
			dpi: 305,
			level: 'optimal',
		} );
		expect(
			imageQuality( { ...photo, width_cm: 18 }, resolution ).level
		).toBe( 'acceptable' );
		expect(
			imageQuality( { ...photo, width_cm: 30 }, resolution ).level
		).toBe( 'low' );
	} );
} );

describe( 'fitToZone', () => {
	const sleeve = { id: 'sleeve', size_cm: { w: 9, h: 9 } };

	it( 'no toca lo que ya cabe', () => {
		const element = {
			cx: 4.5,
			cy: 4.5,
			width_cm: 4,
			height_cm: 4,
			angle: 0,
		};
		expect( fitToZone( element, sleeve ) ).toBe( element );
	} );

	it( 'encoge lo que no cabe, también el tamaño de letra', () => {
		const result = fitToZone(
			{
				cx: 4.5,
				cy: 4.5,
				width_cm: 18,
				height_cm: 3,
				font_size_cm: 2.5,
				angle: 0,
			},
			sleeve
		);
		expect( result.width_cm ).toBe( 9 );
		expect( result.height_cm ).toBe( 1.5 );
		expect( result.font_size_cm ).toBe( 1.25 );
	} );

	it( 'empuja hacia dentro lo que se sale', () => {
		const result = fitToZone(
			{ cx: 8, cy: 1, width_cm: 4, height_cm: 4, angle: 0 },
			sleeve
		);
		expect( result.cx ).toBe( 7 );
		expect( result.cy ).toBe( 2 );
	} );
} );

describe( 'historial', () => {
	it( 'un parche cambia el estado sin crear un paso de deshacer', () => {
		let state = historyReducer( initialHistory, {
			type: 'set',
			update: () => [ 'a' ],
		} );
		state = historyReducer( state, {
			type: 'patch',
			update: () => [ 'a2' ],
		} );
		expect( state.present ).toEqual( [ 'a2' ] );
		expect( state.past ).toEqual( [ [] ] );
	} );

	const add = ( item, extra = {} ) => ( {
		type: 'set',
		update: ( list ) => [ ...list, item ],
		...extra,
	} );

	it( 'deshace y rehace', () => {
		let state = historyReducer( initialHistory, add( 'a' ) );
		state = historyReducer( state, add( 'b' ) );
		state = historyReducer( state, { type: 'undo' } );
		expect( state.present ).toEqual( [ 'a' ] );
		state = historyReducer( state, { type: 'redo' } );
		expect( state.present ).toEqual( [ 'a', 'b' ] );
	} );

	it( 'funde los cambios continuos del mismo elemento', () => {
		let state = historyReducer( initialHistory, add( 'a' ) );
		state = historyReducer( state, add( 'b', { merge: true, key: 'x' } ) );
		state = historyReducer( state, add( 'c', { merge: true, key: 'x' } ) );
		state = historyReducer( state, { type: 'undo' } );
		expect( state.present ).toEqual( [ 'a' ] );
	} );

	it( 'un cambio nuevo borra lo que se podía rehacer', () => {
		let state = historyReducer( initialHistory, add( 'a' ) );
		state = historyReducer( state, { type: 'undo' } );
		state = historyReducer( state, add( 'b' ) );
		expect( state.future ).toEqual( [] );
	} );

	it( 'ignora cambios que no cambian nada', () => {
		const state = historyReducer( initialHistory, {
			type: 'set',
			update: ( list ) => list,
		} );
		expect( state ).toBe( initialHistory );
	} );
} );

import { DRAFT_TTL, loadDraft, saveDraft } from './draft';

const DATA = {
	zones: [ { id: 'front' }, { id: 'back' } ],
	views: [ { id: 'front' }, { id: 'back' } ],
	colors: [ { slug: 'blanco' }, { slug: 'negro' } ],
};

const TEXT = { id: 'a', kind: 'text', zone: 'front', text: 'Hola' };

function memoryStorage() {
	const items = new Map();
	return {
		getItem: ( key ) => ( items.has( key ) ? items.get( key ) : null ),
		setItem: ( key, value ) => items.set( key, String( value ) ),
		removeItem: ( key ) => items.delete( key ),
		items,
	};
}

describe( 'borrador del diseño', () => {
	it( 'guarda y recupera el diseño, el color, la vista y la zona', () => {
		const storage = memoryStorage();
		saveDraft(
			7,
			{
				elements: [ TEXT ],
				color: 'negro',
				viewId: 'back',
				zoneId: 'back',
			},
			storage,
			1000
		);

		expect( loadDraft( 7, DATA, storage, 2000 ) ).toEqual( {
			elements: [ TEXT ],
			color: 'negro',
			viewId: 'back',
			zoneId: 'back',
		} );
	} );

	it( 'es por producto', () => {
		const storage = memoryStorage();
		saveDraft( 7, { elements: [ TEXT ] }, storage, 1000 );

		expect( loadDraft( 8, DATA, storage, 2000 ) ).toBeNull();
	} );

	it( 'caduca a los 7 días', () => {
		const storage = memoryStorage();
		saveDraft( 7, { elements: [ TEXT ] }, storage, 0 );

		expect( loadDraft( 7, DATA, storage, DRAFT_TTL - 1 ) ).not.toBeNull();
		expect( loadDraft( 7, DATA, storage, DRAFT_TTL ) ).toBeNull();
	} );

	it( 'no recupera un diseño que ya se añadió al carrito', () => {
		const storage = memoryStorage();
		saveDraft( 7, { elements: [ TEXT ], added: true }, storage, 1000 );

		expect( loadDraft( 7, DATA, storage, 2000 ) ).toBeNull();
	} );

	it( 'descarta elementos de zonas que el producto ya no tiene', () => {
		const storage = memoryStorage();
		saveDraft(
			7,
			{
				elements: [
					TEXT,
					{ id: 'b', kind: 'text', zone: 'sleeve' },
					{ id: 'c', kind: 'script', zone: 'front' },
				],
				color: 'verde',
				zoneId: 'sleeve',
			},
			storage,
			1000
		);

		expect( loadDraft( 7, DATA, storage, 2000 ) ).toEqual( {
			elements: [ TEXT ],
			color: null,
			viewId: null,
			zoneId: null,
		} );
	} );

	it( 'borra el borrador cuando el diseño se queda vacío', () => {
		const storage = memoryStorage();
		saveDraft( 7, { elements: [ TEXT ] }, storage, 1000 );
		saveDraft( 7, { elements: [] }, storage, 2000 );

		expect( storage.items.size ).toBe( 0 );
	} );

	it( 'ignora datos corruptos y almacenamientos que fallan', () => {
		const storage = memoryStorage();
		storage.setItem( 'camisama:draft:7', '{roto' );
		expect( loadDraft( 7, DATA, storage ) ).toBeNull();

		const broken = {
			getItem: () => {
				throw new Error( 'bloqueado' );
			},
			setItem: () => {
				throw new Error( 'lleno' );
			},
		};
		expect( loadDraft( 7, DATA, broken ) ).toBeNull();
		expect( () =>
			saveDraft( 7, { elements: [ TEXT ] }, broken )
		).not.toThrow();
		expect( loadDraft( 7, DATA, null ) ).toBeNull();
	} );
} );

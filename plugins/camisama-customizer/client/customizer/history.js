/**
 * Estado del diseño con historial para deshacer y rehacer.
 *
 * Los cambios continuos (arrastrar, deslizadores, escribir) llegan con `merge: true` y se funden
 * con el anterior si tocan el mismo elemento, para que deshacer no vaya píxel a píxel.
 */

const LIMIT = 100;

export const initialHistory = {
	past: [],
	present: [],
	future: [],
	lastKey: null,
};

/**
 * Reductor del historial.
 *
 * @param {Object} state  Estado actual.
 * @param {Object} action Acción: set, undo o redo.
 * @return {Object} Nuevo estado.
 */
export function historyReducer( state, action ) {
	switch ( action.type ) {
		case 'set': {
			const next = action.update( state.present );
			if ( next === state.present ) {
				return state;
			}

			const key = action.merge ? ( action.key ?? null ) : null;
			if ( key !== null && key === state.lastKey ) {
				return { ...state, present: next, future: [] };
			}

			return {
				past: [ ...state.past, state.present ].slice( -LIMIT ),
				present: next,
				future: [],
				lastKey: key,
			};
		}

		// Corrige el estado sin crear un paso de deshacer (p. ej. el tamaño medido de un texto).
		case 'patch': {
			const next = action.update( state.present );
			return next === state.present ? state : { ...state, present: next };
		}

		case 'undo':
			if ( ! state.past.length ) {
				return state;
			}
			return {
				past: state.past.slice( 0, -1 ),
				present: state.past[ state.past.length - 1 ],
				future: [ state.present, ...state.future ],
				lastKey: null,
			};

		case 'redo':
			if ( ! state.future.length ) {
				return state;
			}
			return {
				past: [ ...state.past, state.present ],
				present: state.future[ 0 ],
				future: state.future.slice( 1 ),
				lastKey: null,
			};

		default:
			return state;
	}
}

/**
 * Recargo de un diseño, igual que DesignPricer en PHP (src/Pricing/DesignPricer.php).
 *
 * El navegador lo usa para mostrar el precio en vivo; el servidor lo vuelve a calcular al añadir
 * al carrito. Ambos se prueban con tests/fixtures/pricing-cases.json.
 */

const EPSILON = 0.001;

/**
 * Rectángulo alineado con los ejes que ocupa un elemento rotado (cm de la zona).
 *
 * @param {Object} element Elemento con cx, cy, width_cm, height_cm y angle.
 * @return {{left: number, top: number, right: number, bottom: number}} Límites.
 */
export function elementBounds( element ) {
	const radians = ( ( element.angle ?? 0 ) * Math.PI ) / 180;
	const cos = Math.abs( Math.cos( radians ) );
	const sin = Math.abs( Math.sin( radians ) );
	const halfW = ( element.width_cm * cos + element.height_cm * sin ) / 2;
	const halfH = ( element.width_cm * sin + element.height_cm * cos ) / 2;

	return {
		left: element.cx - halfW,
		top: element.cy - halfH,
		right: element.cx + halfW,
		bottom: element.cy + halfH,
	};
}

/**
 * Escalón que corresponde a un lado mayor, o null si supera todos (como PriceTable::tier_for).
 *
 * @param {Array<{label: string, max_cm: number, amount: number}>} tiers     Escalones ordenados.
 * @param {number}                                                 longestCm Lado mayor.
 * @return {Object|null} Escalón.
 */
export function tierFor( tiers, longestCm ) {
	if ( longestCm <= 0 ) {
		return null;
	}

	return tiers.find( ( tier ) => longestCm <= tier.max_cm + EPSILON ) ?? null;
}

/**
 * Desglose del recargo de un diseño.
 *
 * @param {Array}                  zones    Zonas del producto (id, label, size_cm).
 * @param {Array}                  tiers    Escalones de precio ordenados.
 * @param {Array}                  elements Elementos del diseño.
 * @param {Object<string, number>} premiums ID de estampado => recargo en céntimos.
 * @return {{zones: Array, premiums: Array, total: number}} Desglose.
 * @throws {Error} Si un elemento está en una zona inexistente o el diseño supera todos los escalones.
 */
export function quote( zones, tiers, elements, premiums ) {
	const bounds = {};

	for ( const element of elements ) {
		const zone = zones.find( ( item ) => item.id === element.zone );
		if ( ! zone ) {
			throw new Error( `Zona desconocida: ${ element.zone }` );
		}

		const raw = elementBounds( element );
		const box = {
			left: Math.max( 0, raw.left ),
			top: Math.max( 0, raw.top ),
			right: Math.min( zone.size_cm.w, raw.right ),
			bottom: Math.min( zone.size_cm.h, raw.bottom ),
		};
		if ( box.right <= box.left || box.bottom <= box.top ) {
			continue;
		}

		const current = bounds[ zone.id ] ?? box;
		bounds[ zone.id ] = {
			left: Math.min( current.left, box.left ),
			top: Math.min( current.top, box.top ),
			right: Math.max( current.right, box.right ),
			bottom: Math.max( current.bottom, box.bottom ),
		};
	}

	const result = { zones: [], premiums: [], total: 0 };

	for ( const zone of zones ) {
		const box = bounds[ zone.id ];
		if ( ! box ) {
			continue;
		}

		const longest =
			Math.round(
				Math.max( box.right - box.left, box.bottom - box.top ) * 100
			) / 100;
		const tier = tierFor( tiers, longest );
		if ( ! tier ) {
			throw new Error( `Diseño demasiado grande en ${ zone.label }` );
		}

		result.zones.push( {
			zone: zone.id,
			label: zone.label,
			longest_cm: longest,
			tier: tier.label,
			amount: tier.amount,
		} );
		result.total += tier.amount;
	}

	const charged = new Set();
	for ( const element of elements ) {
		const id = element.design_id;
		const amount = premiums[ id ] ?? 0;
		if ( element.kind !== 'design' || charged.has( id ) || amount <= 0 ) {
			continue;
		}
		charged.add( id );
		result.premiums.push( { design_id: id, amount } );
		result.total += amount;
	}

	return result;
}

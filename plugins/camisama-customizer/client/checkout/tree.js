/**
 * Consultas sobre data/ubigeo.json: [ código, nombre, hijos ] en cada nivel.
 */

// Provincias donde se hacen envíos (como Ubigeo::DELIVERY_PROVINCES en PHP).
const DELIVERY_PROVINCES = [ '1501', '0701' ];

const DEPARTMENTS_WITH_DELIVERY = DELIVERY_PROVINCES.map( ( code ) =>
	code.slice( 0, 2 )
);

/**
 * Provincias de un departamento.
 *
 * @param {Array}  tree       Datos.
 * @param {string} department Código del departamento.
 * @return {Array} Provincias, o vacío.
 */
export function provincesOf( tree, department ) {
	return tree.find( ( item ) => item[ 0 ] === department )?.[ 2 ] ?? [];
}

/**
 * Distritos de una provincia.
 *
 * @param {Array}  tree     Datos.
 * @param {string} province Código de la provincia.
 * @return {Array} Distritos, o vacío.
 */
export function districtsOf( tree, province ) {
	return (
		provincesOf( tree, province.slice( 0, 2 ) ).find(
			( item ) => item[ 0 ] === province
		)?.[ 2 ] ?? []
	);
}

/**
 * Departamento y provincia de un distrito.
 *
 * @param {string} code Ubigeo.
 * @return {{department: string, province: string}} Códigos; vacíos si no hay distrito.
 */
export function partsOf( code ) {
	return code?.length === 6
		? { department: code.slice( 0, 2 ), province: code.slice( 0, 4 ) }
		: { department: '', province: '' };
}

/**
 * Separa departamentos o provincias en los que tienen envío a domicilio y el resto.
 *
 * @param {Array} items Departamentos o provincias: [ código, nombre, hijos ].
 * @return {Array} [ con envío, solo recojo ].
 */
export function byDelivery( items ) {
	const delivers = ( item ) =>
		item[ 0 ].length === 2
			? DEPARTMENTS_WITH_DELIVERY.includes( item[ 0 ] )
			: DELIVERY_PROVINCES.includes( item[ 0 ] );

	// Lima primero (como en DELIVERY_PROVINCES): es donde vive la mayoría de quienes compran.
	const rank = ( item ) =>
		DELIVERY_PROVINCES.findIndex( ( code ) =>
			code.startsWith( item[ 0 ] )
		);

	return [
		items.filter( delivers ).sort( ( a, b ) => rank( a ) - rank( b ) ),
		items.filter( ( item ) => ! delivers( item ) ),
	];
}

/**
 * Distrito con su tarifa de envío al lado, si la tiene.
 *
 * @param {Array}  district [ código, nombre ].
 * @param {Object} rates    Céntimos por ubigeo (Checkout\DeliveryRates).
 * @return {Array} [ código, texto de la opción ].
 */
export function withRate( district, rates ) {
	const cents = rates?.[ district[ 0 ] ];
	return cents
		? [
				district[ 0 ],
				`${ district[ 1 ] } · envío S/ ${ ( cents / 100 ).toFixed( 2 ) }`,
			]
		: district;
}

/**
 * ¿Lo elegido hasta ahora queda fuera de la zona de envíos? Sin elegir nada, todavía no.
 *
 * @param {string} department Código del departamento, o vacío.
 * @param {string} province   Código de la provincia, o vacío.
 * @return {boolean} Verdadero si ya se sabe que no se reparte allí.
 */
export function outsideDelivery( department, province ) {
	if ( province ) {
		return ! DELIVERY_PROVINCES.includes( province );
	}
	return (
		Boolean( department ) &&
		! DEPARTMENTS_WITH_DELIVERY.includes( department )
	);
}

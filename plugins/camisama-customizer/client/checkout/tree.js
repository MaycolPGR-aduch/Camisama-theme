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

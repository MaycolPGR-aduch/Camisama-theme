/**
 * Importe en céntimos con el símbolo de la tienda ("S/ 59.90").
 *
 * @param {number}                             cents    Céntimos.
 * @param {{symbol: string, decimals: number}} currency Moneda de la tienda.
 * @return {string} Importe formateado.
 */
export function formatMoney( cents, currency ) {
	const amount = ( cents / 100 ).toLocaleString( 'en-US', {
		minimumFractionDigits: currency.decimals,
		maximumFractionDigits: currency.decimals,
	} );

	return `${ currency.symbol } ${ amount }`;
}

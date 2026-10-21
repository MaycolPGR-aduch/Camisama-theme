/**
 * Generador de archivos de impresión de la página de producción.
 *
 * Dibuja cada zona a 300 DPI con el mismo renderizador que el editor de la tienda
 * (customizer/renderer.js) y la sube a camisama/v1/orders/…/zones/… para guardarla.
 */
import { createRoot, useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

import { renderZone } from '../../customizer/renderer';
import './production.scss';

async function upload( api, orderId, itemId, zoneId, blob ) {
	const body = new window.FormData();
	body.append( 'file', blob, `${ zoneId }.png` );

	const response = await window.fetch(
		`${ api.root }/orders/${ orderId }/items/${ itemId }/zones/${ zoneId }`,
		{
			method: 'POST',
			body,
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': api.nonce },
		}
	);
	if ( ! response.ok ) {
		const data = await response.json().catch( () => null );
		throw new Error(
			data?.message ??
				__( 'No se pudo guardar el archivo.', 'camisama-customizer' )
		);
	}
}

function App( { orderId, items, fonts, api } ) {
	const [ running, setRunning ] = useState( false );
	const [ progress, setProgress ] = useState( '' );
	const [ error, setError ] = useState( null );
	const total = items.reduce(
		( count, item ) => count + item.plan.length,
		0
	);

	const generate = async () => {
		setRunning( true );
		setError( null );
		let done = 0;

		try {
			for ( const item of items ) {
				for ( const plan of item.plan ) {
					done += 1;
					setProgress(
						sprintf(
							/* translators: 1: zona, 2: número, 3: total. */
							__(
								'Generando %1$s (%2$d de %3$d)…',
								'camisama-customizer'
							),
							plan.label,
							done,
							total
						)
					);
					const result = await renderZone( {
						zone: item.zones.find(
							( zone ) => zone.id === plan.zone
						),
						elements: item.elements.filter(
							( element ) => element.zone === plan.zone
						),
						fonts,
						dpi: 300,
					} );
					await upload(
						api,
						orderId,
						item.item_id,
						plan.zone,
						result.blob
					);
				}
			}
			window.location.reload();
		} catch ( exception ) {
			setError( exception.message );
			setRunning( false );
		}
	};

	return (
		<div className="camisama-production__app">
			<Button
				variant="primary"
				onClick={ generate }
				isBusy={ running }
				disabled={ running || ! total }
			>
				{ __(
					'Generar archivos de impresión (300 DPI)',
					'camisama-customizer'
				) }
			</Button>
			<span aria-live="polite">{ running ? progress : '' }</span>
			{ error && (
				<span className="camisama-production__error" role="alert">
					{ error }
				</span>
			) }
		</div>
	);
}

const root = document.getElementById( 'camisama-production-app' );
if ( root && window.camisamaProduction ) {
	createRoot( root ).render( <App { ...window.camisamaProduction } /> );
}

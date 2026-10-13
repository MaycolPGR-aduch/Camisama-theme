/**
 * Monta el editor en la caja "Personalizador" de la edición de producto.
 */
import { createRoot } from '@wordpress/element';

import App from './App';
import './editor.scss';

const root = document.getElementById( 'camisama-product-config' );
const field = document.getElementById( 'camisama_customizer_config' );

if ( root && field && window.camisamaProductConfig ) {
	createRoot( root ).render(
		<App field={ field } { ...window.camisamaProductConfig } />
	);
}

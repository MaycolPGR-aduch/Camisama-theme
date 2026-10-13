/**
 * Configuración de @wordpress/scripts ampliada: además de los bloques (detectados por block.json),
 * compila las apps de administración del plugin.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const extraEntries = {
	'admin/product-config':
		'./plugins/camisama-customizer/client/admin/product-config/index.js',
};

const withExtraEntries = ( config ) => ( {
	...config,
	entry: () => ( { ...config.entry(), ...extraEntries } ),
} );

module.exports = Array.isArray( defaultConfig )
	? [ withExtraEntries( defaultConfig[ 0 ] ), ...defaultConfig.slice( 1 ) ]
	: withExtraEntries( defaultConfig );

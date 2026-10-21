/**
 * Configuración de @wordpress/scripts ampliada: además de los bloques (detectados por block.json),
 * compila las apps de administración del plugin.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

const extraEntries = {
	'admin/product-config':
		'./plugins/camisama-customizer/client/admin/product-config/index.js',
	'admin/production':
		'./plugins/camisama-customizer/client/admin/production/index.js',
};

const withExtraEntries = ( config ) => ( {
	...config,
	entry: () => ( { ...config.entry(), ...extraEntries } ),
	output: {
		...config.output,
		// Fragmentos cargados con import() (el editor y Fabric.js). WordPress solo versiona los
		// puntos de entrada, así que el hash en el nombre evita que el navegador use uno viejo.
		chunkFilename: 'chunks/[name].[contenthash:8].js',
	},
} );

module.exports = Array.isArray( defaultConfig )
	? [ withExtraEntries( defaultConfig[ 0 ] ), ...defaultConfig.slice( 1 ) ]
	: withExtraEntries( defaultConfig );

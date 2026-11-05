// Ejecutar: node node_modules/webpack-cli/bin/cli.js --config bin/qa/renderer.cjs
const path = require( 'path' );
const fs = require( 'fs' );
const output = path.resolve(
	__dirname,
	'../../plugins/camisama-customizer/build/qa'
);
fs.mkdirSync( output, { recursive: true } );
fs.copyFileSync(
	path.join( __dirname, 'renderer.html' ),
	path.join( output, 'index.html' )
);
module.exports = {
	mode: 'production',
	entry: path.join( __dirname, 'renderer.js' ),
	output: { path: output, filename: 'renderer.js' },
	performance: { hints: false },
};

import {
	renderPreview,
	renderZone,
} from '../../plugins/camisama-customizer/client/customizer/renderer';
import { requireColorMockups } from '../../plugins/camisama-customizer/client/customizer/mockups';

const result = document.querySelector( '#result' );
const images = document.querySelector( '#images' );
const button = document.querySelector( '#run' );

async function pixels( src ) {
	const image = new Image();
	image.src = src;
	await image.decode();
	const canvas = document.createElement( 'canvas' );
	canvas.width = image.width;
	canvas.height = image.height;
	const ctx = canvas.getContext( '2d' );
	ctx.drawImage( image, 0, 0 );
	return {
		data: ctx.getImageData( 0, 0, image.width, image.height ).data,
		width: image.width,
		height: image.height,
	};
}

function show( src, label, filename ) {
	const figure = document.createElement( 'figure' );
	const image = document.createElement( 'img' );
	image.src = src;
	image.alt = label;
	const link = document.createElement( 'a' );
	link.href = src;
	link.download = filename;
	link.textContent = label;
	figure.append( image, link );
	images.append( figure );
}

button.addEventListener( 'click', async () => {
	button.disabled = true;
	images.replaceChildren();
	result.textContent = 'Comprobando…';
	try {
		const id = Number( document.querySelector( '#product' ).value );
		if ( ! Number.isInteger( id ) || id < 1 ) {
			throw new Error( 'Introduce el ID del piloto.' );
		}
		const response = await fetch(
			`/wp-json/camisama/v1/products/${ id }/customizer`
		);
		if ( ! response.ok ) {
			throw new Error( `API: ${ response.status }` );
		}
		const data = await response.json();
		const checks = [];
		const elements = data.zones.map( ( zone ) => ( {
			id: `qa-${ zone.id }`,
			kind: 'text',
			zone: zone.id,
			text: 'PILOTO',
			font: data.fonts[ 0 ].slug,
			color: '#D3261C',
			align: 'center',
			cx: zone.size_cm.w / 2,
			cy: zone.size_cm.h / 2,
			width_cm: Math.min( zone.size_cm.w * 0.7, 12 ),
			height_cm: 2,
			font_size_cm: zone.id === 'nape' ? 1 : 1.5,
			angle: 0,
		} ) );
		for ( const color of [ 'blanco', 'negro' ] ) {
			requireColorMockups( data, color );
			for ( const view of data.views ) {
				const zones = data.zones.filter(
					( zone ) => zone.view === view.id
				);
				const src = await renderPreview( {
					mockup: view.mockups[ color ],
					zones,
					elements: elements.filter( ( element ) =>
						zones.some( ( zone ) => zone.id === element.zone )
					),
					fonts: data.fonts,
				} );
				const image = await pixels( src );
				const corner = Array.from( image.data.slice( 0, 4 ) );
				if (
					corner[ 3 ] !== 255 ||
					corner
						.slice( 0, 3 )
						.some(
							( value, index ) =>
								Math.abs( value - [ 247, 244, 238 ][ index ] ) >
								3
						)
				) {
					throw new Error( 'Fondo JPEG incorrecto' );
				}
				checks.push(
					`${ color }/${ view.id }: JPEG ${ image.width }×${ image.height }, esquina ${ corner }`
				);
				show(
					src,
					`${ color } · ${ view.label }`,
					`${ color }-${ view.id }.jpg`
				);
			}
		}
		for ( const zone of data.zones ) {
			const printed = await renderZone( {
				zone,
				elements: elements.filter(
					( element ) => element.zone === zone.id
				),
				fonts: data.fonts,
			} );
			const src = URL.createObjectURL( printed.blob );
			const image = await pixels( src );
			let transparent = 0;
			let ink = 0;
			for ( let index = 3; index < image.data.length; index += 4 ) {
				if ( image.data[ index ] === 0 ) {
					transparent++;
				}
				if ( image.data[ index ] > 0 ) {
					ink++;
					// Los bordes casi transparentes pierden precisión RGB por premultiplicación.
					if (
						image.data[ index ] > 32 &&
						( image.data[ index - 3 ] < 180 ||
							image.data[ index - 2 ] > 80 ||
							image.data[ index - 1 ] > 80 )
					) {
						throw new Error(
							'Se detectó contenido ajeno al diseño rojo en la impresión'
						);
					}
				}
			}
			if (
				! transparent ||
				! ink ||
				printed.width_px !==
					Math.ceil( ( printed.width_cm * 300 ) / 2.54 )
			) {
				throw new Error(
					'Transparencia, tinta o tamaño a 300 DPI incorrectos'
				);
			}
			checks.push(
				`${ zone.id }: PNG ${ printed.width_px }×${ printed.height_px }, transparente, solo tinta, 300 DPI`
			);
			show(
				src,
				`Impresión · ${ zone.label }`,
				`impresion-${ zone.id }.png`
			);
		}
		result.textContent = `OK: ${ checks.length } comprobaciones\n${ checks.join( '\n' ) }`;
	} catch ( error ) {
		result.textContent = `ERROR: ${ error.message }`;
	} finally {
		button.disabled = false;
	}
} );

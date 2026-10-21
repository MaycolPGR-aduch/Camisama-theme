/**
 * Dibujo del diseño fuera de pantalla: vistas previas para el carrito y archivos de impresión.
 *
 * Usa los mismos objetos que el editor (objects.js), así el archivo del taller es idéntico a lo
 * que vio el cliente.
 */
import { FabricImage, StaticCanvas } from 'fabric';

import { printedBounds, zoneBox } from './geometry';
import { applyElement, createObject } from './objects';

const CM_PER_INCH = 2.54;

const fontFor = ( fonts, element ) =>
	fonts.find( ( font ) => font.slug === element.font ) ?? fonts[ 0 ];

function offscreen( width, height ) {
	return new StaticCanvas( document.createElement( 'canvas' ), {
		width,
		height,
		enableRetinaScaling: false,
		renderOnAddRemove: false,
	} );
}

async function drawElements( canvas, elements, fonts, boxFor ) {
	for ( const element of elements ) {
		const font = fontFor( fonts, element );
		const obj = await createObject( element, font );
		applyElement( obj, element, boxFor( element ), font );
		canvas.add( obj );
	}
	canvas.renderAll();
}

function toBlob( canvas, type, quality ) {
	return new Promise( ( resolve, reject ) =>
		canvas.lowerCanvasEl.toBlob(
			( blob ) =>
				blob ? resolve( blob ) : reject( new Error( 'toBlob' ) ),
			type,
			quality
		)
	);
}

/**
 * Vista previa de una vista: maqueta del color elegido con el diseño encima (JPEG).
 *
 * @param {Object} options          Opciones.
 * @param {Object} options.mockup   Maqueta (url, width, height).
 * @param {Array}  options.zones    Zonas de la vista.
 * @param {Array}  options.elements Elementos de esas zonas.
 * @param {Array}  options.fonts    Fuentes.
 * @param {number} options.width    Ancho en píxeles.
 * @return {Promise<string>} Imagen como data URL.
 */
export async function renderPreview( {
	mockup,
	zones,
	elements,
	fonts,
	width = 640,
} ) {
	const height = Math.round( ( width * mockup.height ) / mockup.width );
	const canvas = offscreen( width, height );

	try {
		const background = await FabricImage.fromURL( mockup.url, {
			crossOrigin: 'anonymous',
		} );
		background.set( {
			originX: 'left',
			originY: 'top',
			left: 0,
			top: 0,
			scaleX: width / background.width,
			scaleY: height / background.height,
		} );
		canvas.backgroundImage = background;

		await drawElements( canvas, elements, fonts, ( element ) =>
			zoneBox(
				zones.find( ( zone ) => zone.id === element.zone ),
				width,
				height
			)
		);

		return canvas.toDataURL( { format: 'jpeg', quality: 0.85 } );
	} finally {
		canvas.dispose();
	}
}

/**
 * Archivo de impresión de una zona: PNG transparente a `dpi`, del tamaño exacto de lo impreso.
 *
 * @param {Object} options          Opciones.
 * @param {Object} options.zone     Zona (size_cm).
 * @param {Array}  options.elements Elementos de la zona.
 * @param {Array}  options.fonts    Fuentes.
 * @param {number} options.dpi      Resolución.
 * @return {Promise<Object>} Blob PNG, medidas en px y cm, y posición dentro de la zona.
 */
export async function renderZone( { zone, elements, fonts, dpi = 300 } ) {
	const bounds = printedBounds( zone, elements );
	const ppc = dpi / CM_PER_INCH;
	const widthCm = bounds.right - bounds.left;
	const heightCm = bounds.bottom - bounds.top;
	const width = Math.ceil( widthCm * ppc );
	const height = Math.ceil( heightCm * ppc );
	const canvas = offscreen( width, height );

	// La zona se coloca de forma que la esquina de lo impreso quede en (0, 0).
	const box = {
		x: -bounds.left * ppc,
		y: -bounds.top * ppc,
		w: zone.size_cm.w * ppc,
		h: zone.size_cm.h * ppc,
		ppc,
	};

	try {
		await drawElements( canvas, elements, fonts, () => box );

		return {
			blob: await toBlob( canvas, 'image/png' ),
			width_px: width,
			height_px: height,
			width_cm: Math.round( widthCm * 10 ) / 10,
			height_cm: Math.round( heightCm * 10 ) / 10,
			offset_cm: {
				left: Math.round( bounds.left * 10 ) / 10,
				top: Math.round( bounds.top * 10 ) / 10,
			},
		};
	} finally {
		canvas.dispose();
	}
}

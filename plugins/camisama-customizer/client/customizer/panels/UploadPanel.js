/**
 * Subida de una imagen propia (arrastrar o elegir archivo).
 */
import { useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { uploadImage } from '../api';

const ACCEPT = 'image/png,image/jpeg,image/webp';
const MAX_MB = 15;

export default function UploadPanel( { api, onAdd } ) {
	const input = useRef( null );
	const [ status, setStatus ] = useState( 'idle' );
	const [ error, setError ] = useState( null );
	const [ dragging, setDragging ] = useState( false );

	const send = async ( file ) => {
		if ( ! file ) {
			return;
		}
		// Comprobación rápida en el navegador; la que cuenta es la del servidor.
		if ( ! ACCEPT.split( ',' ).includes( file.type ) ) {
			setError(
				__(
					'Formato no admitido: sube una imagen PNG, JPG o WEBP.',
					'camisama-customizer'
				)
			);
			return;
		}
		if ( file.size > MAX_MB * 1024 * 1024 ) {
			setError(
				__(
					'La imagen pesa demasiado: el máximo es 15 MB.',
					'camisama-customizer'
				)
			);
			return;
		}

		setStatus( 'uploading' );
		setError( null );
		try {
			onAdd( await uploadImage( api, file ) );
			setStatus( 'idle' );
		} catch ( exception ) {
			setError( exception.message );
			setStatus( 'idle' );
		}
		input.current.value = '';
	};

	return (
		<div className="camisama-customizer__panel">
			<div
				className={ `camisama-customizer__drop${
					dragging ? ' is-dragging' : ''
				}` }
				onDragOver={ ( event ) => {
					event.preventDefault();
					setDragging( true );
				} }
				onDragLeave={ () => setDragging( false ) }
				onDrop={ ( event ) => {
					event.preventDefault();
					setDragging( false );
					send( event.dataTransfer.files[ 0 ] );
				} }
			>
				<input
					ref={ input }
					id="camisama-upload"
					type="file"
					accept={ ACCEPT }
					className="camisama-customizer__sr-only"
					onChange={ ( event ) => send( event.target.files[ 0 ] ) }
					disabled={ status === 'uploading' }
				/>
				<label
					htmlFor="camisama-upload"
					className="camisama-customizer__button"
				>
					{ status === 'uploading'
						? __( 'Subiendo…', 'camisama-customizer' )
						: __( 'Elegir imagen', 'camisama-customizer' ) }
				</label>
				<p>
					{ /* Arrastrar solo tiene sentido con ratón: en pantallas táctiles se oculta. */ }
					<span className="camisama-customizer__drag-hint">
						{ __(
							'o arrástrala aquí.',
							'camisama-customizer'
						) }{ ' ' }
					</span>
					{ __(
						'PNG, JPG o WEBP de hasta 15 MB.',
						'camisama-customizer'
					) }
				</p>
			</div>

			{ error && (
				<p className="camisama-customizer__error" role="alert">
					{ error }
				</p>
			) }

			<p className="camisama-customizer__hint">
				{ __(
					'Consejos: usa la imagen más grande que tengas para que no se vea pixelada. Si tiene fondo, ese fondo también se imprime; mejor un PNG con fondo transparente.',
					'camisama-customizer'
				) }
			</p>
		</div>
	);
}

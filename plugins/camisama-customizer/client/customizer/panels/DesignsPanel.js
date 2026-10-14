/**
 * Catálogo de estampados: filtro por categoría y cuadrícula paginada.
 */
import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import { fetchCategories, fetchDesigns } from '../api';
import { formatMoney } from '../format';

export default function DesignsPanel( { api, currency, onAdd } ) {
	const [ categories, setCategories ] = useState( [] );
	const [ category, setCategory ] = useState( null );
	const [ items, setItems ] = useState( [] );
	const [ page, setPage ] = useState( 1 );
	const [ pages, setPages ] = useState( 1 );
	const [ status, setStatus ] = useState( 'loading' );
	const [ error, setError ] = useState( null );

	useEffect( () => {
		fetchCategories( api )
			.then( setCategories )
			.catch( () => {} );
	}, [ api ] );

	useEffect( () => {
		let cancelled = false;
		setStatus( 'loading' );
		fetchDesigns( api, category, page )
			.then( ( result ) => {
				if ( cancelled ) {
					return;
				}
				setItems( ( current ) =>
					page === 1 ? result.items : [ ...current, ...result.items ]
				);
				setPages( result.pages );
				setStatus( 'ready' );
			} )
			.catch( ( exception ) => {
				if ( ! cancelled ) {
					setError( exception.message );
					setStatus( 'error' );
				}
			} );
		return () => {
			cancelled = true;
		};
	}, [ api, category, page ] );

	const chooseCategory = ( slug ) => {
		setCategory( slug );
		setPage( 1 );
	};

	return (
		<div className="camisama-customizer__panel">
			{ categories.length > 0 && (
				<div
					className="camisama-customizer__chips"
					role="group"
					aria-label={ __( 'Categorías', 'camisama-customizer' ) }
				>
					<button
						type="button"
						aria-pressed={ category === null }
						onClick={ () => chooseCategory( null ) }
					>
						{ __( 'Todos', 'camisama-customizer' ) }
					</button>
					{ categories.map( ( item ) => (
						<button
							key={ item.slug }
							type="button"
							aria-pressed={ category === item.slug }
							onClick={ () => chooseCategory( item.slug ) }
						>
							{ item.name }
						</button>
					) ) }
				</div>
			) }

			{ status === 'error' && (
				<p className="camisama-customizer__error">{ error }</p>
			) }

			<ul className="camisama-customizer__designs">
				{ items.map( ( design ) => (
					<li key={ design.id }>
						<button type="button" onClick={ () => onAdd( design ) }>
							<img
								src={ design.file.url }
								alt=""
								loading="lazy"
								width={ design.file.width }
								height={ design.file.height }
							/>
							<span>{ design.title }</span>
							{ design.premium > 0 && (
								<small>
									{ sprintf(
										/* translators: %s: recargo premium. */
										__( '+ %s', 'camisama-customizer' ),
										formatMoney( design.premium, currency )
									) }
								</small>
							) }
						</button>
					</li>
				) ) }
			</ul>

			{ status === 'loading' && (
				<p className="camisama-customizer__hint">
					{ __( 'Cargando estampados…', 'camisama-customizer' ) }
				</p>
			) }
			{ status === 'ready' && items.length === 0 && (
				<p className="camisama-customizer__hint">
					{ __(
						'No hay estampados en esta categoría.',
						'camisama-customizer'
					) }
				</p>
			) }
			{ status === 'ready' && page < pages && (
				<button
					type="button"
					className="camisama-customizer__more"
					onClick={ () => setPage( page + 1 ) }
				>
					{ __( 'Ver más estampados', 'camisama-customizer' ) }
				</button>
			) }
		</div>
	);
}

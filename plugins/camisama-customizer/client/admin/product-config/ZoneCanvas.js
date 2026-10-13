/**
 * Maqueta de una vista con sus zonas: se arrastran para moverlas y desde la esquina para
 * redimensionarlas (conservando la proporción). Con el teclado: flechas para mover, Mayús para
 * pasos largos.
 */
import { useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

import { clamp, round4 } from './utils';

const MIN_SIZE = 0.02;

export default function ZoneCanvas( {
	imageUrl,
	zones,
	selectedId,
	onSelect,
	onChange,
	onAspect,
} ) {
	const containerRef = useRef( null );

	const startPointer = ( event, zone, mode ) => {
		event.preventDefault();
		event.stopPropagation();
		onSelect( zone.id );

		const box = containerRef.current.getBoundingClientRect();
		const start = { x: event.clientX, y: event.clientY, rect: zone.rect };

		const move = ( moveEvent ) => {
			const dx = ( moveEvent.clientX - start.x ) / box.width;
			const dy = ( moveEvent.clientY - start.y ) / box.height;
			const rect = start.rect;

			if ( 'move' === mode ) {
				onChange( zone.id, {
					...rect,
					x: round4( clamp( rect.x + dx, 0, 1 - rect.w ) ),
					y: round4( clamp( rect.y + dy, 0, 1 - rect.h ) ),
				} );
				return;
			}

			const ratio = rect.h / rect.w;
			let w = clamp( rect.w + dx, MIN_SIZE, 1 - rect.x );
			let h = w * ratio;
			if ( rect.y + h > 1 ) {
				h = 1 - rect.y;
				w = h / ratio;
			}
			onChange( zone.id, { ...rect, w: round4( w ), h: round4( h ) } );
		};

		const stop = () => {
			window.removeEventListener( 'pointermove', move );
			window.removeEventListener( 'pointerup', stop );
		};

		window.addEventListener( 'pointermove', move );
		window.addEventListener( 'pointerup', stop );
	};

	const onKeyDown = ( event, zone ) => {
		const step = event.shiftKey ? 0.02 : 0.005;
		const delta = {
			ArrowLeft: [ -step, 0 ],
			ArrowRight: [ step, 0 ],
			ArrowUp: [ 0, -step ],
			ArrowDown: [ 0, step ],
		}[ event.key ];

		if ( ! delta ) {
			return;
		}

		event.preventDefault();
		const rect = zone.rect;
		onChange( zone.id, {
			...rect,
			x: round4( clamp( rect.x + delta[ 0 ], 0, 1 - rect.w ) ),
			y: round4( clamp( rect.y + delta[ 1 ], 0, 1 - rect.h ) ),
		} );
	};

	return (
		<div className="camisama-zone-canvas" ref={ containerRef }>
			{ imageUrl ? (
				<img
					src={ imageUrl }
					alt=""
					draggable={ false }
					onLoad={ ( event ) =>
						onAspect(
							event.target.naturalWidth /
								event.target.naturalHeight
						)
					}
				/>
			) : (
				<div className="camisama-zone-canvas__empty">
					{ __(
						'Elige una imagen de maqueta para esta vista.',
						'camisama-customizer'
					) }
				</div>
			) }

			{ zones.map( ( zone ) => {
				const selected = zone.id === selectedId;
				return (
					<div
						key={ zone.id }
						role="button"
						tabIndex={ 0 }
						aria-pressed={ selected }
						aria-label={ sprintf(
							/* translators: 1: nombre de la zona, 2: ancho en cm, 3: alto en cm. */
							__(
								'Zona %1$s (%2$s × %3$s cm). Usa las flechas para moverla.',
								'camisama-customizer'
							),
							zone.label,
							zone.size_cm.w,
							zone.size_cm.h
						) }
						className={ `camisama-zone${
							selected ? ' is-selected' : ''
						}` }
						style={ {
							left: `${ zone.rect.x * 100 }%`,
							top: `${ zone.rect.y * 100 }%`,
							width: `${ zone.rect.w * 100 }%`,
							height: `${ zone.rect.h * 100 }%`,
						} }
						onPointerDown={ ( event ) =>
							startPointer( event, zone, 'move' )
						}
						onFocus={ () => onSelect( zone.id ) }
						onKeyDown={ ( event ) => onKeyDown( event, zone ) }
					>
						<span className="camisama-zone__label">
							{ zone.label }
						</span>
						{ selected && (
							<span
								className="camisama-zone__handle"
								aria-hidden="true"
								onPointerDown={ ( event ) =>
									startPointer( event, zone, 'resize' )
								}
							/>
						) }
					</div>
				);
			} ) }
		</div>
	);
}

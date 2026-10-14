/**
 * Escenario: la maqueta de la vista activa con sus zonas de impresión.
 *
 * Las zonas se pueden elegir con clic o teclado; la zona activa es donde se añaden los
 * estampados, textos e imágenes.
 */
import { __, sprintf } from '@wordpress/i18n';

export default function Stage( { mockup, zones, activeZoneId, onSelectZone } ) {
	return (
		<div className="camisama-customizer__stage">
			{ mockup ? (
				<img
					className="camisama-customizer__mockup"
					src={ mockup.url }
					width={ mockup.width }
					height={ mockup.height }
					alt=""
					draggable={ false }
				/>
			) : (
				<div className="camisama-customizer__mockup is-empty" />
			) }

			{ zones.map( ( zone ) => (
				<button
					key={ zone.id }
					type="button"
					className={ `camisama-customizer__zone${
						zone.id === activeZoneId ? ' is-active' : ''
					}` }
					style={ {
						left: `${ zone.rect.x * 100 }%`,
						top: `${ zone.rect.y * 100 }%`,
						width: `${ zone.rect.w * 100 }%`,
						height: `${ zone.rect.h * 100 }%`,
					} }
					aria-pressed={ zone.id === activeZoneId }
					aria-label={ sprintf(
						/* translators: 1: zona, 2: ancho en cm, 3: alto en cm. */
						__(
							'Zona %1$s, hasta %2$s × %3$s cm',
							'camisama-customizer'
						),
						zone.label,
						zone.size_cm.w,
						zone.size_cm.h
					) }
					onClick={ () => onSelectZone( zone.id ) }
				>
					<span>{ zone.label }</span>
				</button>
			) ) }
		</div>
	);
}

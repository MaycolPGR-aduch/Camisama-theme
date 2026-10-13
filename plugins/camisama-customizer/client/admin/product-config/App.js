/**
 * Editor de la configuración del personalizador de un producto.
 *
 * Todo el estado vive aquí y se copia como JSON al campo oculto que viaja con el formulario del
 * producto; PHP lo valida al guardar (ProductConfigMetaBox::save).
 */
import { useEffect, useState } from '@wordpress/element';
import {
	Button,
	Notice,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

import ZoneCanvas from './ZoneCanvas';
import ZonePanel from './ZonePanel';
import { fitRect, normalizeConfig, slugify, uniqueId } from './utils';

function chooseImage( onChoose ) {
	const frame = window.wp.media( {
		title: __( 'Imagen de maqueta', 'camisama-customizer' ),
		button: { text: __( 'Usar esta imagen', 'camisama-customizer' ) },
		library: { type: 'image' },
		multiple: false,
	} );

	frame.on( 'select', () => {
		const attachment = frame.state().get( 'selection' ).first().toJSON();
		onChoose(
			attachment.id,
			attachment.sizes?.large?.url ?? attachment.url
		);
	} );
	frame.open();
}

export default function App( {
	field,
	colors,
	images: initialImages,
	presets,
	priceTiers,
	currency,
	settingsUrl,
} ) {
	const [ config, setConfig ] = useState( () =>
		normalizeConfig( JSON.parse( field.value || '{}' ) )
	);
	const [ images, setImages ] = useState( initialImages ?? {} );
	const [ viewId, setViewId ] = useState( config.views[ 0 ]?.id ?? null );
	const [ color, setColor ] = useState( colors[ 0 ].slug );
	const [ zoneId, setZoneId ] = useState( null );
	const [ presetId, setPresetId ] = useState( presets[ 0 ]?.id );
	const [ aspect, setAspect ] = useState( 1 );

	useEffect( () => {
		field.value = JSON.stringify( config );
	}, [ config, field ] );

	const view = config.views.find( ( item ) => item.id === viewId );
	const viewZones = config.zones.filter( ( zone ) => zone.view === viewId );
	const zone = config.zones.find( ( item ) => item.id === zoneId );

	// Si el color elegido no tiene maqueta en esta vista, se muestra la de otro color.
	const mockupId =
		view?.mockups[ color ] ?? Object.values( view?.mockups ?? {} )[ 0 ];
	const usingFallback = view && mockupId && ! view.mockups[ color ];

	const update = ( patch ) =>
		setConfig( ( current ) => ( { ...current, ...patch } ) );

	const updateZone = ( id, patch ) =>
		setConfig( ( current ) => ( {
			...current,
			zones: current.zones.map( ( item ) =>
				item.id === id ? { ...item, ...patch } : item
			),
		} ) );

	const updateView = ( id, patch ) =>
		setConfig( ( current ) => ( {
			...current,
			views: current.views.map( ( item ) =>
				item.id === id ? { ...item, ...patch } : item
			),
		} ) );

	const applyPreset = () => {
		const preset = presets.find( ( item ) => item.id === presetId );
		if (
			! preset ||
			( config.zones.length &&
				// eslint-disable-next-line no-alert
				! window.confirm(
					__(
						'Se reemplazarán las vistas y zonas actuales (las imágenes de las vistas con el mismo nombre se conservan). ¿Continuar?',
						'camisama-customizer'
					)
				) )
		) {
			return;
		}

		const presetConfig = normalizeConfig( preset.config );
		setConfig( {
			...presetConfig,
			views: presetConfig.views.map( ( item ) => ( {
				...item,
				mockups:
					config.views.find( ( old ) => old.id === item.id )
						?.mockups ?? {},
			} ) ),
		} );
		setViewId( presetConfig.views[ 0 ]?.id ?? null );
		setZoneId( null );
	};

	const addView = () => {
		const id = uniqueId(
			'vista',
			config.views.map( ( item ) => item.id )
		);
		update( {
			views: [
				...config.views,
				{
					id,
					label: __( 'Nueva vista', 'camisama-customizer' ),
					mockups: {},
				},
			],
		} );
		setViewId( id );
		setZoneId( null );
	};

	const removeView = () => {
		if (
			// eslint-disable-next-line no-alert
			! window.confirm(
				sprintf(
					/* translators: %s: nombre de la vista. */
					__(
						'¿Eliminar la vista "%s" y sus zonas?',
						'camisama-customizer'
					),
					view.label
				)
			)
		) {
			return;
		}

		const views = config.views.filter( ( item ) => item.id !== viewId );
		update( {
			views,
			zones: config.zones.filter( ( item ) => item.view !== viewId ),
		} );
		setViewId( views[ 0 ]?.id ?? null );
		setZoneId( null );
	};

	const addZone = () => {
		const label = __( 'Nueva zona', 'camisama-customizer' );
		const id = uniqueId(
			slugify( label ),
			config.zones.map( ( item ) => item.id )
		);
		const size = { w: 10, h: 10 };
		update( {
			zones: [
				...config.zones,
				{
					id,
					label,
					view: viewId,
					rect: fitRect(
						{ x: 0.4, y: 0.4, w: 0.15, h: 0.15 },
						size,
						aspect
					),
					size_cm: size,
				},
			],
		} );
		setZoneId( id );
	};

	const setMockup = () =>
		chooseImage( ( attachmentId, url ) => {
			setImages( ( current ) => ( {
				...current,
				[ attachmentId ]: url,
			} ) );
			updateView( viewId, {
				mockups: { ...view.mockups, [ color ]: attachmentId },
			} );
		} );

	return (
		<div className="camisama-product-config">
			<div className="camisama-product-config__toolbar">
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __(
						'Este producto se puede personalizar',
						'camisama-customizer'
					) }
					checked={ config.enabled }
					onChange={ ( enabled ) => update( { enabled } ) }
				/>

				<div className="camisama-product-config__preset">
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Plantilla', 'camisama-customizer' ) }
						hideLabelFromVision
						value={ presetId }
						options={ presets.map( ( preset ) => ( {
							value: preset.id,
							label: preset.label,
						} ) ) }
						onChange={ setPresetId }
					/>
					<Button
						__next40pxDefaultSize
						variant="secondary"
						onClick={ applyPreset }
					>
						{ __( 'Aplicar plantilla', 'camisama-customizer' ) }
					</Button>
				</div>
			</div>

			{ config.enabled && ! config.zones.length && (
				<Notice status="warning" isDismissible={ false }>
					{ __(
						'Añade al menos una zona de impresión o aplica una plantilla; si no, el producto no se podrá personalizar.',
						'camisama-customizer'
					) }
				</Notice>
			) }

			<div
				className="camisama-product-config__tabs"
				role="tablist"
				aria-label={ __( 'Vistas', 'camisama-customizer' ) }
			>
				{ config.views.map( ( item ) => (
					<Button
						key={ item.id }
						role="tab"
						aria-selected={ item.id === viewId }
						variant={ item.id === viewId ? 'primary' : 'tertiary' }
						onClick={ () => {
							setViewId( item.id );
							setZoneId( null );
						} }
					>
						{ item.label }
					</Button>
				) ) }
				<Button variant="link" onClick={ addView }>
					{ __( '+ Añadir vista', 'camisama-customizer' ) }
				</Button>
			</div>

			{ view && (
				<div className="camisama-product-config__body">
					<div className="camisama-product-config__stage">
						<div className="camisama-product-config__colors">
							{ colors.map( ( item ) => (
								<Button
									key={ item.slug }
									size="compact"
									variant={
										item.slug === color
											? 'primary'
											: 'secondary'
									}
									onClick={ () => setColor( item.slug ) }
								>
									{ item.name }
									{ view.mockups[ item.slug ] ? ' ✓' : '' }
								</Button>
							) ) }
						</div>

						<ZoneCanvas
							imageUrl={ mockupId ? images[ mockupId ] : null }
							zones={ viewZones }
							selectedId={ zoneId }
							onSelect={ setZoneId }
							onChange={ ( id, rect ) =>
								updateZone( id, { rect } )
							}
							onAspect={ setAspect }
						/>

						{ usingFallback && (
							<p className="description">
								{ __(
									'Este color aún no tiene imagen en esta vista; se muestra la de otro color.',
									'camisama-customizer'
								) }
							</p>
						) }

						<div className="camisama-product-config__actions">
							<Button variant="secondary" onClick={ setMockup }>
								{ view.mockups[ color ]
									? __(
											'Cambiar imagen de este color',
											'camisama-customizer'
										)
									: __(
											'Elegir imagen de este color',
											'camisama-customizer'
										) }
							</Button>
							<Button variant="secondary" onClick={ addZone }>
								{ __( '+ Añadir zona', 'camisama-customizer' ) }
							</Button>
						</div>
					</div>

					<div className="camisama-product-config__side">
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __(
								'Nombre de la vista',
								'camisama-customizer'
							) }
							value={ view.label }
							onChange={ ( label ) =>
								updateView( view.id, { label } )
							}
						/>

						<ZonePanel
							zone={ zone?.view === viewId ? zone : null }
							priceTiers={ priceTiers }
							currency={ currency }
							settingsUrl={ settingsUrl }
							onLabel={ ( label ) =>
								updateZone( zone.id, { label } )
							}
							onSize={ ( size ) =>
								updateZone( zone.id, {
									size_cm: size,
									rect: fitRect( zone.rect, size, aspect ),
								} )
							}
							onRemove={ () => {
								update( {
									zones: config.zones.filter(
										( item ) => item.id !== zone.id
									),
								} );
								setZoneId( null );
							} }
						/>

						<Button
							variant="link"
							isDestructive
							onClick={ removeView }
						>
							{ __(
								'Eliminar esta vista',
								'camisama-customizer'
							) }
						</Button>
					</div>
				</div>
			) }
		</div>
	);
}

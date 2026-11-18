/**
 * Portada: hero con ejemplos que se turnan y simulador de zonas y precio.
 *
 * Todo es mejora progresiva: sin este archivo el hero muestra el primer ejemplo y la sección de
 * precio el desglose fijo que pinta el servidor. Las tarifas vienen ya convertidas a precio
 * mostrado y en céntimos (camisama_home_price_data() en inc/home.php); aquí solo se suman.
 */
( function () {
	'use strict';

	const reducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)'
	);

	/**
	 * Datos de precios publicados por la sección de zonas, o null.
	 *
	 * @return {Object|null} Datos.
	 */
	function readPrices() {
		const node = document.getElementById( 'camisama-home-prices' );
		if ( ! node ) {
			return null;
		}
		try {
			return JSON.parse( node.textContent );
		} catch {
			return null;
		}
	}

	/**
	 * Formatea céntimos como lo hace WooCommerce (símbolo, separadores y decimales).
	 *
	 * @param {Object} money Formato de la tienda.
	 * @param {number} cents Importe en céntimos.
	 * @return {string} Precio legible.
	 */
	function formatMoney( money, cents ) {
		const fixed = ( Math.abs( cents ) / 100 ).toFixed( money.decimals );
		const [ whole, fraction ] = fixed.split( '.' );
		const grouped = whole.replace(
			/\B(?=(\d{3})+(?!\d))/g,
			money.thousand
		);
		const number = fraction ? grouped + money.decimal + fraction : grouped;
		return (
			( cents < 0 ? '-' : '' ) +
			money.format
				.replace( '%1$s', money.symbol )
				.replace( '%2$s', number )
		);
	}

	/**
	 * Primer escalón que admite ese lado mayor.
	 *
	 * @param {Array}  tiers Escalones ordenados.
	 * @param {number} cm    Lado mayor en cm.
	 * @return {Object|undefined} Escalón.
	 */
	function tierFor( tiers, cm ) {
		return tiers.find( ( tier ) => cm <= tier.max_cm + 0.001 );
	}

	/**
	 * "hasta 20 cm" con el número sin decimales sobrantes.
	 *
	 * @param {Object} prices Tarifas.
	 * @param {Object} tier   Escalón.
	 * @return {string} Texto.
	 */
	function upTo( prices, tier ) {
		return prices.text.upTo.replace(
			'%s',
			String( Number( tier.max_cm ) )
		);
	}

	/**
	 * Vuelve a lanzar una animación CSS de una sola vez.
	 *
	 * @param {Element} element   Elemento.
	 * @param {string}  className Clase con la animación.
	 */
	function replay( element, className ) {
		element.classList.remove( className );
		void element.offsetWidth;
		element.classList.add( className );
	}

	/**
	 * Hero: tres ejemplos que avanzan solos al terminar la barra de progreso de la pestaña activa.
	 * Se pausa con el botón, al elegir un ejemplo, al pasar el cursor o enfocar y fuera de pantalla.
	 *
	 * @param {Element}     root   Figura del hero.
	 * @param {Object|null} prices Tarifas.
	 */
	function initHero( root, prices ) {
		const stage = root.querySelector( '.camisama-live__stage' );
		const controls = root.querySelector( '.camisama-live__controls' );
		const pause = root.querySelector( '.camisama-live__pause' );
		const tabs = Array.from(
			root.querySelectorAll( '.camisama-live__tab' )
		);
		if ( ! stage || ! controls || ! pause || tabs.length === 0 ) {
			return;
		}

		const chip = root.querySelector( '.camisama-live__chip' );
		const front =
			prices && prices.zones.find( ( zone ) => zone.id === 'front' );
		let current = 0;

		function setPaused( paused ) {
			root.classList.toggle( 'is-paused', paused );
			pause.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
			pause.setAttribute(
				'aria-label',
				paused ? pause.dataset.playLabel : pause.dataset.pauseLabel
			);
		}

		function renderChip( tab ) {
			const tier =
				prices && tierFor( prices.tiers, Number( tab.dataset.size ) );
			if ( ! chip || ! tier || ! front ) {
				return;
			}
			chip.textContent = '';
			const label = document.createElement( 'span' );
			label.textContent = `${ front.label } · ${ tier.label }, ${ upTo( prices, tier ) }`;
			const amount = document.createElement( 'strong' );
			amount.textContent =
				'+ ' + formatMoney( prices.money, tier.amount );
			chip.append( label, amount );
			chip.hidden = false;
			replay( chip, 'is-changing' );
		}

		function show( index ) {
			current = ( index + tabs.length ) % tabs.length;
			const tab = tabs[ current ];
			root.dataset.state = String( current );
			root.dataset.tee = tab.dataset.tee;
			stage.setAttribute( 'aria-label', tab.dataset.label );
			tabs.forEach( ( other ) =>
				other.setAttribute(
					'aria-pressed',
					other === tab ? 'true' : 'false'
				)
			);
			renderChip( tab );
		}

		pause.dataset.pauseLabel = pause.getAttribute( 'aria-label' );

		tabs.forEach( ( tab, index ) => {
			tab.addEventListener( 'click', () => {
				setPaused( true );
				show( index );
			} );
			tab.querySelector( '.camisama-live__progress' ).addEventListener(
				'animationend',
				() => show( current + 1 )
			);
		} );

		pause.addEventListener( 'click', () =>
			setPaused( ! root.classList.contains( 'is-paused' ) )
		);

		pauseOffscreen( root, 'is-offscreen' );

		// Con movimiento reducido no hay avance automático: los ejemplos se eligen a mano.
		setPaused( reducedMotion.matches );
		controls.hidden = false;
		show( 0 );
	}

	/**
	 * Simulador: zonas que se encienden y apagan, tamaño en las grandes y desglose en vivo.
	 *
	 * @param {Element} section Sección de zonas y precio.
	 * @param {Object}  prices  Tarifas.
	 */
	function initPricing( section, prices ) {
		const picker = section.querySelector( '[data-camisama-zone-picker]' );
		const map = section.querySelector( '[data-camisama-zone-map]' );
		const card = section.querySelector( '[data-camisama-price-card]' );
		if ( ! picker || ! map || ! card ) {
			return;
		}

		const list = card.querySelector( 'dl' );
		const total = card.querySelector( '[data-price-total]' );
		const totalRow = total && total.parentElement;
		if ( ! list || ! total || ! totalRow ) {
			return;
		}

		const title = card.querySelector( '[data-price-title]' );
		const announce = card.querySelector( '[data-price-announce]' );
		const chips = Array.from(
			picker.querySelectorAll( 'button[data-zone]' )
		);

		// Zona activa => índice del escalón elegido.
		const chosen = new Map( [ [ 'front', 0 ] ] );
		let lastTotal = null;

		function sizeOf( zone ) {
			const checked = picker.querySelector(
				`input[name="camisama-size-${ zone.id }"]:checked`
			);
			return checked ? Number( checked.value ) : 0;
		}

		function render( changedZone ) {
			list.querySelectorAll( '[data-price-line]' ).forEach( ( row ) =>
				row.remove()
			);

			let sum = prices.base;
			const parts = [];
			prices.zones.forEach( ( zone ) => {
				const active = chosen.has( zone.id );
				const chip = chips.find(
					( item ) => item.dataset.zone === zone.id
				);
				const shape = map.querySelector( `[data-zone="${ zone.id }"]` );
				const sizes = picker.querySelector(
					`[data-sizes-for="${ zone.id }"]`
				);
				if ( chip ) {
					chip.setAttribute(
						'aria-pressed',
						active ? 'true' : 'false'
					);
				}
				if ( shape ) {
					shape.classList.toggle( 'is-active', active );
				}
				if ( sizes ) {
					sizes.hidden = ! active;
				}
				if ( ! active ) {
					return;
				}

				const tier = prices.tiers[ chosen.get( zone.id ) ];
				sum += tier.amount;
				parts.push(
					`${ zone.label.toLowerCase() } ${ tier.label.toLowerCase() }`
				);

				const row = document.createElement( 'div' );
				row.dataset.priceLine = '';
				const dt = document.createElement( 'dt' );
				dt.textContent = `${ zone.label } · ${ tier.label }, ${ upTo( prices, tier ) }`;
				const dd = document.createElement( 'dd' );
				dd.textContent =
					'+ ' + formatMoney( prices.money, tier.amount );
				row.append( dt, dd );
				if ( zone.id === changedZone && ! reducedMotion.matches ) {
					row.classList.add( 'is-entering' );
				}
				list.insertBefore( row, totalRow );
			} );

			const text = formatMoney( prices.money, sum );
			total.textContent = text;
			if ( lastTotal !== null && lastTotal !== sum ) {
				replay( total, 'is-changing' );
			}
			lastTotal = sum;

			if ( title ) {
				title.textContent = parts.length
					? prices.text.combo
					: prices.text.garment;
			}
			if ( announce && changedZone ) {
				announce.textContent = parts.length
					? prices.text.total
							.replace( '%1$s', text )
							.replace( '%2$s', parts.join( ', ' ) )
					: prices.text.totalNone.replace( '%s', text );
			}
		}

		function toggle( id ) {
			const zone = prices.zones.find( ( item ) => item.id === id );
			if ( ! zone ) {
				return;
			}
			if ( chosen.has( id ) ) {
				chosen.delete( id );
			} else {
				chosen.set( id, sizeOf( zone ) );
			}
			render( id );
		}

		chips.forEach( ( chip ) =>
			chip.addEventListener( 'click', () => toggle( chip.dataset.zone ) )
		);

		picker.addEventListener( 'change', ( event ) => {
			const fieldset = event.target.closest( '[data-sizes-for]' );
			if ( fieldset ) {
				chosen.set(
					fieldset.dataset.sizesFor,
					Number( event.target.value )
				);
				render( fieldset.dataset.sizesFor );
			}
		} );

		// El dibujo es un atajo para el ratón; las fichas son los controles accesibles.
		map.addEventListener( 'click', ( event ) => {
			const shape = event.target.closest( '[data-zone]' );
			if ( shape ) {
				toggle( shape.dataset.zone );
			}
		} );

		map.classList.add( 'is-interactive' );
		picker.hidden = false;
		render( null );
	}

	/**
	 * Añade o quita una clase según el elemento esté en pantalla, para pausar lo que se mueve.
	 *
	 * @param {Element} element   Elemento observado.
	 * @param {string}  className Clase que se pone mientras está fuera.
	 */
	function pauseOffscreen( element, className ) {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			return;
		}
		new window.IntersectionObserver( ( entries ) => {
			element.classList.toggle(
				className,
				! entries[ 0 ].isIntersecting
			);
		} ).observe( element );
	}

	/**
	 * Franja de estampados: botón de pausa y parada fuera de pantalla. Con movimiento
	 * reducido la franja ya está quieta (CSS) y el botón no hace falta.
	 *
	 * @param {Element} root Sección de la franja.
	 */
	function initDesigns( root ) {
		const pause = root.querySelector( '.camisama-designs__pause' );
		pauseOffscreen( root, 'is-offscreen' );
		if ( ! pause || reducedMotion.matches ) {
			return;
		}
		const pauseLabel = pause.getAttribute( 'aria-label' );
		pause.addEventListener( 'click', () => {
			const paused = root.classList.toggle( 'is-paused' );
			pause.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
			pause.setAttribute(
				'aria-label',
				paused ? pause.dataset.playLabel : pauseLabel
			);
		} );
		pause.hidden = false;
	}

	/**
	 * Muestras de color de los ejemplos: cambian la maqueta y el tono del texto impreso.
	 *
	 * @param {Element} group Grupo de muestras de un ejemplo.
	 */
	function initSwatches( group ) {
		const tee = group.parentElement.querySelector( '.camisama-tee' );
		const base = tee && tee.querySelector( '.camisama-tee__base' );
		if ( ! base ) {
			return;
		}
		const swatches = Array.from(
			group.querySelectorAll( '.camisama-swatch' )
		);

		swatches.forEach( ( swatch ) => {
			// Precarga al acercarse, para que el cambio sea inmediato.
			swatch.addEventListener(
				'pointerenter',
				() => {
					new window.Image().src = swatch.dataset.src;
				},
				{ once: true }
			);
			swatch.addEventListener( 'click', () => {
				swatches.forEach( ( other ) =>
					other.setAttribute(
						'aria-pressed',
						other === swatch ? 'true' : 'false'
					)
				);
				const apply = () => {
					base.src = swatch.dataset.src;
					tee.dataset.tone = swatch.dataset.tone;
					base.classList.remove( 'is-swapping' );
				};
				if ( reducedMotion.matches ) {
					apply();
					return;
				}
				base.classList.add( 'is-swapping' );
				window.setTimeout( apply, 200 );
			} );
		} );
		group.hidden = false;
	}

	/**
	 * Pasos: el que cruza el centro de la pantalla pasa a ser el actual y la prenda muestra
	 * su estado.
	 *
	 * @param {Element} root Sección de los pasos.
	 */
	function initSteps( root ) {
		const steps = Array.from(
			root.querySelectorAll( '[data-step-index]' )
		);
		if ( steps.length === 0 || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		function setStep( index ) {
			root.dataset.step = String( index );
			steps.forEach( ( step, position ) =>
				step.classList.toggle( 'is-current', position === index )
			);
		}

		const observer = new window.IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						setStep( Number( entry.target.dataset.stepIndex ) );
					}
				} );
			},
			{ rootMargin: '-45% 0px -45% 0px' }
		);
		steps.forEach( ( step ) => observer.observe( step ) );

		root.classList.add( 'is-enhanced' );
		setStep( 0 );
	}

	/**
	 * Entradas al hacer scroll. Los bloques de un mismo grupo entran escalonados. Con movimiento
	 * reducido no se toca nada: todo se ve desde el principio.
	 */
	function initReveal() {
		if ( reducedMotion.matches || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}
		const groups = [
			'.camisama-designs__head',
			'.camisama-section-heading',
			'.camisama-examples__grid > figure',
			'.camisama-models .wc-block-product',
			'.camisama-steps h2',
			'.camisama-print__zones',
			'.camisama-faq__intro',
			'.camisama-faq__list > details',
			'.camisama-home-close__copy',
		];
		const observer = new window.IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-visible' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ rootMargin: '0px 0px -10% 0px', threshold: 0.1 }
		);
		groups.forEach( ( selector ) => {
			document
				.querySelectorAll( selector )
				.forEach( ( element, index ) => {
					element.style.setProperty(
						'--reveal-delay',
						`${ ( index % 4 ) * 90 }ms`
					);
					element.classList.add( 'camisama-reveal' );
					observer.observe( element );
				} );
		} );
	}

	/**
	 * Botones principales: aro de "sello" al pulsarlos.
	 */
	function initStampButtons() {
		document.addEventListener( 'pointerdown', ( event ) => {
			const button = event.target.closest( '.camisama-home-button' );
			if ( button ) {
				replay( button, 'is-stamped' );
			}
		} );
	}

	/**
	 * Cierre: el sello cae la primera vez que la camiseta se ve casi entera.
	 *
	 * @param {Element} root Sección de cierre.
	 */
	function initClose( root ) {
		if ( reducedMotion.matches || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}
		root.classList.add( 'is-armed' );
		const observer = new window.IntersectionObserver(
			( entries ) => {
				if ( entries[ 0 ].isIntersecting ) {
					root.classList.add( 'is-stamped' );
					root.classList.remove( 'is-armed' );
					observer.disconnect();
				}
			},
			{ threshold: 0.6 }
		);
		observer.observe(
			root.querySelector( '.camisama-home-close__visual' ) || root
		);
	}

	function init() {
		const prices = readPrices();
		const hero = document.querySelector( '[data-camisama-live]' );
		const section = document.getElementById( 'zonas-y-precio' );
		if ( hero ) {
			initHero( hero, prices );
		}
		if ( section && prices ) {
			initPricing( section, prices );
		}
		const designs = document.querySelector( '[data-camisama-designs]' );
		if ( designs ) {
			initDesigns( designs );
		}
		document
			.querySelectorAll( '.camisama-swatches' )
			.forEach( initSwatches );
		const steps = document.querySelector( '[data-camisama-steps]' );
		if ( steps ) {
			initSteps( steps );
		}
		const close = document.querySelector( '[data-camisama-close]' );
		if ( close ) {
			initClose( close );
		}
		initStampButtons();
		initReveal();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();

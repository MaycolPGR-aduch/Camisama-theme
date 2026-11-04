<?php
/**
 * Páginas de confianza de la tienda local: privacidad, cambios y devoluciones, términos y contacto.
 *
 * SON BORRADORES PARA REVISAR, no asesoría legal: llevan un aviso arriba y huecos "[Por completar: …]"
 * donde falta un dato del negocio. Antes de abrir al público, hay que completarlos, revisarlos con un
 * abogado y quitar el aviso.
 *
 * Solo rellena páginas que no estén publicadas: una vez publicada una página, este script ya no la
 * toca, así que no pisa lo que se haya revisado.
 *
 * Uso: wp eval-file bin/seed-legal.php --use-include --user=admin
 *
 * @package Camisama
 */

declare( strict_types=1 );

/**
 * Bloques de párrafo, encabezado y lista a partir de líneas de texto.
 *
 * @param array<int, string|array<int, string>> $items Una cadena es un párrafo; "## " delante, un
 *                                                     encabezado; un array, una lista.
 * @return string Marcado de bloques.
 */
function camisama_legal_blocks( array $items ): string {
	$note   = 'Borrador para revisar: completa los datos marcados y revísalo con un abogado antes de abrir la tienda. Quita este aviso al terminar.';
	$blocks = array( "<!-- wp:paragraph {\"className\":\"camisama-draft-note\"} -->\n<p class=\"camisama-draft-note\">{$note}</p>\n<!-- /wp:paragraph -->" );

	foreach ( $items as $item ) {
		if ( is_array( $item ) ) {
			$rows     = array_map(
				static fn ( string $row ): string => "<!-- wp:list-item -->\n<li>{$row}</li>\n<!-- /wp:list-item -->",
				$item
			);
			$blocks[] = "<!-- wp:list -->\n<ul class=\"wp-block-list\">" . implode( "\n", $rows ) . "</ul>\n<!-- /wp:list -->";
		} elseif ( str_starts_with( $item, '## ' ) ) {
			$blocks[] = "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . substr( $item, 3 ) . "</h2>\n<!-- /wp:heading -->";
		} else {
			$blocks[] = "<!-- wp:paragraph -->\n<p>{$item}</p>\n<!-- /wp:paragraph -->";
		}
	}

	return implode( "\n\n", $blocks );
}

/**
 * Crea la página o rellena la existente si no está publicada, y la publica.
 *
 * @param int    $existing ID de la página existente, o 0.
 * @param string $title    Título.
 * @param string $slug     Ruta.
 * @param string $content  Contenido.
 * @return int ID de la página.
 */
function camisama_legal_page( int $existing, string $title, string $slug, string $content ): int {
	$found = $existing > 0 ? get_post( $existing ) : get_page_by_path( $slug );
	if ( $found instanceof WP_Post && 'publish' === $found->post_status ) {
		WP_CLI::log( "«{$title}» ya está publicada: no se toca." );
		return $found->ID;
	}

	$id = wp_insert_post(
		array(
			'ID'           => $found instanceof WP_Post ? $found->ID : 0,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id );
	}
	WP_CLI::log( "«{$title}» publicada como borrador para revisar." );

	return (int) $id;
}

$camisama_seller = '[Por completar: razón social], con RUC [Por completar: RUC] y domicilio en [Por completar: dirección]';
$camisama_reach  = '[Por completar: correo y WhatsApp de contacto]';

$camisama_privacy = camisama_legal_page(
	(int) get_option( 'wp_page_for_privacy_policy' ),
	'Política de privacidad',
	'politica-de-privacidad',
	camisama_legal_blocks(
		array(
			"Camisama es de {$camisama_seller}. Aquí te contamos qué datos usamos y para qué.",
			'## Qué datos usamos',
			array(
				'<strong>Tus datos de pedido:</strong> nombre, correo, teléfono y dirección, para preparar y entregar tu compra y avisarte de cómo va.',
				'<strong>Tus diseños:</strong> los textos e imágenes que subes, para imprimirlos.',
				'<strong>Tu sesión:</strong> cookies necesarias para que funcionen el carrito y tu cuenta.',
			),
			'## Tus imágenes y tu borrador',
			array(
				'Las imágenes que subes se guardan sin sus datos ocultos, como la ubicación.',
				'Las que no acaban en un pedido se borran a los 30 días. Las de un pedido se conservan con él.',
				'El diseño a medias se guarda solo en tu dispositivo y caduca a los 7 días.',
			),
			'## Con quién los compartimos',
			'Solo con quien hace falta para atender tu pedido: [Por completar: proveedor de alojamiento, pasarela de pago y reparto]. No vendemos tus datos.',
			'## Tus derechos',
			"Puedes pedir acceso, rectificación, cancelación u oposición al tratamiento de tus datos, conforme a la Ley N.º 29733 de Protección de Datos Personales, escribiendo a {$camisama_reach}. Conservamos los datos de los pedidos el tiempo que exige la ley: [Por completar: plazo].",
		)
	)
);

$camisama_refunds = camisama_legal_page(
	function_exists( 'wc_get_page_id' ) ? max( 0, (int) wc_get_page_id( 'refund_returns' ) ) : 0,
	'Cambios y devoluciones',
	'cambios-y-devoluciones',
	camisama_legal_blocks(
		array(
			'Cada prenda se imprime para ti, a partir de tu diseño. Por eso conviene revisar el diseño, la talla y el color antes de pagar: el editor te avisa si algo puede verse mal, y "¿Qué talla?" te da las medidas.',
			'## Si llega con un problema',
			'Si la prenda tiene un defecto, la impresión salió mal o no coincide con lo que pediste, la cambiamos o te devolvemos el dinero, sin costo para ti.',
			"Escríbenos a {$camisama_reach} dentro de [Por completar: plazo en días] con tu número de pedido y una foto.",
			'## Si cambias de idea',
			'[Por completar: decidir si se aceptan cambios de talla o de diseño en prendas ya impresas, y en qué condiciones.]',
			'## Antes de imprimir',
			'Si aún no hemos empezado a imprimir, puedes cancelar tu pedido escribiéndonos. [Por completar: cómo y hasta cuándo.]',
			'## Cómo te devolvemos el dinero',
			'[Por completar: medio y plazo de devolución.]',
		)
	)
);

$camisama_terms = camisama_legal_page(
	function_exists( 'wc_get_page_id' ) ? max( 0, (int) wc_get_page_id( 'terms' ) ) : 0,
	'Términos y condiciones',
	'terminos-y-condiciones',
	camisama_legal_blocks(
		array(
			"Esta tienda es de {$camisama_seller}. Al comprar, aceptas estas condiciones.",
			'## Tu pedido',
			'Vendemos prendas personalizadas, desde una unidad. Lo que ves en el editor es una vista previa: el color impreso puede variar un poco según la prenda y la pantalla.',
			'## Precios',
			'Los precios están en soles (S/) e incluyen IGV. El total es la prenda, más la impresión de cada zona según su tamaño, más los estampados con recargo y la entrega, si corresponde. Lo ves antes de pagar.',
			'## Pago',
			'[Por completar: medios de pago aceptados.] Preparamos tu pedido cuando se confirma el pago.',
			'## Entrega',
			'Entregamos en Lima Metropolitana y el Callao, o puedes recoger tu pedido en nuestro taller. Tarifas por distrito, dirección y horario: [Por completar]. Plazo de preparación y entrega: [Por completar].',
			'## Tus diseños',
			'Eres responsable de lo que subes: debes tener derecho a usar las imágenes, textos, marcas y fotos de otras personas. Podemos rechazar un diseño si infringe derechos de terceros o la ley, y en ese caso te devolvemos tu dinero.',
			'## Cambios y devoluciones',
			'Se rigen por nuestra página de <a href="/cambios-y-devoluciones/">Cambios y devoluciones</a>. Tus datos se tratan según nuestra <a href="/politica-de-privacidad/">Política de privacidad</a>.',
			'## Reclamos',
			'[Por completar: enlace al Libro de Reclamaciones virtual.] Se aplica la ley peruana, incluido el Código de Protección y Defensa del Consumidor.',
		)
	)
);
update_option( 'woocommerce_terms_page_id', $camisama_terms );

camisama_legal_page(
	0,
	'Contacto',
	'contacto',
	camisama_legal_blocks(
		array(
			'¿Tienes dudas sobre tu diseño o tu pedido? Escríbenos.',
			array(
				'<strong>Correo:</strong> [Por completar]',
				'<strong>WhatsApp:</strong> [Por completar]',
				'<strong>Taller:</strong> [Por completar: dirección y horario de atención]',
			),
			'Si ya tienes un pedido, indícanos su número para ayudarte más rápido.',
		)
	)
);

// Lo que ve quien paga por transferencia: los datos llegan por correo mientras no haya pasarela.
$camisama_bacs = (array) get_option( 'woocommerce_bacs_settings', array() );
update_option(
	'woocommerce_bacs_settings',
	array_merge(
		$camisama_bacs,
		array( 'instructions' => 'Te enviaremos los datos de la cuenta a tu correo. Al hacer la transferencia, indica tu número de pedido.' )
	)
);

flush_rewrite_rules();

WP_CLI::success( "Páginas de confianza listas (privacidad: {$camisama_privacy}, cambios: {$camisama_refunds}, términos: {$camisama_terms})." );

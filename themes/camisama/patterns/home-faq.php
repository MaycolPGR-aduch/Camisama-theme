<?php
/**
 * Title: Portada: preguntas antes de pedir
 * Slug: camisama/home-faq
 * Categories: text
 *
 * @package Camisama
 */

?>
<?php
$camisama_questions = array(
	array( __( '¿Puedo pedir una sola camiseta?', 'camisama' ), __( 'Sí. Puedes personalizar desde una unidad. Elige el modelo, el color y las zonas que quieras estampar.', 'camisama' ) ),
	array( __( '¿Tengo que saber diseñar?', 'camisama' ), __( 'No necesitas preparar un archivo de diseño. Puedes empezar con un estampado del catálogo, escribir un texto o subir una imagen. Verás una vista previa sobre la prenda.', 'camisama' ) ),
	array( __( '¿Qué tipo de foto puedo subir?', 'camisama' ), __( 'PNG, JPG o WEBP de hasta 15 MB. Usa la imagen original y evita capturas de pantalla: el editor te avisará si pierde calidad al ampliarla.', 'camisama' ) ),
	array( __( '¿Cómo elijo mi talla?', 'camisama' ), __( 'En la ficha de cada modelo encontrarás “¿Qué talla?” con ancho y largo de referencia. Compáralos con una camiseta que ya te quede bien antes de elegir.', 'camisama' ) ),
	array( __( '¿Qué es la impresión DTF?', 'camisama' ), __( 'Es una técnica que transfiere tu diseño a la tela mediante calor. Permite imprimir estampados, letras y fotos a todo color, también sobre prendas oscuras.', 'camisama' ) ),
	array( __( '¿Puedo revisar mi diseño antes de comprar?', 'camisama' ), __( 'Sí. Puedes ver frente y espalda, revisar el total y editar el diseño desde el carrito antes de finalizar la compra.', 'camisama' ) ),
	array( __( '¿Dónde entregan y cuándo llegará?', 'camisama' ), __( 'La entrega está prevista para Lima Metropolitana y el Callao, con opción de recojo en taller. Revisa las opciones y el costo que aparezcan al finalizar la compra. Confirma el plazo y los datos de recojo con nosotros antes de pedir.', 'camisama' ) ),
);
?>
<!-- wp:html -->
<section id="ayuda" class="camisama-home-section camisama-faq alignfull" aria-labelledby="camisama-faq-title">
<div class="camisama-home-wrap camisama-faq__layout">
<div class="camisama-faq__intro"><h2 id="camisama-faq-title"><?php esc_html_e( 'Antes de hacerla tuya.', 'camisama' ); ?></h2><p><?php esc_html_e( 'Las respuestas para empezar con tu idea clara.', 'camisama' ); ?></p><a class="camisama-text-link" href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>"><?php esc_html_e( 'Tengo otra pregunta', 'camisama' ); ?></a></div>
<div class="camisama-faq__list">
<?php foreach ( $camisama_questions as $camisama_question ) : ?>
<details><summary><?php echo esc_html( $camisama_question[0] ); ?></summary><p><?php echo esc_html( $camisama_question[1] ); ?></p></details>
<?php endforeach; ?>
<p class="camisama-home-note"><a href="<?php echo esc_url( camisama_wc_page_url( 'refund_returns' ) ); ?>"><?php esc_html_e( 'Consulta cambios y devoluciones', 'camisama' ); ?></a></p>
</div>
</div>
</section>
<!-- /wp:html -->

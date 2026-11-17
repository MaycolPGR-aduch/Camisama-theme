<?php
/**
 * Formulario de "Sigue tu pedido" (sobrescribe order/form-tracking.php de WooCommerce 7.0.1).
 *
 * Mismos campos, ganchos y nonce que el original. Cambia lo que se nota en el móvil: los campos van
 * uno bajo otro, el correo es de tipo email con autocompletado y el número de pedido abre el
 * teclado numérico. Si WooCommerce cambia su plantilla, revisar esta.
 *
 * @package Camisama
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $post;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Solo se repite lo que escribió; el shortcode verifica el nonce.
$camisama_order_id    = isset( $_REQUEST['orderid'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderid'] ) ) : '';
$camisama_order_email = isset( $_REQUEST['order_email'] ) ? sanitize_email( wp_unslash( $_REQUEST['order_email'] ) ) : '';
// phpcs:enable
?>
<form action="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" method="post" class="woocommerce-form woocommerce-form-track-order track_order camisama-track-form">

	<?php
	/** Inicio del formulario (WooCommerce 6.5.0). */
	do_action( 'woocommerce_order_tracking_form_start' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Gancho de WooCommerce.
	?>

	<p><?php esc_html_e( 'Escribe tu número de pedido y el correo con el que compraste. El número está en la confirmación del pedido y en el correo que te enviamos.', 'camisama' ); ?></p>

	<p class="form-row">
		<label for="orderid"><?php esc_html_e( 'Número de pedido', 'camisama' ); ?></label>
		<input class="input-text" type="text" inputmode="numeric" name="orderid" id="orderid" value="<?php echo esc_attr( $camisama_order_id ); ?>" placeholder="<?php esc_attr_e( 'Por ejemplo: 176', 'camisama' ); ?>" required />
	</p>
	<p class="form-row">
		<label for="order_email"><?php esc_html_e( 'Correo con el que compraste', 'camisama' ); ?></label>
		<input class="input-text" type="email" autocomplete="email" name="order_email" id="order_email" value="<?php echo esc_attr( $camisama_order_email ); ?>" placeholder="<?php esc_attr_e( 'nombre@ejemplo.com', 'camisama' ); ?>" required />
	</p>

	<?php
	/** Antes del botón (WooCommerce 6.5.0). */
	do_action( 'woocommerce_order_tracking_form' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Gancho de WooCommerce.
	?>

	<p class="form-row"><button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="track" value="<?php esc_attr_e( 'Ver cómo va', 'camisama' ); ?>"><?php esc_html_e( 'Ver cómo va', 'camisama' ); ?></button></p>
	<?php wp_nonce_field( 'woocommerce-order_tracking', 'woocommerce-order-tracking-nonce' ); ?>

	<?php
	/** Fin del formulario (WooCommerce 6.5.0). */
	do_action( 'woocommerce_order_tracking_form_end' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Gancho de WooCommerce.
	?>

</form>

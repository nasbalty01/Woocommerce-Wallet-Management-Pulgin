<?php
defined('ABSPATH') || exit;

// Check the nonce for security
$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
	wp_send_json_error(array(
		'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
	));
	wp_die();
}

$addf_products_id = $this->get_id_of_wallet_product();

// Add the product to the cart
$wallet_amount = isset( $_POST['wallet_amount'] ) ? sanitize_text_field( $_POST['wallet_amount'] ) : '';
$quantity = 1;
$price = $wallet_amount;

// Empty the cart
WC()->cart->empty_cart();

// Add the new product to the cart
$cart_item_data = array(
	'custom_quantity' => $quantity,
	'custom_price' => $price,
);
$cart_item_key = WC()->cart->add_to_cart( $addf_products_id, $quantity, 0, array(), $cart_item_data );


// Send a response
if ( $cart_item_key ) {
	$response = array(
		'message' => esc_html__('Wallet has been added to your cart.', 'woo_addf_wm'),
		'cart_url' => wc_get_cart_url(),
	);
	wp_send_json_success( $response );
} else {
	wp_send_json_error(array(
		'message' => esc_html__('Error adding wallet to cart.', 'woo_addf_wm'),
	));
}

wp_die();

<?php
defined('ABSPATH') || exit;
class Addify_Wallet_Management_Front {

	public function __construct() {
		// Add css and js
		add_action('wp_enqueue_scripts', array( $this, 'addf_wm_enqueue_scripts_front' ));

		
		// 2. Add new query var
		add_filter( 'query_vars', array( $this, 'addf_wallet_query_vars' ), 0 ); 

		// 3. Insert the new endpoint into the My Account menu
		add_filter( 'woocommerce_account_menu_items', array( $this, 'addf_add_new_wallet_tab' ));

		// 4. Add content to the added tab
		add_action( 'woocommerce_account_customer-wallet_endpoint', array( $this, 'addf_wallet_tab_content' )); // Note: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format
		
		// After ajax call at to cart products show the custom price from offer page to cart page
	   add_action( 'woocommerce_before_calculate_totals', array( $this, 'addf_add_custom_data_in_cart' ));

	   //prevent a specific product(wallet) from being displayed on the frontend 
	   add_action('pre_get_posts', array( $this, 'addf_exclude_product_wallet_from_query' ));

	   //To hide the quantity field on the cart page for a specific product(wallet), 
	   add_filter( 'woocommerce_cart_item_quantity', array( $this, 'addf_hide_quantity_field_for_specific_product' ), 10, 3 );

	   add_filter('woocommerce_add_to_cart_validation', array( $this, 'addf_restrict_product_add_to_cart' ), 10, 3);

	   // Add custom data to the order based on product name
		// add_action('woocommerce_checkout_create_order', array( $this, 'addf_add_custom_order_meta_data'));

	   // Hide  wallet payment when the cart contain product wallet
		add_filter('woocommerce_available_payment_gateways', array( $this, 'addf_hide_wallet_payment_method' ));

		// show the cashback message on shop page
		add_action('woocommerce_after_shop_loop_item_title', array( $this, 'addf_custom_message_on_shop' ));

		// show the cashback message on product detail page
		add_action('woocommerce_single_product_summary', array( $this, 'addf_custom_message_on_product_detail' ), 25);

		// show the cashback message on cart page
		add_action('woocommerce_before_cart', array( $this, 'addf_custom_message_on_cart' ), 10);
	}

	public function addf_wm_enqueue_scripts_front() {
	
			wp_enqueue_style('addf_wm_front_css', ADDF_WM_URL . '/assets/css/addf_wm_front.css', array(), '1.0');
			wp_enqueue_script('addf_wm_front_js', ADDF_WM_URL . '/assets/js/addf_wm_front.js', array( 'jquery' ), '1.0', false);
			

			// Prepare translation strings
			$translations = array(
				'empty_message' => __('Enter a valid numeric amount to recharge your wallet.', 'wm_woo_addf_wm'),
				'min_message' => __('The minimum amount you can credit to your wallet is ', 'wm_woo_addf_wm'),
				'max_message' => __('The maximum amount you can credit to your wallet is ', 'wm_woo_addf_wm'),
				'recharge_message' => __('There is not enough wallet credit in the global wallet to make the request.', 'wm_woo_addf_wm'),
			);
			//currency symbols

			$currency_symbol = get_woocommerce_currency_symbol();
		   

		wp_localize_script('addf_wm_front_js', 'php_var', array( 
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('addf_wm_nonce_ajax'),
			'wallet_add_trnslation' => $translations,
			'currency_symbol' => $currency_symbol,
		));
	}

	

	public function addf_custom_message_on_cart() {
	
		$args = af_get_created_post(array( 'post_type' => 'cashback_rule' ) , 'cart');
	

		if ( !empty( $args ) ) {
		
			$rule_id = current( $args );
			 $cart_message = get_post_meta( $rule_id, 'cart_message', true);
		
			if ($cart_message) {
				echo ' <div class="woocommerce-message" role="alert">' . esc_html($cart_message) . '</div>';
			}

		}
	}

	public function addf_custom_message_on_shop() {
		global $product;
		
			$args = af_get_created_post(array( 'post_type' => 'cashback_rule' ) , 'product' , $product);

		if ( !empty( $args ) ) {
			$this->get_rule_message( current( $args ) , $product );
			 return;
		}

		if ($product->is_type('variable')) {
			$variation_ids = $product->get_children();
			foreach ( $variation_ids as $variation_id ) {
	
			 $args = af_get_created_post(array( 'post_type' => 'cashback_rule' ) , 'product' , $variation_id);
	


				if ( !empty( $args ) ) {
			  $this->get_rule_message( current( $args ) , $variation_id );
			  break;
				}
  
			}
		
		
		}
	}
	
	public function addf_custom_message_on_product_detail() {


		global $product;

		
		
			$args = af_get_created_post(array( 'post_type' => 'cashback_rule' ) , 'product' , $product);
   
		if ( !empty( $args ) ) {
			$this->get_rule_message( current( $args ) , $product );
		}

		
		if ($product->is_type('variable')) {
			$variation_ids = $product->get_children();
			foreach ( $variation_ids as $variation_id ) {
	
			 $args = af_get_created_post(array( 'post_type' => 'cashback_rule' ) , 'product' , $variation_id);

				if ( !empty( $args ) ) {
			  $this->get_rule_message( current( $args ) , $variation_id );
				}
  
			}
		
		
		}
	}

	public function get_rule_message( $rule_id, $product = '' ) {

		$custom_cashback_message_color = get_option('custom_cashback_message_color', '#0f0f0f');
		$custom_cashback_message_border_color = get_option('custom_cashback_message_border_color', '#d6d6d6');
		$custom_cashback_message_bg_color = get_option('custom_cashback_message_bg_color', '#ffffff');
		$custom_cashback_message_padding = get_option('custom_cashback_message_padding', '12px');
		$custom_cashback_message_border_width = get_option('custom_cashback_message_border_width', '1px');
		$custom_cashback_message_border_radius = get_option('custom_cashback_message_border_radius', '0px');
		$custom_cashback_message_text_align = get_option('custom_cashback_message_text_align', 'center');
		$custom_cashback_message_text_size = get_option('custom_cashback_message_text_size', '16px');
		$custom_cashback_message_text_font = get_option('custom_cashback_message_text_font', 'normal');
		$custom_cashback_message_font_weight = get_option('custom_cashback_message_font_weight', 'normal');

		$product = is_object( $product ) ? $product  : wc_get_product($product);
		$product_id =  $product ? $product->get_id() : 0;

	

		$products_message = get_post_meta($rule_id, 'products_message', true);

		echo '<p class="custom-price-message af-wallet-custom-price-message custom-price-message-' . esc_attr( $product_id ) . '" style="color: ' . esc_attr( $custom_cashback_message_color ) . '; border-color: ' . esc_attr( $custom_cashback_message_border_color ) . '; background-color: ' . esc_attr( $custom_cashback_message_bg_color ) . '; padding: ' . esc_attr( $custom_cashback_message_padding ) . '; border-width: ' . esc_attr( $custom_cashback_message_border_width ) . '; border-radius: ' . esc_attr( $custom_cashback_message_border_radius ) . '; text-align: ' . esc_attr( $custom_cashback_message_text_align ) . '; font-size: ' . esc_attr( $custom_cashback_message_text_size ) . '; font-style: ' . esc_attr( $custom_cashback_message_text_font ) . '; font-weight: ' . esc_attr( $custom_cashback_message_font_weight ) . ';">';
		echo wp_kses_post( $products_message );
		echo ' ';

		if ( !empty( $product ) ) {
			echo esc_html( $product->get_name() );
		}

		echo '</p>';
	}
	
	
	

	public function addf_exclude_product_wallet_from_query( $query ) {
		if (!is_admin() && $query->is_main_query() && ( is_shop() || is_product_category() || is_product_tag() )) {
			$addf_main_class = new Addify_Wallet_Management();  
			$addf_products_id = $addf_main_class->get_id_of_wallet_product();
	
			// Ensure a valid product ID is returned
			if (!empty($addf_products_id)) {
				// Get existing post type and add a filter to exclude the product
				$query->set('post__not_in', array( $addf_products_id ));
			}
		}
	}
	

	public function addf_hide_quantity_field_for_specific_product( $product_quantity, $cart_item_key, $cart_item ) {
		$addf_main_class = new Addify_Wallet_Management();  
		$addf_products_id = $addf_main_class->get_id_of_wallet_product();
		// Get the product ID for the cart item
		$product_id = $cart_item['product_id'];
	
		// Check if the product ID matches the specific product ID for which you want to hide the quantity field
		if ( $product_id == $addf_products_id ) { // 
			return '';
		}
	
		// Return the original quantity field if the product ID does not match
		return $product_quantity;
	}

	public function addf_restrict_product_add_to_cart( $passed, $product_id, $quantity ) {

		// Check if the cart contains a product with the name 'Wallet'
		if (WC()->cart->get_cart_contents_count() > 0) {
			foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
				// Check if the cart item name contains 'Wallet'
				if (strpos($cart_item['data']->get_name(), 'Wallet') !== false) {
					// If 'Wallet' is found in cart, prevent adding the product to cart
					wc_add_notice(__('Cannot add new product now. Either empty cart or process it first.', 'woo_addf_wm'), 'error');
					return false;
				}
			}
		}

		return $passed;
	}

	

	public function addf_add_custom_data_in_cart( $cart_object ) {
		// Define the custom image callback function
		$custom_image_callback = function ( $image, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['custom_price'] ) ) {
				$custom_image_url = ADDF_WM_URL . 'assets/images/wallet.png';
				return '<img src="' . esc_url( $custom_image_url ) . '" alt="Wallet Image" />';
			}
			return $image; // Return original image if no custom price is set
		};
	
		// Loop through cart items
		foreach ( $cart_object->cart_contents as $cart_item_key => $cart_item ) {
			// Check if custom price is set
			if ( isset( $cart_item['custom_price'] ) ) {
				// Set custom price
				$cart_item['data']->set_price( $cart_item['custom_price'] );
				// Set custom image filter
				add_filter( 'woocommerce_cart_item_thumbnail', $custom_image_callback, 10, 3 );
			}
		}
	}
	
	
	  
	 
	public function addf_wallet_query_vars( $vars ) {
		$vars[] = 'customer-wallet';
		return $vars;
	}
	  
	
	public function addf_add_new_wallet_tab( $items ) {
		// Add 'My Wallet' tab after 'Edit Account' tab
		$new_items = array();
		foreach ( $items as $key => $value ) {
			$new_items[ $key ] = $value;
			if ( 'edit-account' === $key ) {
				$new_items['customer-wallet'] = 'My Wallet';
			}
		}
		// Add 'My Wallet' tab before 'Logout' tab
		$new_items = array_merge(
			array_slice( $new_items, 0, array_search( 'customer-logout', array_keys( $new_items ) ) ),
			array( 'customer-wallet' => 'My Wallet' ),
			array_slice( $new_items, array_search( 'customer-logout', array_keys( $new_items ) ), count( $new_items ) )
		);
		return $new_items;
	}

	public function get_customer_order() {
		  // Get the current user ID
			$user_id = get_current_user_id();

			// Get orders for the current user with the status "completed"
			$addf_orders = wc_get_orders( array(
				'customer_id' => $user_id,
				'status'      => 'completed', // Only get orders with status "completed"
			) );

			// Initialize an empty array to store order IDs
			$order_ids_addf = array();

			// Loop through each order
		foreach ($addf_orders as $order ) {
			// Get items in the order
			$items = $order->get_items();

			// Loop through each item in the order
			foreach ( $items as $item ) {
				// Check if the product name contains "wallet"
				if ( stripos( $item->get_name(), 'wallet' ) !== false ) {
					// If the product name contains "wallet," store the order ID
					$order_ids_addf[] = $order->get_id();
					break; // Break the inner loop, as we only need to check one item per order
				}
			}
		}
	return $order_ids_addf;
	}
	  
	
	
	public function addf_wallet_tab_content() {

	  $order_ids_addf = $this->get_customer_order();
	  // Get the value of the customer-wallet variable from the URL
		$wallet_view = get_query_var( 'customer-wallet' );
	

		if ( 0 === strpos( $wallet_view, 'view/' )) {
			// If the variable starts with 'view/', extract the view parameter
			$view_params = explode( '/', $wallet_view );
			$view = $view_params[1];

			// Display the content based on the view parameter
			include_once ADDF_WM_DIR . 'includes/front/views/addify-wallet-tab-content_view.php';
		} elseif ( 0 === strpos( $wallet_view, 'transfer' )) {
			
			// Display the content based on the view parameter
			
			include_once ADDF_WM_DIR . 'includes/front/views/addify-wallet-tab-transfer.php';
		} else {
			// Invalid URL format
			include_once ADDF_WM_DIR . 'includes/front/views/addify-wallet-tab-content.php';
		}
	}
	
	// 6. Go to Settings >> Permalinks and re-save permalinks. Otherwise, you will end up with a 404 Page not found error

 

   
	



	public function addf_hide_wallet_payment_method( $available_gateways ) {
		global $woocommerce;
	
		// Check if "Wallet" product is in the cart
		$is_wallet_product_in_cart = false;
		foreach ($woocommerce->cart->get_cart() as $cart_item_key => $values) {
			$_product = $values['data'];
			if ($_product->get_name() == 'Wallet') {
				$is_wallet_product_in_cart = true;
				break;
			}
		}
	
		// If "Wallet" product is in the cart, unset the "wallet_payment" gateway
		if ($is_wallet_product_in_cart) {
			unset($available_gateways['wallet_payment']);
		}
	
		return $available_gateways;
	}
}

new Addify_Wallet_Management_Front();

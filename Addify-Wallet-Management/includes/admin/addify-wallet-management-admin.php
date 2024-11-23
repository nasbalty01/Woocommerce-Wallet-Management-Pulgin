<?php
defined('ABSPATH') || exit;

class Addify_Wallet_Management_Admin {
	public $nonce;

	public function __construct() {
		$this->nonce = wp_create_nonce('addf_wm_nonce');
		global $addify_wallet_management_nonce;
		$addify_wallet_management_nonce = $this->nonce;

		// Add CSS and script
		add_action('admin_enqueue_scripts', array( $this, 'addf_wm_Admin_enqueue_scripts' ));
		include_once ADDF_WM_DIR . 'includes/admin/menu-rule-metaboxes/addify-wallet-management-menu-rule-metaboxes.php';

		// Remove the 'Edit', 'Trash', 'View', and 'Duplicate' actions from the row actions list for the product with specific ID.
		add_filter('post_row_actions', array( $this, 'adff_disable_product_actions' ), 15, 2);
		add_action('woocommerce_order_status_changed', array( $this, 'addf_custom_status_change_function' ), 10, 4);
	}

	public function addf_wm_Admin_enqueue_scripts() {
		wp_enqueue_style('addf_wm_admin_css', ADDF_WM_URL . 'assets/css/addf_wm_admin.css', array(), '1.0');
		wp_enqueue_script('addf_wm_admin_js', ADDF_WM_URL . 'assets/js/addf_wm_admin.js', array( 'jquery' ), '1.0', false);

		// Enqueue Select2 JS CSS.
		wp_enqueue_style('select2', plugins_url('assets/css/select2.css', WC_PLUGIN_FILE), array(), '5.7.2');
		wp_enqueue_script('select2', plugins_url('assets/js/select2/select2.min.js', WC_PLUGIN_FILE), array( 'jquery' ), '4.0.3', true);
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('custom-admin-script', plugins_url('/js/custom-admin-script.js', __FILE__), array( 'wp-color-picker', 'jquery' ), null, true);

		wp_localize_script('addf_wm_admin_js', 'php_var', array(
			'nonce' => wp_create_nonce('addf_wm_nonce_ajax'),
		));
	}

	public function adff_disable_product_actions( $actions, $post ) {
		$addf_main_class = new Addify_Wallet_Management();  
		$addf_products_id = $addf_main_class->get_id_of_wallet_product();

		if ($post && $post->ID == $addf_products_id) {
			unset($actions['edit']);
			unset($actions['trash']);
			unset($actions['view']);
			unset($actions['duplicate']);
			unset($actions['inline hide-if-no-js']); // Quick Edit
			?>
			<script type="text/javascript">
				jQuery('table.wp-list-table a.row-title').contents().unwrap();
			</script>
			<?php
		}
		return $actions;
	}

	public function addf_custom_status_change_function( $order_id, $old_status, $new_status, $order ) {
		$selected_statuses = get_option('order_statuses_select', '');
		foreach ($selected_statuses as $key => $status) {
			$selected_statuses[ $key ] = str_replace('wc-', '', $status);
		}

		if (empty($selected_statuses) || in_array($new_status, $selected_statuses)) {
			$order = wc_get_order($order_id);
			$user_id = $order->get_user_id();

			// Get user role
			$user = get_userdata($user_id);
			$user_roles = $user ? current($user->roles) : '';

			
			$add_total_cash_amount = 0;

			foreach ($order->get_items() as $item_id => $item) {
				$product_name = $item->get_name();
				if (str_contains($product_name, 'Wallet')) {
					$recharge_amount_expiry_days = get_option('expiry_date', '');
					$current_date = new DateTime();
					$current_date->modify('+' . (int) $recharge_amount_expiry_days . ' days');

					$expiry_date_en_dis_option = get_option('expiry_date_en_dis_option', '');

					if ('yes' === $expiry_date_en_dis_option) {
						// Format the date as desired, e.g., 'Y-m-d'
						$recharge_amount_expiry_date = $current_date->format('Y-m-d');
					} else {
						$recharge_amount_expiry_date = '–';
					}
					

					$this->insert_data_on_order_completed($order_id, 'wallet', $item->get_subtotal(), $recharge_amount_expiry_date);

					$args_recharge = af_get_created_post(array( 'post_type' => 'cashback_rule' ), 'recharge', $item->get_subtotal(), $user_roles, $user_id);
					
					$rules0 = $args_recharge['rules'];
					$amount0 = $args_recharge['amount'];
					if (!empty($args_recharge)) {
						$rule_id0 = current($rules0);
						$cashback_for0 = get_post_meta($rule_id0, 'cashback_for', true);
						$cashback_amount0 = $amount0;
						$rule_end_date0 = get_post_meta($rule_id0, 'rule_end_date', true);
						$this->insert_data_on_order_completed($order_id, $cashback_for0, $cashback_amount0, $rule_end_date0);
					}
				} else {
					$products = wc_get_product($item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id());

					$args_product = af_get_created_post(array( 'post_type' => 'cashback_rule' ), 'product', $products, $user_roles, $user_id);
					
					// echo '<pre>';
					// print_r($args_product);
					$rules1 = $args_product['rules'];
					$amount1 = $args_product['amount'];
					

					$args_cart = af_get_created_post(array( 'post_type' => 'cashback_rule' ), 'cart', $order->get_subtotal(), $user_roles, $user_id);
					
					$rules2 = $args_cart['rules'];
					$amount2 = $args_cart['amount'];
					

					$args_last_order = af_get_created_post(array( 'post_type' => 'cashback_rule' ), 'last order', $order->get_subtotal(), $user_roles, $user_id);
					$rules3 = $args_last_order['rules'];
					$amount3 = $args_last_order['amount'];

					$args_purchase_history = af_get_created_post(array( 'post_type' => 'cashback_rule' ), 'purchase history', $order->get_subtotal(), $user_roles, $user_id);
					$rules4 = $args_purchase_history['rules'];
					$amount4 = $args_purchase_history['amount'];


					if (!empty($args_product)) {
						$rule_id1 = current($rules1);
						
						$cashback_for1 = get_post_meta($rule_id1, 'cashback_for', true);
						$cashback_amount1 = $amount1;
						
						$rule_end_date1 = get_post_meta($rule_id1, 'rule_end_date', true);
						$add_total_cash_amount += (float) $cashback_amount1;
					}
		
					if (!empty($args_cart)) {
						$rule_id2 = current($rules2);
						
						$cashback_for2 = get_post_meta($rule_id2, 'cashback_for', true);
						$cashback_amount2 = $amount2;
						$rule_end_date2 = get_post_meta($rule_id2, 'rule_end_date', true);
						$this->insert_data_on_order_completed($order_id, $cashback_for2, $cashback_amount2, $rule_end_date2);
					}

					if (!empty($args_last_order)) {
						$rule_id3 = current($rules3);
						$cashback_for3 = get_post_meta($rule_id3, 'cashback_for', true);
						$cashback_amount3 = $amount3;
						$rule_end_date3 = get_post_meta($rule_id3, 'rule_end_date', true);
						$this->insert_data_on_order_completed($order_id, $cashback_for3, $cashback_amount3, $rule_end_date3);
					}

					if (!empty($args_purchase_history)) {
						$rule_id4 = current($rules4);
						$cashback_for4 = get_post_meta($rule_id4, 'cashback_for', true);
						$cashback_amount4 = $amount4;
						$rule_end_date4 = get_post_meta($rule_id4, 'rule_end_date', true);
						$this->insert_data_on_order_completed($order_id, $cashback_for4, $cashback_amount4, $rule_end_date4);
					}
				}
			}

			if ($add_total_cash_amount >= 0.1) {
				$this->insert_data_on_order_completed($order_id, $cashback_for1, $add_total_cash_amount, $rule_end_date1);
			
			}
		}
	}

	public function insert_data_on_order_completed( $order_id, $cashback_for, $cashback_amount_before, $rule_end_date ) {

		$number_of_decimals = get_option('woocommerce_price_num_decimals');
		$decimal_separator = get_option('woocommerce_price_decimal_sep');

		// Format the amount
		$cashback_amount = number_format($cashback_amount_before, $number_of_decimals, $decimal_separator, '');
		
		global $wpdb;

		$existing_recharge_id = $wpdb->get_var('SELECT id FROM wm_recharge_amount');
		$recharge_amount_check = $wpdb->get_var('SELECT recharge_amount FROM wm_recharge_amount');
		$max_balance_en_dis_option =  $wpdb->get_var('SELECT max_balance_option FROM wm_recharge_amount');

		if ('yes' === $max_balance_en_dis_option) {
			$max_balance_option = 'on';
		} else {
			$max_balance_option = 'off';
		}

		if ($recharge_amount_check >= $cashback_amount || 'off' === $max_balance_option ) {

			$order = wc_get_order($order_id);
			$user_id = $order->get_user_id();

			$current_user = wp_get_current_user();
			$sender_email = $current_user->user_email;

			$user_info = get_userdata($user_id);
			$receiver_email = $user_info->user_email;
			$user_name = $user_info->user_login;

			$currency_symbol = get_woocommerce_currency_symbol();

			if ('products' === $cashback_for) {
				$origin = 'products';
				$amount = $cashback_amount;
				$reference = 'Cashback received on product purchase';
				$reference_email = 'product purchase';
				$notes = esc_html__('Order No: ' . $order_id, 'woo_addf_wm');
			} elseif ('cart' === $cashback_for) {
				$origin = 'cart';
				$amount = $cashback_amount;
				$reference = 'Cashback received on the based cart subtotal amount';
				$reference_email = 'cart subtotal amount';
				$notes = esc_html__('Order No: ' . $order_id, 'woo_addf_wm');
			} elseif ('last order' === $cashback_for) {
				$origin = 'last order';
				$amount = $cashback_amount;
				$reference = 'Cashback received on the based of last order total spent';
				$reference_email = 'last order total spent';
				$notes = esc_html__('Order No: ' . $order_id, 'woo_addf_wm');
			} elseif ('purchase history' === $cashback_for) {
				$origin = 'purchase history';
				$amount = $cashback_amount;
				$reference = 'Cashback received on the based of purchase history';
				$reference_email = 'purchase history total spent';
				$notes = esc_html__('Order No: ' . $order_id, 'woo_addf_wm');
			} elseif ('recharge' === $cashback_for) {
	
				$origin = 'recharge';
				$amount = $cashback_amount;
				$reference = 'Cashback received on the based wallet recharge';
				$reference_email = 'wallet recharge';
				$notes = esc_html__('Order No: ' . $order_id, 'woo_addf_wm');
			} elseif ('wallet' === $cashback_for) {
				$origin = 'wallet';
				$amount = $cashback_amount;
				$reference = 'Wallet credit on recharge';
				$notes = esc_html__('Order No: ' . $order_id . ' Wallet Transaction: ' . $currency_symbol . $amount, 'woo_addf_wm');
			}

			$customer_id = $user_id;
			$transaction_type = 'Credit';
			$transaction_action = 'Wallet Credit';
			$receiver_email = $receiver_email;
			$payer_email = $receiver_email;
			$transaction_note = $notes;
			$date = current_time('mysql');
			$statuses = 'Active';

			$existing_order_id = $wpdb->get_var($wpdb->prepare('SELECT order_id FROM wm_wallet_data WHERE order_id = %d', $order_id));
			$existing_origin = $wpdb->get_var($wpdb->prepare('SELECT origin FROM wm_wallet_data WHERE origin = %s', $origin));

			if ($existing_order_id == $order_id && $existing_origin == $origin) {
				$wpdb->update(
					'wm_wallet_data',
					array(
						'customer_id' => $customer_id,
						'reference' => $reference,
						'amount' => $amount,
						'transaction_type' => $transaction_type,
						'transaction_action' => $transaction_action,
						'receiver_email' => $receiver_email,
						'payer_email' => $payer_email,
						'transaction_note' => $transaction_note,
						'date' => $date,
						'expiry_date' => $rule_end_date,
						'status' => $statuses,
					),
					array(
						'order_id' => $order_id,
						'origin' => $origin,
					)
				);
			} else {
				$wpdb->insert(
					'wm_wallet_data',
					array(
						'order_id' => $order_id,
						'customer_id' => $customer_id,
						'reference' => $reference,
						'amount' => $amount,
						'transaction_type' => $transaction_type,
						'transaction_action' => $transaction_action,
						'receiver_email' => $receiver_email,
						'payer_email' => $payer_email,
						'transaction_note' => $transaction_note,
						'origin' => $origin,
						'date' => $date,
						'expiry_date' => $rule_end_date,
						'status' => $statuses,
					)
				);

				if ('on' === $max_balance_option) {
				$recharge_amount = $wpdb->get_var('SELECT recharge_amount FROM wm_recharge_amount');
				$total_amount_debited = $recharge_amount - $amount;
				$wpdb->update(
					'wm_recharge_amount',
					array(
						'recharge_amount' => $total_amount_debited,
						'recharge_action' => 'Debit',
						'date' => current_time('mysql'),
					),
					array( 'id' => $existing_recharge_id )
				);
				}

				$existing_customer_id = $wpdb->get_var($wpdb->prepare('SELECT customer_id FROM wm_wallet_amount WHERE customer_id = %d', $customer_id));
				$sum_total_amount = $wpdb->get_var($wpdb->prepare('SELECT SUM(amount) FROM wm_wallet_data WHERE customer_id= %d', $customer_id));

				if ($existing_customer_id) {
					$wpdb->update(
						'wm_wallet_amount',
						array(
							'total_amount' => $sum_total_amount,
							'date' => $date,
						),
						array( 'customer_id' => $customer_id )
					);
				} else {
					$wpdb->insert(
						'wm_wallet_amount',
						array(
							'customer_id' => $customer_id,
							'total_amount' => $sum_total_amount,
							'date' => $date,
						)
					);
				}
				// $this->send_casback_email($receiver_email, $user_name, $reference_email, $amount, $origin);
				$currency_symbol = get_woocommerce_currency_symbol();
				if ('wallet' === $origin) {
					WC()->mailer()->emails['Addify_recharge']->trigger($receiver_email, $user_name, $amount);
				} else {
					WC()->mailer()->emails['Addify_cashback']->trigger($receiver_email, $user_name, $reference_email, $amount);
				}
			

			}
		}
	}
}

new Addify_Wallet_Management_Admin();

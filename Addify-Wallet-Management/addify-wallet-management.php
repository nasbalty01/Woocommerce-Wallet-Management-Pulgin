<?php
/**
 * Plugin Name: Wallet Management For Woocommerce
 * Description:  Wallet management for woocommerce pulgin.
 * Plugin URI:        https://woocommerce.com/products/wallet-management-for-woocommerce/
 * Author:            Addify
 * Developed By:      Addify
 * Author URI:        https://woocommerce.com/vendor/addify/
 * Support:           https://woocommerce.com/vendor/addify/
 * Version: 1.1.0
 * Domain Path:       /languages
 * Text Domain:       woo_addf_wm
 * WC requires at least: 3.0.9
 * WC tested up to: 6.*.*
 * Woo: 8949002:f66e345faff9375c4a31c1f590248551
 *
 * @package woo_addf_wm
 */

if (!defined('ABSPATH')) {
	exit();
}

class Addify_Wallet_Management {



	public function __construct() {

		$this->addf_post_purchase_offer_global_constents_vars();
		add_action('plugin_loaded', array( $this, 'addf_wm_wc_check' ));
		add_action('before_woocommerce_init', array( $this, 'addf_wm_HOPS_Compatibility' ));
		add_action('init', array( $this, 'addf_wm_check_woocommerce_is_defined_or_not' ));

		register_activation_hook(__FILE__, array( $this, 'addf_plugin_activate' ));
		register_deactivation_hook(__FILE__, array( $this, 'addf_plugin_deactivate' ));

		register_activation_hook(__FILE__, array( $this, 'addf_create_wallet_table' ));
		register_deactivation_hook(__FILE__, array( $this, 'addf_delete_wallet_table' ));

		add_action('init', array( $this, 'addf_create_cashback_rule_post_type' ));

		// 1. Register new wallet endpoint (URL) for My Account page
		add_action( 'init', array( $this, 'addf_add_wallet_endpoint' ));
		add_action('after_switch_theme', array( $this, 'addf_flush_rewrite_rules_on_activation' ));
		add_action( 'wp_ajax_add_to_wallet_cart', array( $this, 'add_to_wallet_cart_callback' ));
		add_action( 'wp_ajax_nopriv_add_to_wallet_cart', array( $this, 'add_to_wallet_cart_callback' ));

		add_action('wp_ajax_send_otp', array( $this, 'addf_send_otp_callback' ));
		add_action('wp_ajax_nopriv_send_otp', array( $this, 'addf_send_otp_callback' ));
		add_action('wp_ajax_verify_otp', array( $this, 'addf_verify_otp_callback' ));
		add_action('wp_ajax_nopriv_verify_otp', array( $this, 'addf_verify_otp_callback' ));

		add_action('wp_ajax_create_order', array( $this, 'addf_create_order_callback' ));
		add_action('wp_ajax_nopriv_create_order', array( $this, 'addf_create_order_callback' ));
		add_action('wp_ajax_create_order_admin', array( $this, 'addf_create_order_admin_callback' ));
		add_action('wp_ajax_nopriv_create_order_admin', array( $this, 'addf_create_order_admin_callback' ));
		add_action('wp_ajax_create_recharge_admin', array( $this, 'addf_create_recharge_admin_callback' ));
		add_action('wp_ajax_nopriv_create_recharge_admin', array( $this, 'addf_create_recharge_admin_callback' ));

		add_filter('cron_schedules', array( $this, 'addf_custom_cron_schedules' ));

		add_action('wp', array( $this, 'addf_custom_cron_activation' ));

		// add_action('wp_footer', array( $this,  'addf_custom_update_wallet_status'));

		add_action('addf_custom_update_wallet_status_event', array( $this, 'addf_custom_update_wallet_status' ));

		add_filter( 'woocommerce_email_classes', array( $this, 'addf_email_send_include_clases' ), 90, 1 );
	}


	public function addf_email_send_include_clases( $emails ) {
		include ADDF_WM_DIR . 'email/class-addify-cashback-config.php';
		$emails['Addify_cashback']      = new Addify_Cashback();

		include ADDF_WM_DIR . 'email/class-addify-recharge-config.php';
		$emails['Addify_recharge']      = new Addify_Recharge();

		include ADDF_WM_DIR . 'email/class-addify-manually-admim-config.php';
		$emails['Addify_manually_admin']        = new Addify_Manually_Admin();

		include ADDF_WM_DIR . 'email/class-addify-transfer-sender-config.php';
		$emails['Addify_transfer_sender']       = new Addify_Transfer_Sender();

		include ADDF_WM_DIR . 'email/class-addify-transfer-reciver-config.php';
		$emails['Addify_transfer_reciver']      = new Addify_Transfer_Reciver();

		include ADDF_WM_DIR . 'email/class-addify-transfer-admin-config.php';
		$emails['Addify_transfer_admin']       = new Addify_Transfer_Admin();

		include ADDF_WM_DIR . 'email/class-addify-otp-transfer-config.php';
		$emails['Addify_otp_transfer']      = new Addify_OTP_Transfer();

		include ADDF_WM_DIR . 'email/class-addify-otp-payment-config.php';
		$emails['Addify_otp_payment']       = new Addify_OTP_Payment();
		
		return $emails;
	}

	// Add a custom interval of 5 minutes
	public function addf_custom_cron_schedules( $schedules ) {
		$schedules['every_five_minutes'] = array(
			'interval' => 300, // 300 seconds = 5 minutes
			'display' => __('Every 5 Minutes'),
		);
		return $schedules;
	}

	// Schedule the event if it hasn't been scheduled
	public function addf_custom_cron_activation() {
		if (!wp_next_scheduled('addf_custom_update_wallet_status_event')) {
			wp_schedule_event(time(), 'every_five_minutes', 'addf_custom_update_wallet_status_event');
		}
	}

	// Function to update wallet status
	public function addf_custom_update_wallet_status() {
		global $wpdb;

		// Get today's date
		$today_date = current_time('Y-m-d');

		

		// Fetch the sum of amounts per customer where expiry_date is today or earlier and status is Active.
		$expired_amounts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT customer_id, SUM(amount) as total_expired_amount 
				FROM wm_wallet_data 
				WHERE DATE(expiry_date) <= %s AND status = 'Active'
				GROUP BY customer_id",
				$today_date
			)
		);

		// Update status to "Expired" for records where expiry_date is today or earlier
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE wm_wallet_data SET status = 'Expired' WHERE DATE(expiry_date) <= %s",
				$today_date
			)
		);
	
		// Update the total_amount in wm_wallet_amount table for each customer
		foreach ($expired_amounts as $expired) {
			$wpdb->query(
				$wpdb->prepare(
					'UPDATE wm_wallet_amount 
					SET total_amount = total_amount - %d 
					WHERE customer_id = %d',
					$expired->total_expired_amount, $expired->customer_id
				)
			);
		}
		exit;
	}

	

	public function addf_delete_wallet_table() {
		global $wpdb;
	
		// Define table names with the WordPress prefix
		$tables = array(
			'wm_wallet_data',
			'wm_wallet_amount',
			'wm_recharge_amount',
		);
	
		// Drop tables
		foreach ($tables as $table_name) {
			$sql = "DROP TABLE IF EXISTS {$table_name}";
			$wpdb->query($sql);
		}
	}
	
	
	
	
	
	
	public function addf_create_wallet_table() {
		global $wpdb;
		
		$charset_collate = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
	
		$tables = array(
			'wm_wallet_data' => "CREATE TABLE `wm_wallet_data` (
				id INT NOT NULL AUTO_INCREMENT,
				customer_id INT NOT NULL,
				reference VARCHAR(255) NOT NULL,
				amount DECIMAL(10,2) NOT NULL,
				transaction_type VARCHAR(255) NOT NULL,
				transaction_action VARCHAR(255) NOT NULL,
				transaction_note VARCHAR(255) NOT NULL,
				receiver_email VARCHAR(255) NOT NULL,
				payer_email VARCHAR(255) NOT NULL,
				order_id INT NOT NULL,
				origin VARCHAR(255) NOT NULL,
				date DATETIME NOT NULL,
				expiry_date VARCHAR(255) NOT NULL,
				status VARCHAR(255) NOT NULL,
				action INT NOT NULL DEFAULT 1,
				PRIMARY KEY (id)
			) $charset_collate;",
	
			'wm_wallet_amount' => "CREATE TABLE `wm_wallet_amount` (
				id INT NOT NULL AUTO_INCREMENT,
				customer_id INT NOT NULL,
				total_amount DECIMAL(10,2) NOT NULL,
				date DATETIME NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;",
	
			'wm_recharge_amount' => "CREATE TABLE `wm_recharge_amount` (
				id INT NOT NULL AUTO_INCREMENT,
				recharge_amount DECIMAL(10,2) NOT NULL,
				max_balance_option VARCHAR(255) NOT NULL,
				recharge_action VARCHAR(255) NOT NULL,
				date DATETIME NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;",
		);
	
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	
		foreach ($tables as $sql) {
			dbDelta($sql);
		}
	}
	

	// public function addf_create_wallet_table() {
	//  global $wpdb;
	
	//  $table_name = 'wm_wallet_data';
	
	//  $charset_collate = $wpdb->get_charset_collate();
	
	//  $sql_wallet_data = "CREATE TABLE $table_name (
	//      id INT NOT NULL AUTO_INCREMENT,
	//      customer_id INT NOT NULL,
	//      reference VARCHAR(255) NOT NULL,
	//      amount DECIMAL(10,2) NOT NULL,
	//      transaction_type VARCHAR(255) NOT NULL,
	//      transaction_action VARCHAR(255) NOT NULL,
	//      transaction_note VARCHAR(255) NOT NULL,
	//      receiver_email VARCHAR(255) NOT NULL,
	//      payer_email VARCHAR(255) NOT NULL,
	//      order_id INT NOT NULL,
	//      origin VARCHAR(255) NOT NULL,
	//      date DATETIME NOT NULL,
	//      expiry_date VARCHAR(255) NOT NULL,
	//      status VARCHAR(255) NOT NULL,
	//      action INT NOT NULL DEFAULT 1,
	//      PRIMARY KEY (id)
	//  ) $charset_collate;";
	
	//  require_once ABSPATH . 'wp-admin/includes/upgrade.php' ;
	//  dbDelta( $sql_wallet_data );

	//  $table_name = 'wm_wallet_amount';
	
	//  $charset_collate = $wpdb->get_charset_collate();
	
	//  $sql_wallet_amount = "CREATE TABLE $table_name (
	//      id INT NOT NULL AUTO_INCREMENT,
	//      customer_id INT NOT NULL,
	//      total_amount DECIMAL(10,2) NOT NULL,
	//      date DATETIME NOT NULL,
	//      PRIMARY KEY (id)
	//  ) $charset_collate;";
	
	//  require_once ABSPATH . 'wp-admin/includes/upgrade.php' ;
	//  dbDelta( $sql_wallet_amount );

	//  $table_name = 'wm_recharge_amount';
	
	//  $charset_collate = $wpdb->get_charset_collate();
	
	//  $sql_recharge = "CREATE TABLE $table_name (
	//      id INT NOT NULL AUTO_INCREMENT,
	//      recharge_amount DECIMAL(10,2) NOT NULL,
	//      max_balance_option VARCHAR(255) NOT NULL,
	//      recharge_action VARCHAR(255) NOT NULL,
	//      date DATETIME NOT NULL,
	//      PRIMARY KEY (id)
	//  ) $charset_collate;";
	
	//  require_once ABSPATH . 'wp-admin/includes/upgrade.php' ;
	//  dbDelta( $sql_recharge );
	// }

	// Register a custom post type for Cashback Rules
	public function addf_create_cashback_rule_post_type() {
		$labels = array(
			'name' => 'Cashback Rules',
			'singular_name' => 'Cashback Rule',
			'menu_name' => 'Wallet System',
			'add_new'   => 'Add New Cashback Rule',
			'add_new_item' => 'Add New Cashback Rule',
			'edit_item' => 'Edit Cashback Rule',
			'view_item' => 'View Cashback Rule',
			'search_items' => 'Search Cashback Rules',
			'not_found' => 'No cashback rules found',
			'not_found_in_trash' => 'No cashback rules found in trash',
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'has_archive' => false,
			'show_ui' => true,
			'show_in_menu' => 'woocommerce',
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'title', 'page-attributes' ),
			'menu_icon' => 'dashicons-money',
		);

		register_post_type('cashback_rule', $args);
	}

 

	public function addf_create_order_admin_callback() {

		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}

		global $wpdb;
	
		$receiver_email = isset($_POST['receiver_email']) ? sanitize_email(wp_unslash($_POST['receiver_email'])) : '';
		$sender_email = isset($_POST['sender_email']) ? sanitize_email(wp_unslash($_POST['sender_email'])) : '';
		$pay_amount = isset($_POST['pay_amount']) ? sanitize_text_field(wp_unslash($_POST['pay_amount'])) : '';
		$transaction_type = isset($_POST['transaction_type']) ? sanitize_text_field(wp_unslash($_POST['transaction_type'])) : '';
		$transaction_action = isset($_POST['transaction_action']) ? sanitize_text_field(wp_unslash($_POST['transaction_action'])) : '';
		$transaction_reference = isset($_POST['transaction_reference']) ? sanitize_text_field(wp_unslash($_POST['transaction_reference'])) : '';
		$pay_note = isset($_POST['pay_note']) ? sanitize_text_field(wp_unslash($_POST['pay_note'])) : '';
		$end_date = isset($_POST['end_date']) ? sanitize_text_field(wp_unslash($_POST['end_date'])) : '';
		$status = isset($_POST['status']) ? sanitize_text_field(wp_unslash($_POST['status'])) : '';
	
		if (empty($receiver_email) || empty($sender_email) || empty($pay_amount) || empty($transaction_type) || empty($transaction_reference)) {
			wp_send_json_error(array( 'message' => esc_html__('Required fields are missing.', 'woo_addf_wm') ));
			return;
		}
	
		$user = get_user_by('email', $receiver_email);
		$user_name_for_email = $user->user_login;

		
		$existing_recharge_id = $wpdb->get_var('SELECT id FROM wm_recharge_amount');
		$recharge_amount_check = $wpdb->get_var('SELECT recharge_amount FROM wm_recharge_amount');
		$max_balance_en_dis_option = $wpdb->get_var('SELECT max_balance_option FROM wm_recharge_amount');

		if ('yes' === $max_balance_en_dis_option) {
			$max_balance_option = 'on';
		} else {
			$max_balance_option = 'off';
		}
		if ($recharge_amount_check >= $cashback_amount || 'off' === $max_balance_option ) {
			if ($user) {
				$user_id = $user->ID;
	
				// Get necessary data from the order
				$customer_id = $user_id;
				$amount = $pay_amount;
				$order_id = mt_rand(100, 999);
				$receiver_email = $receiver_email;
				$sender_email = $sender_email;
				$transaction_type = $transaction_type;
				$transaction_note = $pay_note;
				$transaction_action = $transaction_action;
				$reference = $transaction_reference;
				$date = current_time('mysql');
				$origin = 'admin';
	
				// Update total_amount logic
				$existing_total_amount = $wpdb->get_var(
				$wpdb->prepare('SELECT total_amount FROM wm_wallet_amount WHERE customer_id = %d', $customer_id)
				);
	
				if ('Credit' === $transaction_type) {
					$total_amount = $existing_total_amount + $amount;
					if (empty($end_date) || empty($status)) {
						wp_send_json_error(array( 'message' => esc_html__('Required fields are missing.', 'woo_addf_wm') ));
						return;
					}
					$expiry_date = $end_date;
					$statuses = $status;
				} elseif ('Debit' === $transaction_type) {
					$total_amount = $existing_total_amount - $amount;
					$expiry_date = '–';
					$statuses = '–';
				} 

				// Insert new record
				$wpdb->insert(
				'wm_wallet_data',
				array(
					'customer_id' => $customer_id,
					'reference' => $reference,
					'amount' => $amount,
					'transaction_type' => $transaction_type,
					'transaction_action' => $transaction_action,
					'order_id' => $order_id,
					'receiver_email' => $receiver_email,
					'payer_email' => $sender_email,
					'transaction_note' => $transaction_note,
					'origin' => $origin,
					'date' => $date,
					'expiry_date' => $expiry_date,
					'status' => $statuses,
				)
				);
				if ('on' === $max_balance_option) {
				$recharge_amount = $wpdb->get_var('SELECT recharge_amount FROM wm_recharge_amount');
				$total_amount_debited = $recharge_amount-$amount;
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
	
				// Check if customer_id exists
				$existing_customer_id = $wpdb->get_var(
					$wpdb->prepare('SELECT customer_id FROM wm_wallet_amount WHERE customer_id = %d', $customer_id)
				);
	
				if ($existing_customer_id) {
					// Update existing record
					$wpdb->update(
						'wm_wallet_amount',
						array(
							'total_amount' => $total_amount,
							'date' => $date,
						),
					array( 'customer_id' => $customer_id )
					);
				} else {
					// Insert new record
					$wpdb->insert(
					'wm_wallet_amount',
					array(
						'customer_id' => $customer_id,
						'total_amount' => $total_amount,
						'date' => $date,
					)
					);
				}
			} else {
				wp_send_json_error(array(
					'message' => esc_html__( 'User not found for this email. ', 'woo_addf_wm' ),
				));
			}
		}

		WC()->mailer()->emails['Addify_manually_admin']->trigger($user_name_for_email, $receiver_email, $transaction_type, $pay_amount);
		wp_send_json_success(array(
			'message' => esc_html__( 'created sucessfully. ', 'woo_addf_wm' ),
		));
	}

	public function addf_create_recharge_admin_callback() {

		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}

		global $wpdb;
		$recharge_id = isset($_POST['recharge_id']) ? sanitize_text_field($_POST['recharge_id']) : '';
		$max_balance_en_dis_option = isset($_POST['max_balance_en_dis_option']) ? sanitize_text_field($_POST['max_balance_en_dis_option']) : '';
		$recharge_amount = isset($_POST['recharge_amount']) ? sanitize_text_field($_POST['recharge_amount']) : '';
		$recharge_action = isset($_POST['recharge_action']) ? sanitize_text_field($_POST['recharge_action']) : '';
	
		// Get necessary data from the order
		$existing_recharge_amount = $wpdb->get_var('SELECT recharge_amount FROM wm_recharge_amount');
		$existing_recharge_id = $wpdb->get_var('SELECT id FROM wm_recharge_amount');
		$total_amount = $existing_recharge_amount;
	
		if ('Credit' === $recharge_action) {
			$total_amount += $recharge_amount;
		} elseif ('Debit' === $recharge_action) {
			$total_amount -= $recharge_amount;
		} 
		
	
		if (null !== $existing_recharge_amount) {
			// update record
			$wpdb->update(
				'wm_recharge_amount',
				array(
					'recharge_amount' => $total_amount,
					'max_balance_option' => $max_balance_en_dis_option,
					'recharge_action' => $recharge_action,
					'date' => current_time('mysql'),
				),
				array( 'id' => $existing_recharge_id ) // Assuming there's only one row in 'wm_recharge_amount' table
			);
	
			wp_send_json_success(array(
				'message' => esc_html__('Wallet updated successfully!', 'woo_addf_wm'),
			));
		} else {

			// Insert new record
			$wpdb->insert(
				'wm_recharge_amount',
				array(
					'recharge_amount' => $recharge_amount,
					'recharge_action' => $recharge_action,
					'date' => current_time('mysql'),
				)
			);

			
			wp_send_json_error(array(
				'message' => esc_html__('Wallet updated successfully!', 'woo_addf_wm'),
			));
		}
	}



	

	

	public function addf_create_order_callback() {

		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}
		
		global $wpdb;
	
		$emails = isset($_POST['emails']) ? array_map('sanitize_email', wp_unslash($_POST['emails'])) : array();
		$reciver_email = isset($_POST['reciver_email']) ? sanitize_email(wp_unslash($_POST['reciver_email'])) : '';
		$sendr_email = isset($_POST['sendr_email']) ? sanitize_email(wp_unslash($_POST['sendr_email'])) : '';
		$pay_amount = isset($_POST['pay_amount']) ? sanitize_text_field(wp_unslash($_POST['pay_amount'])) : '';
		$transaction_type = isset($_POST['transaction_type']) ? sanitize_text_field(wp_unslash($_POST['transaction_type'])) : '';
		$transaction_reference = isset($_POST['transaction_reference']) ? sanitize_text_field(wp_unslash($_POST['transaction_reference'])) : '';
		$pay_note = isset($_POST['pay_note']) ? sanitize_text_field(wp_unslash($_POST['pay_note'])) : '';
	
		if (empty($emails) || empty($reciver_email) || empty($sendr_email) || empty($pay_amount) || empty($transaction_type) || empty($transaction_reference)) {
			wp_send_json_error(array( 'message' => esc_html__('Required fields are missing.', 'woo_addf_wm') ));
			return;
		}

		$user_for_email = get_user_by('email', $reciver_email);
		$user_name_for_email = $user_for_email->user_login;
	
		
		
		foreach ($emails as $index => $email) {
			$user = get_user_by('email', $email);
			if ($user) {
				$user = get_user_by('email', $email);
				$user_id = $user->ID;
				
	
				// Determine transaction action based on the iteration index
				if (0 === $index) {
					$transaction_action = 'Wallet Credit'; // First iteration
					
				} else {
					$transaction_action = 'Wallet Debit'; // Second iteration
					
				}
	
				// Get necessary data from the order
				$reference = $transaction_reference;
				$customer_id = $user_id;
				$amount = $pay_amount;
				$order_id = mt_rand(100, 999);
				$receiver_email = $emails[0]; // Keep [0] index
				$payer_email = $emails[1]; // Keep [1] index
				$transaction_note = $pay_note;
				$date = current_time('mysql');
				$origin = 'transfor';
				$statuses = 'Active';

				$recharge_amount_expiry_days = get_option('expiry_date', '');
				// Create a DateTime object for the current date and time
				$current_date = new DateTime();
				// Add the number of days to the current date
				$current_date->modify('+' . (int) $recharge_amount_expiry_days . ' days');

				$expiry_date_en_dis_option = get_option('expiry_date_en_dis_option', '');

				if ('yes' === $expiry_date_en_dis_option) {
				// Format the date as desired, e.g., 'Y-m-d'
					$end_date = $current_date->format('Y-m-d');
				} else {
					$end_date = '–';
				}
				// Update total_amount logic
				$existing_total_amount = $wpdb->get_var(
					$wpdb->prepare('SELECT total_amount FROM wm_wallet_amount WHERE customer_id = %d', $customer_id)
				);
	
				if (null === $existing_total_amount) {
					$existing_total_amount = 0;
				}
	
				// Calculate total_amount based on the transaction_action
				if ('Wallet Credit' === $transaction_action) {
					$total_amount = $existing_total_amount + $amount;
				} else if ('Wallet Debit' === $transaction_action) {
					$total_amount = $existing_total_amount - $amount;
				} else {
					// Handle unknown transaction action if necessary
					$total_amount = $existing_total_amount; // Or any other logic you want to apply
				}
	
				// Insert new record
				$result = $wpdb->insert(
					'wm_wallet_data',
					array(
						'customer_id' => $customer_id,
						'reference' => $reference,
						'amount' => $amount,
						'transaction_type' => $transaction_type,
						'transaction_action' => $transaction_action,
						'order_id' => $order_id,
						'receiver_email' => $receiver_email,
						'payer_email' => $payer_email,
						'transaction_note' => $transaction_note,
						'origin' => $origin,
						'date' => $date,
						'expiry_date' => $end_date,
						'status' => $statuses,
					)
				);
	
				if (false === $result) {
					wp_send_json_error(array(
						'message' => esc_html__('Failed to insert wallet data.', 'woo_addf_wm'),
						'error' => $wpdb->last_error,
					));
					return;
				}
	
				// Check if customer_id exists
				$existing_customer_id = $wpdb->get_var(
					$wpdb->prepare('SELECT customer_id FROM wm_wallet_amount WHERE customer_id = %d', $customer_id)
				);
	
				if ($existing_customer_id) {
					// Update existing record
					$update_result = $wpdb->update(
						'wm_wallet_amount',
						array(
							'total_amount' => $total_amount,
							'date' => $date,
						),
						array( 'customer_id' => $customer_id )
					);
	
					if (false === $update_result) {
						wp_send_json_error(array(
							'message' => esc_html__('Failed to update wallet amount.', 'woo_addf_wm'),
							'error' => $wpdb->last_error,
						));
						return;
					}
				} else {
					// Insert new record
					$insert_result = $wpdb->insert(
						'wm_wallet_amount',
						array(
							'customer_id' => $customer_id,
							'total_amount' => $total_amount,
							'date' => $date,
						)
					);
	
					if (false === $insert_result) {
						wp_send_json_error(array(
							'message' => esc_html__('Failed to insert wallet amount.', 'woo_addf_wm'),
							'error' => $wpdb->last_error,
						));
						return;
					}
				}
			} else {
				// User not found
				wp_send_json_error(array(
					'message' => esc_html__('User not found for email.', 'woo_addf_wm'),
					'email' => $email,
				));
				return;
			}
		}
	
		// $this->send_transfer_email($user_name_for_email, $reciver_email, $sendr_email, $pay_amount);
		
		$transfor_sender = WC()->mailer()->emails['Addify_transfer_sender']->trigger($reciver_email, $sendr_email, $pay_amount);

		if ($transfor_sender) {
			$transfor_reciver = WC()->mailer()->emails['Addify_transfer_reciver']->trigger($user_name_for_email, $reciver_email, $sendr_email, $pay_amount);
			if ($transfor_reciver) {
				$transfor_admin = WC()->mailer()->emails['Addify_transfer_admin']->trigger($reciver_email, $sendr_email, $pay_amount);
				if ($transfor_admin) {
				wp_send_json_success(array(
					'message' => esc_html__( 'Created successfully.', 'woo_addf_wm' ) . $current_user_email,
				));
				}
			}
			
		} else {
			wp_send_json_error(array(
				'message' => esc_html__( 'Failed to send email. Please try again.', 'woo_addf_wm' ),
			));
		}
	}
	

	
	
	
	
	

	public function generate_otp() {
		return rand(100000, 999999);
	}

	
	public function addf_verify_otp_callback() {

		// Check the nonce for security
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}

		session_start();
	
		// Sanitize the OTP (assuming it's numeric)
		$otp = isset($_POST['otpp']) ? sanitize_text_field($_POST['otpp']) : '';
	
		if (isset($_SESSION['otp']) && $_SESSION['otp'] == $otp) {
			// Unset the OTP session variable
			unset($_SESSION['otp']);
	
			wp_send_json_success(array(
				'message' => esc_html__('Amount has been successfully transferred.', 'woo_addf_wm'),
			));
		} else {
			wp_send_json_error(array(
				'message' => esc_html__('OTP verification failed.', 'woo_addf_wm'),
			));
		}
	}

	public function addf_send_otp_callback() {

		// Check the nonce for security
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}
		
		$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
		$current_user_email = isset($_POST['current_user_email']) ? sanitize_email($_POST['current_user_email']) : '';
		$total_amount = isset($_POST['total_amount']) ? sanitize_text_field($_POST['total_amount']) : '';
		$pay_amount = isset($_POST['pay_amount']) ? sanitize_text_field($_POST['pay_amount']) : '';
		$pay_note = isset($_POST['pay_note']) ? sanitize_text_field($_POST['pay_note']) : '';

		$wallet_payment_max_transfer = get_option('wallet_payment_max_transfer', '10');
		$wallet_payment_min_transfer = get_option('wallet_payment_min_transfer', '1');
		$currency_symbol = get_woocommerce_currency_symbol();
	
		switch (true) {
			case empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL):
				wp_send_json_error(array(
					'message' => esc_html__( 'Valid email is required.', 'woo_addf_wm' ),
				));
				break;
			default:
				$user = get_user_by('email', $email);
				if (!$user) {
					wp_send_json_error(array(
						'message' => esc_html__( 'Receiver does not exist.', 'woo_addf_wm' ),
					));
					wp_die();
				}

				if ($current_user_email === $email) {
					wp_send_json_error(array(
						'message' => esc_html__( 'You can send money to another user account only.', 'woo_addf_wm' ),
					));
					wp_die();
				}

				if (empty($pay_amount) || !is_numeric($pay_amount)) {
					wp_send_json_error(array(
						'message' => esc_html__( 'Valid amount is required.', 'woo_addf_wm' ),
					));
					break;
				}

				if ($pay_amount > $total_amount) {
					wp_send_json_error(array(
						'message' => esc_html__( 'Your are not enough amount to transfor.', 'woo_addf_wm' ),
					));
					break;
				}

				if ($pay_amount < $wallet_payment_min_transfer) {
					wp_send_json_error(array(
						'message' => esc_html__( 'The minimum amount you can transfer from your wallet is ', 'woo_addf_wm' ) . $currency_symbol . ' ' . $wallet_payment_min_transfer . '.',
					));
					break;
				}

				if ($pay_amount > $wallet_payment_max_transfer) {
					wp_send_json_error(array(
						'message' => esc_html__( 'The maximum amount you can transfer from your wallet is ', 'woo_addf_wm' ) . $currency_symbol . ' ' . $wallet_payment_max_transfer . '.',
					));
					break;
				}
	
				if (strlen($pay_note) < 10) {
					wp_send_json_error(array(
						'message' => esc_html__( 'Note must be at least 10 characters long.', 'woo_addf_wm' ),
					));
					break;
				}
	
				$otp = $this->generate_otp();
				$otp_sent = WC()->mailer()->emails['Addify_otp_transfer']->trigger($current_user_email, $otp);

				if ($otp_sent) {
					session_start();
					$_SESSION['otp'] = $otp;
					wp_send_json_success(array(
						'message' => esc_html__( 'Your six-digit OTP for wallet transaction has been successfully sent to the email: ', 'woo_addf_wm' ) . $current_user_email,
					));
				} else {
					wp_send_json_error(array(
						'message' => esc_html__( 'Failed to send OTP. Please try again.', 'woo_addf_wm' ),
					));
				}
				wp_die();
				break;
		}
	}
	

	public function get_id_of_wallet_product() {

		$addf_args_wallet = array(
			'post_type' => 'product',
			'post_title' => 'Wallet',
			'posts_per_page' => 1,
			'fields' => 'ids',
		);
		$addf_product_ids = get_posts($addf_args_wallet);

		if (!empty($addf_product_ids)) {
			$addf_product_id = $addf_product_ids[0];
			return $addf_product_id;
		}
	}

	public function addf_plugin_activate() {
		$post_id = wp_insert_post(array(
			'post_title' => 'Wallet',
			'post_status' => 'publish',
			'post_type' => 'product',
		));
	
		if (!is_wp_error($post_id)) {
			// Set the regular price
			update_post_meta($post_id, '_regular_price', '10000');
			update_post_meta($post_id, '_price', '10000');
		}
	}
	
	public function addf_plugin_deactivate() {
		// Get the product ID to delete 
		$addf_products_id = $this->get_id_of_wallet_product();
		if ($addf_products_id) {
		// Delete the product permanently
		wp_delete_post($addf_products_id, true);
		}
	}


	public function addf_add_wallet_endpoint() {
		add_rewrite_endpoint( 'customer-wallet', EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}

	public function addf_flush_rewrite_rules_on_activation() {
		addf_add_wallet_endpoint();
		flush_rewrite_rules();
	}

	public function add_to_wallet_cart_callback() {
		include_once ADDF_WM_DIR . 'includes/front/front-ajax/addify-add-to-cart-wallet.php';
	}
	

	public function addf_wm_wc_check() {
		if (!is_multisite() && ( !in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')), true) )) {
			add_action('admin_notices', array( $this, 'addf_wm_wc_active' ));

		}
	}


	public function addf_wm_wc_active() {
		deactivate_plugins(__FILE__);
		?>
		<div id="message" class="error">
			<p>
				<strong>
					<?php echo esc_html__('Wallet managmenet for WooCommerce plugin is inactive. WooCommerce plugin must be active in order to activate it.', 'woo_addf_wm'); ?>
				</strong>
			</p>
		</div>
		<?php
	}
	

	public function addf_wm_HOPS_Compatibility() {
		if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
		}
	}


	public function addf_wm_check_woocommerce_is_defined_or_not() {
		
		if (defined('WC_PLUGIN_FILE')) {
			
			include ADDF_WM_DIR . 'includes/addify-wallet-payment.php';
			include_once ADDF_WM_DIR . 'includes/general-functions.php';

			if (is_admin()) {
				
				include_once ADDF_WM_DIR . 'includes/admin/addify-wallet-management-admin.php';
			} else {
				include_once ADDF_WM_DIR . 'includes/front/addify-wallet-management-front.php';
				
			}

			add_action('wp_loaded', array( $this, 'addf_wm_load_text_domain' ));
		}
	}

	public function addf_wm_load_text_domain() {
		if (function_exists('load_plugin_textdomain')) {
			load_plugin_textdomain('woo_addf_wm', false, dirname(plugin_basename(__FILE__)) . '/languages/');
		}
	}

	public function addf_post_purchase_offer_global_constents_vars() {
		if (!defined('ADDF_WM_URL')) {
			define('ADDF_WM_URL', plugin_dir_url(__FILE__));
		}

		if (!defined('ADDF_WM_BASENAME')) {
			define('ADDF_WM_BASENAME', plugin_basename(__FILE__));
		}
		if (!defined('ADDF_WM_DIR')) {
			define('ADDF_WM_DIR', plugin_dir_path(__FILE__));
		}
	}
}

new Addify_Wallet_Management();




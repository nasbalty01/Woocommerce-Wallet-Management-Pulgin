<?php
defined('ABSPATH') || exit;

class Addify_Wallet_Payment {
	public $nonce;
	public function __construct() {
		$this->nonce = wp_create_nonce('addf_wm_nonce_ajax');
		add_action('wp_enqueue_scripts', array( $this, 'addf_wm_enqueue_scripts_payment' ));
		add_action('wp_ajax_addf_apply_wallet_payment', array( $this, 'addf_apply_wallet_payment' ));
		add_action('wp_ajax_nopriv_addf_apply_wallet_payment', array( $this, 'addf_apply_wallet_payment' ));

		add_action('wp_ajax_addf_cancel_wallet_payment', array( $this, 'addf_cancel_wallet_payment' ));
		add_action('wp_ajax_nopriv_addf_cancel_wallet_payment', array( $this, 'addf_cancel_wallet_payment' ));

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'addf_addf_get_subtotal_before_calculate_totals' ), 10 );

		add_action('woocommerce_checkout_order_processed', array( $this, 'addf_get_subtotal_before_calculate_totals' ), 10, 3);

		add_action('woocommerce_review_order_before_payment', array( $this, 'addf_check_cart_items_for_wallet' ));

		add_action('wp_ajax_addf_send_otp_payment', array( $this, 'addf_send_otp_payment' ));
		add_action('wp_ajax_nopriv_addf_send_otp_payment', array( $this, 'addf_send_otp_payment' ));

		add_action('wp_ajax_addf_verify_otp_payment', array( $this, 'addf_verify_otp_payment' ));
		add_action('wp_ajax_nopriv_addf_verify_otp_payment', array( $this, 'addf_verify_otp_payment' ));
	}




	public function addf_wm_enqueue_scripts_payment() {
		wp_enqueue_style('wm_payment', ADDF_WM_URL . '/assets/css/addf_wm_payment.css', array(), '1.0');
	}

	public function addf_send_otp_payment() {
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
	
		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}
	
		if (isset($_POST['email'])) {
			$email = sanitize_email($_POST['email']);
			$otp = mt_rand(100000, 999999); // Generate a 6-digit OTP
			WC()->session->set('wallet_payment_otp', $otp);
	
			
			
	
			$current_user = wp_get_current_user();
			$user_name = sanitize_text_field($current_user->user_login);

			$otp_sent = WC()->mailer()->emails['Addify_otp_payment']->trigger($user_name, $email, $otp);
			
	
			// Send the email
			if ($otp_sent) {
				wp_send_json_success(array(
					'message' => esc_html__('OTP successfully sent to your email.', 'woo_addf_wm'),
				));
			} else {
				wp_send_json_error(array(
					'message' => esc_html__('Failed to send OTP. Please try again.', 'woo_addf_wm'),
				));
			}
		} else {
			wp_send_json_error(array(
				'message' => esc_html__('Email not exist. Please try again.', 'woo_addf_wm'),
			));
		}
	}
	
	
	
	public function addf_verify_otp_payment() {
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
	
		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}
	
		if (isset($_POST['otp'])) {
			$otp = intval($_POST['otp']);
			$saved_otp = WC()->session->get('wallet_payment_otp');
	
			if ($otp == $saved_otp) {
				// Unset the OTP session variable
				WC()->session->set('wallet_payment_otp', null);
	
				wp_send_json_success(array(
					'message' => esc_html__('OTP verified successfully.', 'woo_addf_wm'),
				));
			} else {
				wp_send_json_error(array(
					'message' => esc_html__('OTP verification failed.', 'woo_addf_wm'),
				));
			}
		} else {
			wp_send_json_error(array(
				'message' => esc_html__('OTP verification failed.', 'woo_addf_wm'),
			));
		}
	}
	
	
	

   
	public function addf_cancel_wallet_payment() {
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}
	
		$wallet_payment_amount = WC()->session->get('wallet_payment_amount', 0);
		if ($wallet_payment_amount > 0) {
			WC()->session->set('wallet_payment_amount', 0);
			wp_send_json_success(array(
				'message' => esc_html__('Payment removed successfully.', 'woo_addf_wm'),
			));
		}
	
		wp_send_json_error(array(
			'message' => esc_html__('There are issue removing payment. Please try again.', 'woo_addf_wm'),
		));
	}
	

	public function addf_apply_wallet_payment() {
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
	
		if (!wp_verify_nonce($nonce, 'addf_wm_nonce_ajax')) {
			wp_send_json_error(array(
				'message' => esc_html__('Nonce verification failed!', 'woo_addf_wm'),
			));
			wp_die();
		}
	
		if (isset($_POST['wallet_amount'])) {
			$wallet_amount = floatval($_POST['wallet_amount']);
			WC()->session->set('wallet_payment_amount', $wallet_amount);
			wp_send_json_success(array(
				'message' => esc_html__('Payment added successfully.', 'woo_addf_wm'),
			));
		}
		wp_send_json_error(array(
			'message' => esc_html__('There was an issue processing your payment. Please try again.', 'woo_addf_wm'),
		));
	}
	
	public function addf_addf_get_subtotal_before_calculate_totals( $cart ) {
		$wallet_payment_title = get_option('wallet_payment_title', __('Pay via Wallet', 'woo_addf_wm'));
	
		$wallet_payment_amount = (int) WC()->session->get('wallet_payment_amount');
		if ($wallet_payment_amount > 0) {
			$cart->add_fee($wallet_payment_title, -$wallet_payment_amount);
		}
	}
	

   

	public function custom_check_payment_method( $order_id, $posted_data, $order ) {
		// Get current user's ID
		$user_id = get_current_user_id();
		$user_info = get_userdata($user_id);
		$user_email = $user_info->user_email;
		// Get the wallet payment amount from session
		$wallet_payment_amount = (int) WC()->session->get('wallet_payment_amount');
	

		if ($wallet_payment_amount > 0) {
		// Get the current user's wallet amount
		global $wpdb;
		$wallet_amount_total = $wpdb->get_var($wpdb->prepare('SELECT total_amount FROM wm_wallet_amount WHERE customer_id = %d', $user_id));
	
	
		// Subtract the wallet payment amount from the total wallet amount
		$updated_wallet_amount = $wallet_amount_total - $wallet_payment_amount;
	
		// Update the wallet amount in the database
		$wpdb->update('wm_wallet_amount', array( 'total_amount' => $updated_wallet_amount ), array( 'customer_id' => $user_id ));
		
		$notes = esc_html__( 'Order No: ' . $order_id, 'woo_addf_wm' );

			$customer_id = $user_id;
			$amount = $wallet_payment_amount;
			$order_id = $order_id;
			$receiver_email = $user_email;
			$sender_email = '';
			$transaction_type = 'Debit';
			$transaction_note = $notes;
			$transaction_action = 'Wallet Debit ';
			$reference = 'Wallet debited on product purchase';
			$date = current_time('mysql');
			$origin = 'purchase';
	
	
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
					'payer_email' => '',
					'transaction_note' => $transaction_note,
					'origin' => $origin,
					'date' => $date,
				)
			);
		
	
		// Unset session wallet payment amount
		WC()->session->set('wallet_payment_amount', 0);
		}
	}
	

	public function addf_check_cart_items_for_wallet() {
		$cart = WC()->cart->get_cart();
		$wallet_payment_enabled = get_option('wallet_payment_enabled', 'no');

		foreach ($cart as $cart_item) {
			$product_name = $cart_item['data']->get_name();
			if (false !== strpos($product_name, 'Wallet') || 'no' === $wallet_payment_enabled) {
				return;
			}           
		}
		
		$this->add_wallet_payment_field();
	}


	public function add_wallet_payment_field() {

		global $wpdb;
	
			$user_id =  get_current_user_id();
			$user_info = get_userdata($user_id);
			$user_email = $user_info->user_email;
			
			$image_spiner = ADDF_WM_URL . 'assets/images/spinner.gif';
	
			$wallet_amount_total = $wpdb->get_var($wpdb->prepare('SELECT total_amount FROM wm_wallet_amount WHERE customer_id = %d', $user_id));

			$wallet_payment_title = get_option('wallet_payment_title', __('Pay via Wallet', 'woo_addf_wm'));
			$wallet_payment_description = get_option('wallet_payment_description', esc_html__('Pay with amount in your wallet.', 'woo_addf_wm'));
			$wallet_payment_otp_message = get_option('wallet_payment_otp_message', esc_html__('Your six digit OTP for wallet transaction has been successfully sent to the email', 'woo_addf_wm'));

			$wallet_payment_max_pay = get_option('wallet_payment_max_pay', '50');
			$wallet_payment_min_pay = get_option('wallet_payment_min_pay', '1');

			$wallet_payment_otp_validation_time = get_option('wallet_payment_otp_validation_time', '2');
			$currency_symbol = get_woocommerce_currency_symbol();

		  
		?>
			   <fieldset>
			   <div>
				
			   <p class="form-row woocommerce-validated" id="wkwc_wallet-checkout-payment_field" data-priority="">
				<span class="woocommerce-input-wrapper">
						<label class="checkbox ">
						<input type="checkbox" id="wm_wallet-checkout-payment" >
						<?php echo esc_html__($wallet_payment_title); ?> <?php echo wp_kses_post(wc_price($wallet_amount_total)); ?> <?php echo esc_html__(' (optional)', 'woo_addf_wm'); ?>
						</label>
					</span>
				</p>
			  </div>
			  <p><?php echo esc_html__($wallet_payment_description); ?></p>
			  <p class="form-row form-row-wide wm_wallet_spin">
				<img decoding="async" class="wp-spin" src="<?php echo esc_url( $image_spiner ); ?>">
			  </p>
			  <div id="otp_field">
				<p class="form-row form-row-wide">
					<input type="text" id="otp" name="otp" placeholder="<?php echo esc_html__('Enter the OTP.', 'woo_addf_wm'); ?>" />
					<button type="button" id="verify_otp_button"><?php echo esc_html__('Verify OTP', 'woo_addf_wm'); ?></button>
					<button type="button" id="resend_otp_button"><?php echo esc_html__('Resend', 'woo_addf_wm'); ?></button>
				</p>
				
				<p class="form-row form-row-wide wm_wallet_timmer"></p>
				
				<p class="form-row form-row-wide wm_wallet-success">
				<?php echo esc_html__($wallet_payment_otp_message); ?>: <?php echo esc_attr_e($user_email); ?>
				</p>
			</div>

			<div class="wm_wallet_input">
				<p class="form-row form-row-wide">
					<input type="number" id="wallet_payment_amount" name="wallet_payment_amount" class="input-text" />
					<button type="button" id="wallet_pay_add"><?php esc_html_e('Add', 'woo_addf_wm'); ?></button>
					<button type="button" id="wallet_pay_cancel"><?php esc_html_e('Cancel', 'woo_addf_wm'); ?></button>
				</p>
			</div>

			</fieldset>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
				var wallet_payment_otp_validation_time = <?php echo json_encode($wallet_payment_otp_validation_time); ?>;
				var otp_validation_time = wallet_payment_otp_validation_time * 60;
				var timer_payment
				function timmerr_payment(){
					var seconds = otp_validation_time; // Start the timer 
					timer_payment = setInterval(function() {
						seconds--;
						$('.wm_wallet_timmer').html(seconds + ' Seconds');
						if (seconds <= 0) { // Change 60 to the desired duration
							$('#verify_otp_button').prop('disabled', true);
							$('#resend_otp_button').show();
							clearInterval(timer_payment);
						}
					}, 1000);
				}


				$(document).on('change', '#wm_wallet-checkout-payment', function() {
					$('#otp').val('');
					clearInterval(timer_payment);
					$('#wallet_payment_amount').val('');
				if ($(this).is(":checked")) {
					$('#resend_otp_button').hide();
					$('.wm_wallet_spin').show();
					addf_send_otp_payment_ajax();
				   
				} else {
					$('#otp_field').hide();
					$('.wm_wallet_input').hide();
				}
			});

			$(document).on('click', '#resend_otp_button', function() {
				$current_user('#otp').val('');
				$('#wallet_payment_amount').val('');
				$('#resend_otp_button').hide();
				$('.wm_wallet_spin').show();
				$('#verify_otp_button').prop('disabled', false);
				addf_send_otp_payment_ajax();      
			});

			function addf_send_otp_payment_ajax(){
				var nonce = <?php echo json_encode($this->nonce); ?>;
				var email = <?php echo json_encode($user_email); ?>;
				$.ajax({
					type: 'POST',
					url: wc_checkout_params.ajax_url,
					data: {
						action: 'addf_send_otp_payment',
						nonce: nonce,
						email: email
					},
					success: function(response) {
						console.log(response);
						if (response.success) {
							timmerr_payment();
							// Show OTP input field and verify button
							$('#otp_field').show();
							$('.wm_wallet_spin').hide();
						} else {
							var message = response.data.message;
							$('.woocommerce-notices-wrapper:first .message_wallet').remove();
							// Append the new message to the first .woocommerce-notices-wrapper
							$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + '</div></div>');
							$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
						}
					}
				});
			}


			$(document).on('click', '#verify_otp_button', function() {
				var nonce = <?php echo json_encode($this->nonce); ?>;
				var otp = $('#otp').val();
				$.ajax({
					type: 'POST',
					url: wc_checkout_params.ajax_url,
					data: {
						action: 'addf_verify_otp_payment',
						nonce: nonce,
						otp: otp
					},
					success: function(response) {
						if (response.success) {
							clearInterval(timer_payment);
							// OTP verified, show payment amount input field
							$('#otp_field').hide();
							$('.wm_wallet_input').show();
						} else {
							clearInterval(timer_payment);
							var message = response.data.message;
							$('.woocommerce-notices-wrapper:first .message_wallet').remove();
							// Append the new message to the first .woocommerce-notices-wrapper
							$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + '</div></div>');
							$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
						}
					}
				});
			});


					var walletAmountAvailable = <?php echo json_encode($wallet_amount_total); ?>;
					
					$(document).on('click', '#wallet_pay_add', function() {
					var walletAmount = parseFloat($('#wallet_payment_amount').val());
					var subtotalAmount_cart = <?php echo json_encode(WC()->cart->get_subtotal()); ?>;
					var subtotalAmount = parseFloat(subtotalAmount_cart);

					var currency_symbol =  <?php echo json_encode($currency_symbol); ?>;
					var wallet_payment_max_pay =  parseFloat(<?php echo json_encode($wallet_payment_max_pay); ?>);
					var wallet_payment_min_pay =  parseFloat(<?php echo json_encode($wallet_payment_min_pay); ?>);

					if (isNaN(walletAmount)) {
						var message = '<?php echo esc_html__('Valid amount is required.', 'woo_addf_wm'); ?>';
						$('.woocommerce-notices-wrapper:first .message_wallet').remove();
						$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + '</div></div>');
						$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
						return false;
					}

					if (walletAmount <= 0) {
						var message = '<?php echo esc_html__('Amount must be greater than zero.', 'woo_addf_wm'); ?>';
						$('.woocommerce-notices-wrapper:first .message_wallet').remove();
						$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + '</div></div>');
						$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
						return false;
					}

					if (walletAmount > walletAmountAvailable) {
						var message = '<?php echo esc_html__('The entered amount exceeds your wallet balance.', 'woo_addf_wm'); ?>';
						$('.woocommerce-notices-wrapper:first .message_wallet').remove();
						$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + '</div></div>');
						$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
						return false;
					}

					if (walletAmount < wallet_payment_min_pay) {
						var message = '<?php echo esc_html__('The minimum amount you can pay from your wallet is ', 'woo_addf_wm'); ?>';
						$('.woocommerce-notices-wrapper:first .message_wallet').remove();
						$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + currency_symbol + ' ' + wallet_payment_min_pay + '</div></div>');
						$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
						return false;
					}

					if (walletAmount > wallet_payment_max_pay) {
						var message = '<?php echo esc_html__('The maximum amount you can pay from your wallet is ', 'woo_addf_wm'); ?>';
						$('.woocommerce-notices-wrapper:first .message_wallet').remove();
						$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + currency_symbol + ' ' + wallet_payment_max_pay + '</div></div>');
						$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
						return false;
					}

					if (walletAmount >= subtotalAmount) {
						var message = '<?php echo esc_html__('You cannot pay an amount greater/equal than the order subtotal.', 'woo_addf_wm'); ?>';
						$('.woocommerce-notices-wrapper:first .message_wallet').remove();
						$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + '</div></div>');
						$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
						return false;
					}

					var nonce = <?php echo json_encode($this->nonce); ?>;
					$.ajax({
						type: 'POST',
						url: wc_checkout_params.ajax_url,
						data: {
							action: 'addf_apply_wallet_payment',
							nonce: nonce,
							wallet_amount: walletAmount
						},
						success: function(response) {
							if (response.success) {
								$('.woocommerce-notices-wrapper:first .message_wallet').remove();
								$('body').trigger('update_checkout');
							} else {
								var message = response.data.message;
								$('.woocommerce-notices-wrapper:first .message_wallet').remove();
								$('.woocommerce-notices-wrapper:first').append('<div class="message_wallet woocommerce-error"><div>' + message + '</div></div>');
								$('html, body').animate({ scrollTop: $('.woocommerce-notices-wrapper:first').offset().top - 150 }, 'slow');
							}
						}
					});

				});

	
				var nonce = <?php echo json_encode($this->nonce); ?>;
				$(document).on('click', '#wallet_pay_cancel', function() {
					$.ajax({
						type: 'POST',
						url: wc_checkout_params.ajax_url,
						data: {
							action: 'addf_cancel_wallet_payment',
							nonce: nonce, 
						},
						success: function(response) {
							if (response.success) {
								$('body').trigger('update_checkout');
							} else {
								// Handle error response
							}
						}
					});
				});

	
	
				});
			</script>
	<?php
	}
}

new Addify_Wallet_Payment();





?>

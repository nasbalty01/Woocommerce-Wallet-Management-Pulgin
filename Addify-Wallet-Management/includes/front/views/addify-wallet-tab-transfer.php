<?php 
defined('ABSPATH') || exit;

global $wpdb;
$image_spiner = ADDF_WM_URL . 'assets/images/spinner.gif';
$user = wp_get_current_user();
$customer_email = $user->user_email;


 
$currency_symbol = get_woocommerce_currency_symbol();
//get current user
$user = wp_get_current_user();
if ( $user->ID ) { 
	$user_id = $user->ID;
}

 



 $wallet_amount_total = '0';
 $wallet_amount = $wpdb->get_var($wpdb->prepare('SELECT total_amount FROM wm_wallet_amount WHERE customer_id = %d', $user_id));
 $wallet_amount_total += $wallet_amount;

 $wallet_payment_otp_validation_time = get_option('wallet_payment_otp_validation_time', '2');

?>

<div class="wallet-container container_transfor_page">
	<h4 class="wallet-amount"><?php esc_html_e('Wallet Balance:', 'woo_addf_wm'); ?></h4>
	<div class="wallet-wrapper custom-wrapper">
	<p class="wallet-amount-value"><?php echo wp_kses_post(wc_price($wallet_amount_total)); ?></p>
		
		<p class="wm_curreny_symbol"><?php echo esc_html($currency_symbol); ?></p>
	   
	</div>
</div>


<div class="form-wrapper wallet-transfer-container">
	<div>
		<table id="wm_wallet_transfer_from">
			<tbody>
				<tr>
					<td class="wm_transfer_heading">
						<?php esc_html_e('Receiver’s email:', 'woo_addf_wm'); ?>
					</td>
					<td>
						<input type="text" id="wm_otp_otion" value="<?php echo esc_attr($wallet_payment_otp_validation_time); ?>" />
						<input type="text" id="wm_current_user_email" value="<?php echo esc_attr($customer_email); ?>" />
						<input value="" type="email" class="wm_wallet_transfer" id="wm_wallet_receiver" placeholder="<?php esc_attr_e('e.g. example@xyz.com', 'woo_addf_wm'); ?>">
					</td>
				</tr>
				<tr>
					<td class="wm_transfer_heading">
						<?php esc_html_e('Amount to transfer:', 'woo_addf_wm'); ?>
					</td>
					<td>
						<input value="<?php echo esc_attr($wallet_amount_total); ?>" type="text" class="wm_wallet_transfer" id="total_amount" style="display:none;">
						<input value="" type="number" class="wm_wallet_transfer" id="wm_wallet_pay_amount" placeholder="<?php esc_attr_e('e.g. 100', 'woo_addf_wm'); ?>" step="0.01" min="1" max="8.00">
					</td>
				</tr>
				<tr class="wkwc_wallet_transaction_note">
					<td class="wm_transfer_heading">
						<?php esc_html_e('Transaction note:', 'woo_addf_wm'); ?>
					</td>
					<td>
						<input value="" type="text" class="wm_wallet_transfer" id="wm_wallet_pay_note" placeholder="<?php esc_attr_e('e.g. Abc', 'woo_addf_wm'); ?>">
					</td>
				</tr>
				<tr class="wkwc_wallet_otp_input" style="display:none;">
					<td class="wm_transfer_heading">
						<?php esc_html_e('Enter your OTP to verify:', 'woo_addf_wm'); ?>
					</td>
					<td>
						<input type="password" class="wm_wallet_transfer" id="wm_wallet_transfer_otp" placeholder="<?php esc_attr_e('Enter the OTP', 'woo_addf_wm'); ?>">
					</td>
				</tr>
				<tr>
					<td class="wm_button_td">
						<button type="button" id="wm_wallet_transfer_money" class="wallet-button wm_wallet_transfer_money"><?php esc_html_e('Transfer', 'woo_addf_wm'); ?></button>
						<button type="button" id="wm_wallet_verify_otp" class="wallet-button" style="display:none;"><?php esc_html_e('Verify & Transfer', 'woo_addf_wm'); ?></button>
						<button type="button" id="wm_wallet_resend_otp" class="wallet-button wm_wallet_transfer_money" style="display:none;"><?php esc_html_e('Resend', 'woo_addf_wm'); ?></button>
					</td>
					<td><img class="wp-spin wm_wallet-spin-loader" style="display: none;" src="<?php echo esc_url( $image_spiner ); ?>" > <p id="wm_wallet_timer_msg"></p></td>
				</tr>
				<tr class="wm_wallet_otp_success_notice" style="display:none;">
					<td> </td>
					<td><p id="wm_wallet_info_msg"></p></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>







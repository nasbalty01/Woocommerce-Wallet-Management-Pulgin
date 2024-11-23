<?php
defined('ABSPATH') || exit;

$Customer_id = $action_customer_id;
$user = get_user_by('ID', $Customer_id);

if ($user) {
	$user_name = $user->user_login; // Display name
	$reciver_user_email = $user->user_email; // Email
	
} 

$user = wp_get_current_user();
$sender_user_email = $user->user_email;

$current_url =  admin_url('admin.php?page=customer-wallet');

?>

<div class="wrap woocommerce">
	<h1 class="append_message"><?php esc_html_e('Wallet Manual Transaction', 'woo_addf_wm'); ?></h1>

	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wallet-customer"><?php esc_html_e('Username', 'woo_addf_wm'); ?></label>
				</th>
				<td>
					<span class="user_name"><?php echo esc_html($user_name); ?></span>
					<input type="text" required="" value="<?php echo esc_html($sender_user_email); ?>" id="wallet-sender_email" style="display:none">
					<input type="text" required="" value="<?php echo esc_html($reciver_user_email); ?>" id="wallet-reciver_email" style="display:none">
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wallet-transaction-amount"><?php esc_html_e('Amount', 'woo_addf_wm'); ?></label>
				</th>
				<td>
					<input type="number" required="" value="" id="wallet-transaction-amount" min="0">
					<p class="description"><?php esc_html_e('Enter the transaction amount here.', 'woo_addf_wm'); ?></p>
					<span id="amount_message"><?php esc_html_e('Amount is required.', 'woo_addf_wm'); ?></span>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wallet-action"><?php esc_html_e('Action', 'woo_addf_wm'); ?></label>
				</th>
				<td>
					<select class="" id="wallet-action" title="action">
						<option value="Credit"><?php esc_html_e('Credit', 'woo_addf_wm'); ?></option>
						<option value="Debit"><?php esc_html_e('Debit', 'woo_addf_wm'); ?></option>
					</select>
					<p class="description"><?php esc_html_e('Select the action (Credit/Debit) here.', 'woo_addf_wm'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wallet-note"><?php esc_html_e('Transaction Note', 'woo_addf_wm'); ?></label>
				</th>
				<td>
					<textarea cols="46" pattern="([A-z0-9\s]){2,}" rows="7" name="wallet-note" id="wallet-note" title="note"></textarea>
					<p class="description"><?php esc_html_e('Enter the transaction note here.', 'woo_addf_wm'); ?></p>
				</td>
			</tr>

			<tr valign="top"  class="end_date_tr">
				<th scope="row" class="titledesc">
					<label for="wallet-note"><?php esc_html_e('Expiry Date', 'woo_addf_wm'); ?></label>
				</th>
				<td>
				   <input type="date" name="end_date" id="end_date" class="end_date" value="">
					<p class="description"><?php esc_html_e('Select the expiry date.', 'woo_addf_wm'); ?></p>
				</td>
			</tr>

		</tbody>
	</table>
	<p class="submit">
		<input class="button button-primary button-large wm_manual_add_wallet" id="wm_manual_add_wallet" type="submit" value="<?php esc_html_e('Update wallet', 'woo_addf_wm'); ?>">
		<a href="<?php echo esc_html($current_url); ?>" class="button button-secondary button-large"><?php esc_html_e('Back to wallet list', 'woo_addf_wm'); ?></a>
	</p>

</div>

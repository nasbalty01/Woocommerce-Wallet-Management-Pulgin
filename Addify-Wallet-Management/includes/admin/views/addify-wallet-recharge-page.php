<?php
defined('ABSPATH') || exit;

global $wpdb;

$recharge_id = $wpdb->get_var('SELECT id FROM wm_recharge_amount');
$recharge_amount_total = '0';
$recharge_amount = $wpdb->get_var('SELECT recharge_amount FROM wm_recharge_amount');
$recharge_amount_total += $recharge_amount;
$recharge_date = $wpdb->get_var('SELECT date FROM wm_recharge_amount');
$max_balance_option = $wpdb->get_var('SELECT max_balance_option FROM wm_recharge_amount');



$current_url =  admin_url('admin.php?page=customer-wallet');

?>
<div class="wrap woocommerce">
	<h1 class="append_message"><?php esc_html_e('Wallet Max Balance', 'woo_addf_wm'); ?></h1>
	<table class="form-table">
		<tbody>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="max_balance_en_dis_option"><?php esc_html_e('Max balance', 'woo_addf_wm'); ?></label>
				</th>
				<td><input type="checkbox" name="max_balance_en_dis_option" id="max_balance_en_dis_option"  <?php checked($max_balance_option, 'yes'); ?>>
				<p class="description"><?php echo esc_html__('Enable max balance.', 'woo_addf_wm'); ?></p>
			</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="recharge_total"><?php esc_html_e('Wallet amount', 'woo_addf_wm'); ?></label>
				</th>
				<td>
					<span class="user_name"><?php echo wp_kses_post(wc_price($recharge_amount_total)); ?></span>
					<input type="text" value="<?php echo esc_html($recharge_id); ?>" id="recharge_id" style="display:none">
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="recharge_amount"><?php esc_html_e('Amount', 'woo_addf_wm'); ?></label>
				</th>
				<td>
					<input type="number" required="" value="" id="recharge_amount" min="0">
					<p class="description"><?php esc_html_e('Enter the (Credit/Debit) amount here.', 'woo_addf_wm'); ?></p>
					<span id="recharge_amount_message" style="display:none; color:red;"><?php esc_html_e('Amount is required.', 'woo_addf_wm'); ?></span>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="recharge_action"><?php esc_html_e('Action', 'woo_addf_wm'); ?></label>
				</th>
				<td>
					<select class="" id="recharge_action" title="action">
						<option value="Credit"><?php esc_html_e('Credit', 'woo_addf_wm'); ?></option>
						<option value="Debit"><?php esc_html_e('Debit', 'woo_addf_wm'); ?></option>
					</select>
					<p class="description"><?php esc_html_e('Select the action (Credit/Debit) here.', 'woo_addf_wm'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="recharge_date"><?php esc_html_e('Date', 'woo_addf_wm'); ?></label>
				</th>
				<td>
				<span class="user_name"><?php echo esc_html(date_format(date_create($recharge_date), 'M d, Y g:i:s A')); ?></span>
				</td>
			</tr>

		</tbody>
	</table>
	<p class="submit">
		<input class="button button-primary button-large recharge_wallet" id="recharge_wallet" type="submit" value="<?php esc_html_e('Update Wallet', 'woo_addf_wm'); ?>">
	</p>

</div>
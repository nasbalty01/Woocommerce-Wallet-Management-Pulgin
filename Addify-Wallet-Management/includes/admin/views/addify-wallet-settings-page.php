<?php
defined('ABSPATH') || exit;

// Handle form submission
if (isset($_POST['save_payment_settings'])) {

	// For Security check
	$nonce = isset($_POST['addf_wm_nonce']) ? sanitize_text_field($_POST['addf_wm_nonce']) : '';
	if (!wp_verify_nonce($nonce, 'addf_wm_nonce')) {
		wp_die(esc_html__('Nonce verification failed!', 'woo_addf_wm'));
	}

	// Save the payment settings
	update_option('wallet_payment_enabled', isset($_POST['wallet_payment_enabled']) ? 'yes' : 'no');
	update_option('wallet_payment_title', isset($_POST['wallet_payment_title']) ? sanitize_text_field(wp_unslash($_POST['wallet_payment_title'])) : '');
	update_option('wallet_payment_description', isset($_POST['wallet_payment_description']) ? sanitize_textarea_field(wp_unslash($_POST['wallet_payment_description'])) : '');
	update_option('expiry_date', isset($_POST['expiry_date']) ? sanitize_text_field(wp_unslash($_POST['expiry_date'])) : '');
	update_option('wallet_payment_otp_message', isset($_POST['wallet_payment_otp_message']) ? sanitize_textarea_field(wp_unslash($_POST['wallet_payment_otp_message'])) : '');
	update_option('wallet_payment_min_amount', isset($_POST['wallet_payment_min_amount']) ? sanitize_text_field(wp_unslash($_POST['wallet_payment_min_amount'])) : '');
	update_option('wallet_payment_max_wallet', isset($_POST['wallet_payment_max_wallet']) ? sanitize_text_field(wp_unslash($_POST['wallet_payment_max_wallet'])) : '');
	update_option('wallet_payment_max_transfer', isset($_POST['wallet_payment_max_transfer']) ? sanitize_text_field(wp_unslash($_POST['wallet_payment_max_transfer'])) : '');
	update_option('wallet_payment_min_transfer', isset($_POST['wallet_payment_min_transfer']) ? sanitize_text_field(wp_unslash($_POST['wallet_payment_min_transfer'])) : '');
	update_option('wallet_payment_otp_validation_time', isset($_POST['wallet_payment_otp_validation_time']) ? sanitize_text_field(wp_unslash($_POST['wallet_payment_otp_validation_time'])) : '');
	update_option('wallet_payment_max_pay', isset($_POST['wallet_payment_max_pay']) ? sanitize_text_field(wp_unslash($_POST['wallet_payment_max_pay'])) : '');
	update_option('wallet_payment_min_pay', isset($_POST['wallet_payment_min_pay']) ? sanitize_text_field(wp_unslash($_POST['wallet_payment_min_pay'])) : '');
	update_option('expiry_date_en_dis_option', isset($_POST['expiry_date_en_dis_option']) ? 'yes' : 'no');
	
	$order_statuses_select = isset($_POST['order_statuses_select']) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['order_statuses_select'] ) ) : array();
	update_option('order_statuses_select', $order_statuses_select);

	// Save the style settings
	update_option('custom_cashback_message_color', isset($_POST['custom_cashback_message_color']) ? sanitize_hex_color($_POST['custom_cashback_message_color']) : '');
	update_option('custom_cashback_message_border_color', isset($_POST['custom_cashback_message_border_color']) ? sanitize_hex_color($_POST['custom_cashback_message_border_color']) : '');
	update_option('custom_cashback_message_bg_color', isset($_POST['custom_cashback_message_bg_color']) ? sanitize_hex_color($_POST['custom_cashback_message_bg_color']) : '');
	update_option('custom_cashback_message_padding', isset($_POST['custom_cashback_message_padding']) ? sanitize_text_field($_POST['custom_cashback_message_padding']) : '');
	update_option('custom_cashback_message_border_width', isset($_POST['custom_cashback_message_border_width']) ? sanitize_text_field($_POST['custom_cashback_message_border_width']) : '');
	update_option('custom_cashback_message_border_radius', isset($_POST['custom_cashback_message_border_radius']) ? sanitize_text_field($_POST['custom_cashback_message_border_radius']) : '');
	update_option('custom_cashback_message_text_align', isset($_POST['custom_cashback_message_text_align']) ? sanitize_text_field($_POST['custom_cashback_message_text_align']) : '');
	update_option('custom_cashback_message_text_size', isset($_POST['custom_cashback_message_text_size']) ? sanitize_text_field($_POST['custom_cashback_message_text_size']) : '');
	update_option('custom_cashback_message_text_font', isset($_POST['custom_cashback_message_text_font']) ? sanitize_text_field($_POST['custom_cashback_message_text_font']) : '');
	update_option('custom_cashback_message_font_weight', isset($_POST['custom_cashback_message_font_weight']) ? sanitize_text_field($_POST['custom_cashback_message_font_weight']) : '');

	echo '<div class="updated"><p>' . esc_html__('Wallet settings saved!', 'woo_addf_wm') . '</p></div>';
}

// Get the current payment settings
$wallet_payment_enabled = get_option('wallet_payment_enabled', 'no');
$wallet_payment_title = get_option('wallet_payment_title', esc_html__('Pay via Wallet', 'woo_addf_wm'));
$wallet_payment_description = get_option('wallet_payment_description', esc_html__('Pay with amount in your wallet', 'woo_addf_wm'));
$wallet_payment_otp_message = get_option('wallet_payment_otp_message', esc_html__('Your six digit OTP for wallet transaction has been successfully sent to the email', 'woo_addf_wm'));
$expiry_date = get_option('expiry_date', '10');
$wallet_payment_min_amount = get_option('wallet_payment_min_amount', '1');
$wallet_payment_max_wallet = get_option('wallet_payment_max_wallet', '50');
$wallet_payment_max_transfer = get_option('wallet_payment_max_transfer', '10');
$wallet_payment_min_transfer = get_option('wallet_payment_min_transfer', '1');
$wallet_payment_otp_validation_time = get_option('wallet_payment_otp_validation_time', '2');
$wallet_payment_max_pay = get_option('wallet_payment_max_pay', '100');
$wallet_payment_min_pay = get_option('wallet_payment_min_pay', '1');
$expiry_date_en_dis_option = get_option('expiry_date_en_dis_option', 'no');
$order_statuses = wc_get_order_statuses();
$selected_statuses = get_option('order_statuses_select', '');

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

global $addify_wallet_management_nonce;
$nonce = $addify_wallet_management_nonce;

?>

<div class="wrap woocommerce">
	<h1><?php esc_html_e('Wallet settings', 'woo_addf_wm'); ?></h1>
	<form method="post" action="" autocomplete="off" class="wallet-payment-settings-form">
		<table class="form-table">
			<input type="hidden" name="addf_wm_nonce" value="<?php echo esc_attr($nonce); ?>" />
			<tr>
				<th><label for="wallet_payment_enabled"><?php esc_html_e('Enable wallet', 'woo_addf_wm'); ?></label></th>
				<td><input type="checkbox" name="wallet_payment_enabled" id="wallet_payment_enabled" <?php checked($wallet_payment_enabled, 'yes'); ?>>
				<p class="description"><?php echo esc_html__('Enable wallet payment on checkout page.', 'woo_addf_wm'); ?></p>
			</td>
			</tr>
			<tr>
				<th><label for="wallet_payment_title"><?php esc_html_e('Wallet title', 'woo_addf_wm'); ?></label></th>
				<td><input type="text" name="wallet_payment_title" id="wallet_payment_title" value="<?php echo esc_attr($wallet_payment_title); ?>">
				<p class="description"><?php echo esc_html__('Wallet title on checkout page.', 'woo_addf_wm'); ?></p>
			</td>
			</tr>
			<tr>
				<th><label for="wallet_payment_description"><?php esc_html_e('Wallet description', 'woo_addf_wm'); ?></label></th>
				<td><textarea name="wallet_payment_description" id="wallet_payment_description"><?php echo esc_html($wallet_payment_description); ?></textarea>
				<p class="description"><?php echo esc_html__('Wallet description on checkout page.', 'woo_addf_wm'); ?></p>
			</td>
			</tr>
			<tr>
				<th><label for="wallet_payment_otp_message"><?php esc_html_e('Wallet otp message', 'woo_addf_wm'); ?></label></th>
				<td><textarea name="wallet_payment_otp_message" id="wallet_payment_otp_message"><?php echo esc_html($wallet_payment_otp_message); ?></textarea>
				<p class="description"><?php echo esc_html__('Wallet otp message on checkout page.', 'woo_addf_wm'); ?></p>
			</td>
			</tr>
			
			<tr>
				<th><label for="expiry_date_en_dis_option"><?php esc_html_e('Recharge and transfer amount validaty', 'woo_addf_wm'); ?></label></th>
				<td><input type="checkbox" name="expiry_date_en_dis_option" id="expiry_date_en_dis_option" <?php checked($expiry_date_en_dis_option, 'yes'); ?>>
				<p class="description"><?php echo esc_html__('Enable rcharge and transfor amount validaty.', 'woo_addf_wm'); ?></p>
			</td>
			</tr>
			<tr>
				<th><label for="wallet_payment_otp_message"><?php esc_html_e('Wallet recharge and transfer amount valid for days', 'woo_addf_wm'); ?></label></th>
				<td> <input type="number" name="expiry_date" id="expiry_date" class="expiry_date" value="<?php echo esc_attr($expiry_date); ?>" min="1">
				<p class="description"><?php echo esc_html__('Enter the wallet recharge and transfer amount valid for days.', 'woo_addf_wm'); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="order_statuses_select"><?php esc_html_e('Order status', 'woo_addf_wm'); ?></label></th>
				<td>
				<select name="order_statuses_select[]" id="order_statuses_select" data-placeholder="<?php echo esc_html__(' Select order statuses', 'woo_addf_wm'); ?>" multiple="multiple" >
					<?php
					foreach ($order_statuses as $status_key => $status_name) {
						if ('' === $selected_statuses) {
							echo '<option value="' . esc_attr($status_key) . '">' . esc_html($status_name) . '</option>';
						} else {
							$selected = in_array($status_key, $selected_statuses) ? 'selected' : '';
							echo '<option value="' . esc_attr($status_key) . '" ' . esc_attr($selected) . '>' . esc_html($status_name) . '</option>';
						}
					}
					?>
				</select>
				<p class="description"><?php echo esc_html__('Select the order statuses that should trigger actions for requesting a recharge and completing cashback.', 'woo_addf_wm'); ?></p>
			</td>
			</tr>
			<tr>
				<th><label for="wallet_payment_otp_validation_time"><?php esc_html_e('Otp validation time (minutes)', 'woo_addf_wm'); ?></label></th>
				<td><input type="number" name="wallet_payment_otp_validation_time" id="wallet_payment_otp_validation_time" value="<?php echo esc_attr($wallet_payment_otp_validation_time); ?>" min="0"></td>
			</tr>
			<tr>
				<th><label for="wallet_payment_min_amount"><?php esc_html_e('Minimum wallet credit amount', 'woo_addf_wm'); ?></label></th>
				<td><input type="number" name="wallet_payment_min_amount" id="wallet_payment_min_amount" value="<?php echo esc_attr($wallet_payment_min_amount); ?>" min="0"></td>
			</tr>
			<tr>
				<th><label for="wallet_payment_max_wallet"><?php esc_html_e('Maximum wallet credit amount', 'woo_addf_wm'); ?></label></th>
				<td><input type="number" name="wallet_payment_max_wallet" id="wallet_payment_max_wallet" value="<?php echo esc_attr($wallet_payment_max_wallet); ?>" min="0"></td>
			</tr>
			<tr>
				<th><label for="wallet_payment_min_transfer"><?php esc_html_e('Minimum amount transfer from wallet', 'woo_addf_wm'); ?></label></th>
				<td><input type="number" name="wallet_payment_min_transfer" id="wallet_payment_min_transfer" value="<?php echo esc_attr($wallet_payment_min_transfer); ?>" min="0"></td>
			</tr>
			<tr>
				<th><label for="wallet_payment_max_transfer"><?php esc_html_e('Maximum amount transfer from wallet', 'woo_addf_wm'); ?></label></th>
				<td><input type="number" name="wallet_payment_max_transfer" id="wallet_payment_max_transfer" value="<?php echo esc_attr($wallet_payment_max_transfer); ?>" min="0"></td>
			</tr>
			<tr>
				<th><label for="wallet_payment_min_pay"><?php esc_html_e('Minimum pay from wallet amount', 'woo_addf_wm'); ?></label></th>
				<td><input type="number" name="wallet_payment_min_pay" id="wallet_payment_min_pay" value="<?php echo esc_attr($wallet_payment_min_pay); ?>" min="0"></td>
			</tr>
			<tr>
				<th><label for="wallet_payment_max_pay"><?php esc_html_e('Maximum pay from wallet amount', 'woo_addf_wm'); ?></label></th>
				<td><input type="number" name="wallet_payment_max_pay" id="wallet_payment_max_pay" value="<?php echo esc_attr($wallet_payment_max_pay); ?>" min="0"></td>
			</tr>
		</table>

		<h1><?php esc_html_e('Cashback Messages Settings', 'woo_addf_wm'); ?></h1>
		<div class="cashback-settings-wrapper">
			<table class="form-table">
			<tr>
				<th><label for="custom_cashback_message_color"><?php esc_html_e('Text color', 'woo_addf_wm'); ?></label></th>
				<td><input type="text" name="custom_cashback_message_color" id="custom_cashback_message_color" value="<?php echo esc_attr($custom_cashback_message_color); ?>" class="wp-color-picker-field"></td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_bg_color"><?php esc_html_e('Background color', 'woo_addf_wm'); ?></label></th>
				<td><input type="text" name="custom_cashback_message_bg_color" id="custom_cashback_message_bg_color" value="<?php echo esc_attr($custom_cashback_message_bg_color); ?>" class="wp-color-picker-field"></td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_border_color"><?php esc_html_e('Border color', 'woo_addf_wm'); ?></label></th>
				<td><input type="text" name="custom_cashback_message_border_color" id="custom_cashback_message_border_color" value="<?php echo esc_attr($custom_cashback_message_border_color); ?>" class="wp-color-picker-field"></td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_border_width"><?php esc_html_e('Border width', 'woo_addf_wm'); ?></label></th>
				<td>
				<select name="custom_cashback_message_border_width" id="custom_cashback_message_border_width">
				<?php for ($i = 1; $i <= 20; $i++) : ?>
					<option value="<?php echo esc_attr($i . 'px'); ?>" <?php selected($custom_cashback_message_border_width, $i . 'px'); ?>><?php echo esc_html($i . 'px'); ?></option>
				<?php endfor; ?>
			</select>


				</td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_border_radius"><?php esc_html_e('Border radius', 'woo_addf_wm'); ?></label></th>
				<td>
				<select name="custom_cashback_message_border_radius" id="custom_cashback_message_border_radius">
				<?php for ($i = 0; $i <= 20; $i++) : ?>
					<option value="<?php echo esc_attr($i . 'px'); ?>" <?php selected($custom_cashback_message_border_radius, $i . 'px'); ?>><?php echo esc_html($i . 'px'); ?></option>
				<?php endfor; ?>
			</select>

				</td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_padding"><?php esc_html_e('Padding', 'woo_addf_wm'); ?></label></th>
				<td>
					<select name="custom_cashback_message_padding" id="custom_cashback_message_padding">
					<?php for ($i = 1; $i <= 20; $i++) : ?>
						<option value="<?php echo esc_attr($i . 'px'); ?>" <?php selected($custom_cashback_message_padding, $i . 'px'); ?>><?php echo esc_html($i . 'px'); ?></option>
					<?php endfor; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_text_align"><?php esc_html_e('Text align', 'woo_addf_wm'); ?></label></th>
				<td>
					<select name="custom_cashback_message_text_align" id="custom_cashback_message_text_align">
						<option value="left" <?php selected($custom_cashback_message_text_align, 'left'); ?>>Left</option>
						<option value="center" <?php selected($custom_cashback_message_text_align, 'center'); ?>>Center</option>
						<option value="right" <?php selected($custom_cashback_message_text_align, 'right'); ?>>Right</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_text_size"><?php esc_html_e('Text Size', 'woo_addf_wm'); ?></label></th>
				<td>
				<select name="custom_cashback_message_text_size" id="custom_cashback_message_text_size">
				<?php for ($i = 10; $i <= 40; $i++) : ?>
					<option value="<?php echo esc_attr($i . 'px'); ?>" <?php selected($custom_cashback_message_text_size, $i . 'px'); ?>><?php echo esc_html($i . 'px'); ?></option>
				<?php endfor; ?>
			</select>

				</td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_text_font"><?php esc_html_e('Text style', 'woo_addf_wm'); ?></label></th>
				<td>
					<select name="custom_cashback_message_text_font" id="custom_cashback_message_text_font">
						<option value="normal" <?php selected($custom_cashback_message_text_font, 'normal'); ?>>Normal</option>
						<option value="italic" <?php selected($custom_cashback_message_text_font, 'italic'); ?>>Italic</option>
				
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="custom_cashback_message_font_weight"><?php esc_html_e('Font weight', 'woo_addf_wm'); ?></label></th>
				<td>
					<select name="custom_cashback_message_font_weight" id="custom_cashback_message_font_weight">
					<option value="100" <?php selected($custom_cashback_message_font_weight, '100'); ?>>100</option>
					<option value="200" <?php selected($custom_cashback_message_font_weight, '200'); ?>>200</option>
					<option value="300" <?php selected($custom_cashback_message_font_weight, '300'); ?>>300</option>
					<option value="400" <?php selected($custom_cashback_message_font_weight, '400'); ?>>400</option>
					<option value="500" <?php selected($custom_cashback_message_font_weight, '500'); ?>>500</option>
					<option value="600" <?php selected($custom_cashback_message_font_weight, '600'); ?>>600</option>
					<option value="700" <?php selected($custom_cashback_message_font_weight, '700'); ?>>700</option>
					<option value="800" <?php selected($custom_cashback_message_font_weight, '800'); ?>>800</option>
					<option value="900" <?php selected($custom_cashback_message_font_weight, '900'); ?>>900</option>
				</select>

				</td>
			</tr>
		</table>
			</table>
			<p class="custom-cashback-message-preview">This is a preview of the custom cashback message.</p>
		</div>
		
	  
		<p class="submit"><input type="submit" name="save_payment_settings" id="save_payment_settings" class="button button-primary button-large" value="<?php esc_attr_e('Save Changes', 'woo_addf_wm'); ?>"></p>
	</form>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('.wp-color-picker-field').wpColorPicker();

		$('select[name="custom_cashback_message_padding"], select[name="custom_cashback_message_font_weight"], select[name="custom_cashback_message_border_width"], select[name="custom_cashback_message_border_radius"], select[name="custom_cashback_message_text_align"], select[name="custom_cashback_message_text_size"], select[name="custom_cashback_message_text_font"]').change(function() {
			updatePreview();
		});

		function updatePreview() {
			
			var textColor = $('#custom_cashback_message_color').val();
			var borderColor = $('#custom_cashback_message_border_color').val();
			var bgColor = $('#custom_cashback_message_bg_color').val();
			var padding = $('#custom_cashback_message_padding').val();
			var borderWidth = $('#custom_cashback_message_border_width').val();
			var borderRadius = $('#custom_cashback_message_border_radius').val();
			var textAlign = $('#custom_cashback_message_text_align').val();
			var textSize = $('#custom_cashback_message_text_size').val();
			var textFont = $('#custom_cashback_message_text_font').val();
			var textFontWeight = $('#custom_cashback_message_font_weight').val();

			$('.custom-cashback-message-preview').css({
				'color': textColor,
				// 'border-color': borderColor,
				'background-color': bgColor,
				'padding': padding,
				'border': borderWidth+' solid '+ borderColor,
				'border-radius': borderRadius,
				'text-align': textAlign,
				'font-size': textSize,
				'font-style': textFont,
				'font-weight': textFontWeight
			});
		}

		updatePreview();

		$('.wp-color-picker-field').wpColorPicker({
			change: function(event, ui) {
				updatePreview();
			}
		});
	});
</script>



<style type="text/css">
	.cashback-settings-wrapper {
		display: flex;
		align-items: flex-start;
	}

	.cashback-settings-wrapper .form-table {
		flex: 1;
	}

	.custom-cashback-message-preview {
		margin-right: 500px;
		flex: 1;
	}
</style>

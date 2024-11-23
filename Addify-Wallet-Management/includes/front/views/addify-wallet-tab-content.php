<?php
defined('ABSPATH') || exit;

global $wp;
global $wpdb;

// Get current user
$user = wp_get_current_user();
if ($user->ID) {
	$user_id = $user->ID;
}

$wallet_amount_total = 0;
$wallet_amount = $wpdb->get_var($wpdb->prepare('SELECT total_amount FROM wm_wallet_amount WHERE customer_id = %d', $user_id));
$wallet_amount_total += $wallet_amount;

$current_url = get_permalink();
$wallet_transfer_url = esc_url($current_url . 'customer-wallet/transfer');

$recharge_amount_check = $wpdb->get_var('SELECT recharge_amount FROM wm_recharge_amount');
$wallet_payment_min_amount = get_option('wallet_payment_min_amount', '1');
$wallet_payment_max_wallet = get_option('wallet_payment_max_wallet', '50');
?>
<div class="wallet-head">
</div>
<div class="wallet-container">
	<h4 class="wallet-amount"><?php esc_html_e('Wallet Balance:', 'woo_addf_wm'); ?></h4>
	<div class="wallet-wrapper custom-wrapper">
		<p class="wallet-amount-value">
			<?php echo wp_kses_post(wc_price($wallet_amount_total)); ?>&nbsp;
			<a href="<?php echo esc_url($wallet_transfer_url); ?>" class="wallet-transfer-link"><?php esc_html_e('Wallet Transfer', 'woo_addf_wm'); ?></a>
		</p>
		
		<!-- Display input field to enter the amount -->
		<form method="post" id="add_wallet_amount_form" class="wallet-form">
			<input type="text" id="wallet_payment_min_amount" value="<?php echo esc_attr($wallet_payment_min_amount); ?>" readonly>
			<input type="text" id="wallet_payment_max_wallet" value="<?php echo esc_attr($wallet_payment_max_wallet); ?>" readonly>
			<input type="text" id="wallet_recharge_amount" value="<?php echo esc_attr($recharge_amount_check); ?>" readonly>
			<input type="number" class="input-text wallet-input" placeholder = "<?php echo esc_html( 'Enter amount to recharge', 'woo_addf_wm' ); ?>" name="wallet_amount" id="wallet_amount" step="any" min="0" required>
			<button type="button" class="button wp-element-button wallet-button" id="add_to_wallet_button"><?php esc_html_e('Add to Wallet', 'woo_addf_wm'); ?></button>
		</form>
	</div>
</div>

<div class="wallet-transactions-wrapper">
	<?php
	echo '<h4 class="transition_history">' . esc_html__('Wallet Transactions History', 'woo_addf_wm') . '</h4>';
	?>

	<div class="wrap table-responsive">
		<table id="table-table-front" class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<th><?php esc_html_e('ID', 'woo_addf_wm'); ?></th>
					<th><?php esc_html_e('Reference', 'woo_addf_wm'); ?></th>
					<th><?php esc_html_e('Amount', 'woo_addf_wm'); ?></th>
					<th><?php esc_html_e('Type', 'woo_addf_wm'); ?></th>
					<th><?php esc_html_e('Date', 'woo_addf_wm'); ?></th>
					<th><?php esc_html_e('Expiry Date', 'woo_addf_wm'); ?></th>
					<th><?php esc_html_e('Status', 'woo_addf_wm'); ?></th>
					<th class="action"><?php esc_html_e('Action', 'woo_addf_wm'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$results = $wpdb->get_results($wpdb->prepare('SELECT * FROM wm_wallet_data WHERE customer_id = %d AND action = %s ORDER BY id DESC',
	$user_id, '1'));

				if (!$results) {
					echo '<div class="woocommerce-info custom custom-info">' . esc_html__('No Transaction details available yet.', 'woo_addf_wm') . '</div>';
					exit;
				}

				foreach ($results as $result) {
					$idd = $result->id;
					$reference = $result->reference;
					$amount = $result->amount;
					$transaction_type = $result->transaction_type;
					$date = $result->date;
					$expiry_date = $result->expiry_date;
					$statues = $result->status;

					$current_url = home_url(add_query_arg(array(), $wp->request));
					$wallet_details_url = $current_url . '/view/' . $result->id;

					echo '<tr>';
					echo '<td><a href="' . esc_url($wallet_details_url) . '">#' . esc_attr($idd) . '</a></td>';
					echo '<td class="column-reference">' . esc_html($reference) . '</td>';
					echo '<td><span class="woocommerce-Price-amount amount">' . wp_kses_post(wc_price($amount)) . '</span></td>';
					echo '<td>' . esc_html($transaction_type) . '</td>';
					echo '<td class="column-date">' . esc_attr(date_format(date_create($date), 'M d, Y g:i:s A')) . '</td>';
					if ('–' === $expiry_date) {
						echo '<td>' . esc_attr($expiry_date) . '</td>';
					} else {
						echo '<td>' . esc_attr(date_format(date_create($expiry_date), 'M d, Y')) . '</td>';
					}
					
					echo '<td class="column-status">' . esc_html($statues) . '</td>';
					echo '<td><a href="' . esc_url($wallet_details_url) . '" class="woocommerce-button button view">' . esc_html__('View', 'woo_addf_wm') . '</a></td>';
					echo '</tr>';
				}
				?>
			</tbody>
		</table>
		<!-- <div id="pagination" class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<button id="prev-page" class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button"><?php esc_html_e('Previous', 'woo_addf_wm'); ?></button>
			<button id="next-page" class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button"><?php esc_html_e('Next', 'woo_addf_wm'); ?></button>
		</div> -->
	</div>
</div>


</script>

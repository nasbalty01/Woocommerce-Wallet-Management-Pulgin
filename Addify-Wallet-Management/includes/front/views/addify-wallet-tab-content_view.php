<?php
defined('ABSPATH') || exit;

global $wpdb;

function get_current_url_id() {
	global $wp;
	$current_url = add_query_arg($wp->query_string, '', home_url($wp->request));
	$path_parts = explode('/', parse_url($current_url, PHP_URL_PATH));
	$id = intval(end($path_parts));
	return $id;
}

$url_id = get_current_url_id();

$current_url = get_permalink();


// Use $wpdb->get_row to fetch a single row from the database
$row_url_id = $wpdb->get_row(
	$wpdb->prepare(
		'SELECT * FROM wm_wallet_data WHERE id = %d',
		$url_id
	)
);

// Check if a row was found
if ($row_url_id) {
	// Assign values to variables
	$transaction_id = $row_url_id->id;
	$order_id = $row_url_id->order_id;
	$reference = $row_url_id->reference;
	$customer_id = $row_url_id->customer_id;
	$amount = $row_url_id->amount;
	$transaction_type = $row_url_id->transaction_type;
	$transaction_action = $row_url_id->transaction_action;
	$receiver_email = $row_url_id->receiver_email;
	$payer_email = $row_url_id->payer_email;
	$transaction_note = $row_url_id->transaction_note;
	$date = $row_url_id->date;
	$expiry_date = $row_url_id->expiry_date;
	$statueses = $row_url_id->status;
	$wallet_order_details_url = esc_url($current_url . 'view-order/' . $order_id);
	// Now you can use these variables as needed
} else {
	echo 'No data found';
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html__('Wallet Transaction Details', 'woo_addf_wm'); ?></h1>
	<div class="wallet-transaction-view-wrapper">
		<table class="wallet-transaction-view">
			<tbody>
				<tr>
					<th><?php echo esc_html__('Amount', 'woo_addf_wm'); ?></th>
					<td>
						<span class="woocommerce-Price-amount amount">
							<?php echo wp_kses_post(wc_price($amount)); ?>
						</span>
					</td>
				</tr>

				<?php if ($url_id) : ?>
					<tr>
						<th><?php echo esc_html__('Order ID', 'woo_addf_wm'); ?></th>
						<td>
							<a href="<?php echo esc_url($wallet_order_details_url); ?>">#<?php echo esc_html($order_id); ?></a>
						</td>
					</tr>
				<?php endif; ?>

				<tr>
					<th><?php echo esc_html__('Action', 'woo_addf_wm'); ?></th>
					<td><?php echo esc_html($transaction_action); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Type', 'woo_addf_wm'); ?></th>
					<td><?php echo esc_html($transaction_type); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Reciever', 'woo_addf_wm'); ?></th>
					<td><?php echo esc_html($receiver_email); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Payer', 'woo_addf_wm'); ?></th>
					<td><?php echo esc_html($payer_email); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Transaction On', 'woo_addf_wm'); ?></th>
					<td><?php echo esc_html(date_format(date_create($date), 'M d, Y g:i:s A')); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Expiry Date', 'woo_addf_wm'); ?></th>
					<td>
					<?php 
					if ('–' === $expiry_date) {
						echo esc_html($expiry_date);
					} else {
						echo esc_html(date_format(date_create($expiry_date), 'M d, Y'));
					}
					 
					?>
					</td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Transaction Note', 'woo_addf_wm'); ?></th>
					<td><?php echo esc_html($transaction_note); ?></td>
				</tr>
				<tr>
					<th><?php echo esc_html__('Status', 'woo_addf_wm'); ?></th>
					<td class="column-status"><?php echo esc_html($statueses); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

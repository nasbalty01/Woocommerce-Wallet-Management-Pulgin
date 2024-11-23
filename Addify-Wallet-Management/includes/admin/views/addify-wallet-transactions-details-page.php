
<?php 
defined('ABSPATH') || exit;
   
   global $wpdb;
   $url_id = $transaction_id;


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
		$transition_id = $row_url_id->id;
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
		$order_edit_url = admin_url('post.php?post=' . $order_id . '&action=edit');
	 
		// Now you can use these variables as needed
	} else {
		echo 'No data found';
	}


   

   
	?>

<div class="wrap">
	<h1><?php echo esc_html__( 'Customer Wallet Transaction Details', 'woo_addf_wm' ); ?></h1>
	
	<table class="wallet-transaction-view wp-list-table widefat fixed ">
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Amount', 'woo_addf_wm' ); ?></th>
				<td>
					<span class="woocommerce-Price-amount amount">
						<?php echo wp_kses_post(wc_price($amount)); ?>
					</span>
				</td>
			</tr>
			<?php if ($url_id) : ?>
				<tr>
					<th><?php esc_html_e( 'Order ID', 'woo_addf_wm' ); ?></th>
					<td>
						<a href="<?php echo esc_url($order_edit_url); ?>">#<?php echo esc_attr($order_id); ?></a>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th><?php esc_html_e( 'Action', 'woo_addf_wm' ); ?></th>
				<td>
					<?php echo esc_attr__($transaction_action); ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Type', 'woo_addf_wm' ); ?></th>
				<td>
					<?php echo esc_attr__( $transaction_type ); ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Receiver', 'woo_addf_wm' ); ?></th>
				<td>
					<?php echo esc_attr__($receiver_email); ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Payer', 'woo_addf_wm' ); ?></th>
				<td>
					<?php echo esc_attr__($payer_email); ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Transaction On', 'woo_addf_wm' ); ?></th>
				<td>
					<?php echo esc_attr__(date_format(date_create($date), 'M d, Y g:i:s A')); ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Expire Date', 'woo_addf_wm' ); ?></th>
				<td>
					<?php 
					if ('–' === $expiry_date) {
						echo esc_html($expiry_date);
					} else {
						echo esc_attr__(date_format(date_create($expiry_date), 'M d, Y')); 
					}
					
					?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Transaction Note', 'woo_addf_wm' ); ?></th>
				<td>
					<?php echo esc_attr__($transaction_note); ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Status', 'woo_addf_wm' ); ?></th>
				<td class="column-status">
					<?php echo esc_attr__($statueses); ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>

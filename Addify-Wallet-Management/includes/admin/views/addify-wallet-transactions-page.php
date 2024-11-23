<?php

defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Wallet_Transactions_List_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct(array(
			'singular' => __('Transaction', 'woo_addf_wm'),
			'plural'   => __('Transactions', 'woo_addf_wm'),
			'ajax'     => false,
		));
	}

	public function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'id'               => __('ID', 'woo_addf_wm'),
			'reference'        => __('Reference', 'woo_addf_wm'),
			'username'         => __('Username', 'woo_addf_wm'),
			'email'            => __('Email', 'woo_addf_wm'),
			'amount'           => __('Amount', 'woo_addf_wm'),
			'transaction_type' => __('Transaction Type', 'woo_addf_wm'),
			'date'             => __('Date', 'woo_addf_wm'),
			'expiry_date'      => __('Expiry Date', 'woo_addf_wm'),
			'status'           => __('Status', 'woo_addf_wm'),
		);

		return $columns;
	}

	public function prepare_items() {
		global $wpdb;

		$per_page = $this->get_items_per_page('transactions_per_page', 10);
		$current_page = $this->get_pagenum();
		$search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
		$transaction_type = isset($_REQUEST['transaction_type']) ? sanitize_text_field($_REQUEST['transaction_type']) : '';
		$from_date = isset($_REQUEST['from_date']) ? sanitize_text_field($_REQUEST['from_date']) : '';
		$to_date = isset($_REQUEST['to_date']) ? sanitize_text_field($_REQUEST['to_date']) : '';
		$status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '';

		$orderby = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'id';
		$order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'desc';

		// Adding the action condition
		$where = 'WHERE t.action = %d';
		$where_args = array( 1 );

		if (!empty($search)) {
			$where .= ' AND (t.reference LIKE %s OR u.user_login LIKE %s OR u.user_email LIKE %s)';
			$like_search = '%' . $wpdb->esc_like($search) . '%';
			array_push($where_args, $like_search, $like_search, $like_search);
		}

		if (!empty($transaction_type)) {
			$where .= ' AND t.transaction_type = %s';
			$where_args[] = $transaction_type;
		}

		if (!empty($from_date) && !empty($to_date)) {
			$where .= ' AND t.date BETWEEN %s AND %s';
			array_push($where_args, $from_date, $to_date);
		}

		if (!empty($status)) {
			$where .= ' AND t.status = %s';
			$where_args[] = $status;
		}

		$total_items_sql = "SELECT COUNT(*) FROM wm_wallet_data t
                            LEFT JOIN {$wpdb->users} u ON t.receiver_email = u.user_email
                            $where";
		$total_items = $wpdb->get_var($wpdb->prepare($total_items_sql, $where_args));

		

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		));

		$query_sql = "SELECT t.*, u.user_login as username, u.user_email as email 
                      FROM wm_wallet_data t
                      LEFT JOIN {$wpdb->users} u ON t.receiver_email = u.user_email
                      $where 
                      ORDER BY $orderby $order 
                      LIMIT %d OFFSET %d";
		$query_args = array_merge($where_args, array( $per_page, ( $current_page - 1 ) * $per_page ));
		$query = $wpdb->prepare($query_sql, $query_args);

		$this->items = $wpdb->get_results($query);

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns(), 'cb' );
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'               => array( 'id', true ),
			'reference'        => array( 'reference', false ),
			'username'         => array( 'username', false ),
			'email'            => array( 'email', false ),
			'amount'           => array( 'amount', false ),
			'transaction_type' => array( 'transaction_type', false ),
			'date'             => array( 'date', false ),
			'expiry_date'      => array( 'expiry_date', false ),
			'status'           => array( 'status', false ),
		);

		return $sortable_columns;
	}

	public function column_default( $item, $column_name ) {
		// echo '<pre>';
		// print_r($item);
		// exit;
		switch ($column_name) {
			
			case 'id':
				$current_url = admin_url('admin.php?page=wallet-transactions');
				$wallet_details_url = esc_url($current_url . '&transaction_id=' . intval($item->id));
				return '<a href="' . esc_url($wallet_details_url) . '">#' . esc_html($item->id) . '</a>';
			case 'reference':
				return esc_html($item->reference);
			case 'username':
				$addf_user = get_user_by('id', $item->customer_id);
				return esc_html($addf_user->user_login);
			case 'email':
				$addf_user = get_user_by('id', $item->customer_id);
				return esc_html($addf_user->user_email);
			case 'amount':
				return wp_kses_post(wc_price($item->amount));
			case 'transaction_type':
				return esc_html($item->transaction_type);
			case 'date':
				return esc_html(date_format(date_create($item->date), 'M d, Y g:i:s A'));
			case 'expiry_date':
				if ('–' === $item->expiry_date) {
					return esc_html($item->expiry_date);
				} else {
					return esc_html(date_format(date_create($item->expiry_date), 'M d, Y'));
				}
			case 'status':
				return esc_html($item->status);
			default:
				return print_r($item, true);
		}
	}

	public function column_cb( $item ) {
		return sprintf('<input type="checkbox" name="transaction_ids[]" value="%d" />', $item->id);
	}

	protected function get_bulk_actions() {
		$actions = array(
			'bulk_delete' => __('Delete', 'woo_addf_wm'),
		);

		return $actions;
	}

	public function process_bulk_action() {
		global $wpdb;

		if ('bulk_delete' === $this->current_action()) {
			$transaction_ids = isset($_REQUEST['transaction_ids']) ? array_map('intval', (array) $_REQUEST['transaction_ids']) : array();

			if (!empty($transaction_ids)) {
				// Step 1: Update action to 0 in wm_wallet_data for the specified transaction IDs
				$placeholders = implode(',', array_fill(0, count($transaction_ids), '%d'));
				$wpdb->query($wpdb->prepare("UPDATE wm_wallet_data SET action = 0 WHERE id IN ($placeholders)", $transaction_ids));

				// Step 2: Retrieve customer_ids for the affected transactions
				$customer_ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT customer_id FROM wm_wallet_data WHERE id IN ($placeholders)", $transaction_ids));

				// Step 3: For each customer_id, calculate the sum of amount where action is 0
				foreach ($customer_ids as $customer_id) {
					$total_amount = $wpdb->get_var($wpdb->prepare('SELECT SUM(amount) FROM wm_wallet_data WHERE customer_id = %d AND action = 0', $customer_id));

					// Step 4: Update total_amount in wm_wallet_amount for the customer_id
					$wpdb->query($wpdb->prepare('UPDATE wm_wallet_amount SET total_amount = %d WHERE customer_id = %d', $total_amount, $customer_id));
				}
			}
		}
	}


	public function extra_tablenav( $which ) {
		if ('top' == $which) {
			?>
				<div class="alignleft actions bulkactions">
					<?php
					$transaction_type = isset($_REQUEST['transaction_type']) ? sanitize_text_field($_REQUEST['transaction_type']) : '';
					$status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '';
					$from_date = isset($_REQUEST['from_date']) ? sanitize_text_field($_REQUEST['from_date']) : '';
					$to_date = isset($_REQUEST['to_date']) ? sanitize_text_field($_REQUEST['to_date']) : '';
					?>
					<select name="transaction_type">
						<option value=""><?php esc_html_e('Transaction Type', 'woo_addf_wm'); ?></option>
						<option value="credit" <?php selected($transaction_type, 'credit'); ?>><?php esc_html_e('Credit', 'woo_addf_wm'); ?></option>
						<option value="debit" <?php selected($transaction_type, 'debit'); ?>><?php esc_html_e('Debit', 'woo_addf_wm'); ?></option>
						<option value="transfer" <?php selected($transaction_type, 'transfer'); ?>><?php esc_html_e('Transfer', 'woo_addf_wm'); ?></option>
					</select>
					<select name="status">
						<option value=""><?php esc_html_e('Status', 'woo_addf_wm'); ?></option>
						<option value="active" <?php selected($status, 'active'); ?>><?php esc_html_e('Active', 'woo_addf_wm'); ?></option>
						<option value="expired" <?php selected($status, 'expired'); ?>><?php esc_html_e('Expired', 'woo_addf_wm'); ?></option>
					</select>
					<input type="date" name="from_date" value="<?php echo esc_attr($from_date); ?>" placeholder="<?php esc_attr_e('From Date', 'woo_addf_wm'); ?>" />
					<input type="date" name="to_date" value="<?php echo esc_attr($to_date); ?>" placeholder="<?php esc_attr_e('To Date', 'woo_addf_wm'); ?>" />
					<?php submit_button(__('Filter'), 'secondary', '', false); ?>
				</div>
				<?php
		}
	}
	public static function render_page() {
		$wallet_transactions_list_table = new Wallet_Transactions_List_Table();
		$wallet_transactions_list_table->prepare_items();
		$wallet_transactions_list_table->process_bulk_action();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__('Customer Wallet Transaction Lists', 'woo_addf_wm'); ?></h1>
			<form method="post">
				<?php
				$wallet_transactions_list_table->search_box(__('Search Transactions', 'woo_addf_wm'), 'search_id');
				$wallet_transactions_list_table->display();
				?>
			</form>
		</div>
		<?php
	}
}

if (is_admin()) {
	Wallet_Transactions_List_Table::render_page();
}
?>

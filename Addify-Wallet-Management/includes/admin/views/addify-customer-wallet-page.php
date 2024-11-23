<?php
defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Customer_Wallet_List_Table extends WP_List_Table {
	
	public function __construct() {
		parent::__construct(array(
			'singular' => __('Customer Wallet', 'woo_addf_wm'),
			'plural'   => __('Customer Wallets', 'woo_addf_wm'),
			'ajax'     => false,
		));
	}

	public function get_columns() {
		$columns = array(
			'id'            => __('ID', 'woo_addf_wm'),
			'username'      => __('Username', 'woo_addf_wm'),
			'email'         => __('Email', 'woo_addf_wm'),
			'location'      => __('Location', 'woo_addf_wm'),
			'orders'        => __('Orders', 'woo_addf_wm'),
			'wallet_balance'=> __('Wallet Balance', 'woo_addf_wm'),
			'last_order'    => __('Last Order', 'woo_addf_wm'),
			'actions'       => __('Actions', 'woo_addf_wm'),
		);

		return $columns;
	}

	public function prepare_items() {
		
		global $wpdb;
	
		$per_page = $this->get_items_per_page('customers_per_page', 10);
		$current_page = $this->get_pagenum();
		$search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
	
		// Get the sorting parameters
		$orderby = isset($_REQUEST['orderby']) ? sanitize_key($_REQUEST['orderby']) : 'login';
		$order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'ASC';
	
		// Wallet Balance Filters
		$wallet_filter_type = isset($_REQUEST['wallet_filter_type']) ? sanitize_text_field($_REQUEST['wallet_filter_type']) : '';
		$wallet_min = isset($_REQUEST['wallet_min']) ? floatval($_REQUEST['wallet_min']) : 0;
		$wallet_max = isset($_REQUEST['wallet_max']) ? floatval($_REQUEST['wallet_max']) : 0;
	
		$args = array(
			'number'  => $per_page,
			'offset'  => ( $current_page - 1 ) * $per_page,
			'search'  => '*' . esc_attr($search) . '*',
			'orderby' => $orderby,
			'order'   => $order,
		);
	
		// Custom query to filter wallet balance
		$users = get_users($args);
		if ($wallet_filter_type && ( ( 'less' == $wallet_filter_type && $wallet_min > 0 ) || 
									( 'more' == $wallet_filter_type && $wallet_min > 0 ) || 
									( 'between' == $wallet_filter_type && $wallet_min > 0 && $wallet_max > 0 ) )) {
			$filtered_users = array();
			foreach ($users as $user) {
				$wallet_amount = $wpdb->get_var($wpdb->prepare('SELECT total_amount FROM wm_wallet_amount WHERE customer_id = %d', $user->ID));
				$wallet_amount = $wallet_amount ? $wallet_amount : 0;
	
				if ('less' == $wallet_filter_type && $wallet_amount < $wallet_min) {
					$filtered_users[] = $user;
				} elseif ('more' == $wallet_filter_type && $wallet_amount > $wallet_min) {
					$filtered_users[] = $user;
				} elseif ('between' == $wallet_filter_type && $wallet_amount >= $wallet_min && $wallet_amount <= $wallet_max) {
					$filtered_users[] = $user;
				}
			}
			$users = $filtered_users;
		}
	
		$total_items = count($users);
	
		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
		));
	
		$this->items = $users;
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns(), 'id' );
	}
	

	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'           => array( 'ID', true ),
			'username'     => array( 'user_login', false ),
			'email'        => array( 'user_email', false ),
			'orders'       => array( 'orders', false ),
			'wallet_balance' => array( 'wallet_balance', false ),
			'last_order'   => array( 'last_order', false ),
		);

		return $sortable_columns;
	}

	public function column_default( $item, $column_name ) {
		global $wpdb;

		

		switch ($column_name) {
			case 'id':
				return esc_html($item->ID);
			case 'username':
				return esc_html($item->user_login);
			case 'email':
				return esc_html($item->user_email);
			case 'location':
				$customer = new WC_Customer($item->ID);
				if (!$customer || is_wp_error($customer)) {
					return '-';
				}
				$city = $customer->get_billing_city();
				$state = $customer->get_billing_state();
				$country = $customer->get_billing_country();
				return esc_html(implode(', ', array_filter(array( $city, $state, $country ))));
			case 'orders':
				return esc_html(wc_get_customer_order_count($item->ID));
			case 'wallet_balance':
				$wallet_amount = $wpdb->get_var($wpdb->prepare('SELECT total_amount FROM wm_wallet_amount WHERE customer_id = %d', $item->ID));
				return wp_kses_post(wc_price($wallet_amount ? $wallet_amount : 0));
			case 'last_order':
				$last_order_args = array(
					'customer_id' => $item->ID,
					'status'      => array( 'completed', 'processing' ),
					'limit'       => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
				);
				$last_order = wc_get_orders($last_order_args);
				if ($last_order) {
					$last_order_id = $last_order[0]->get_id();
					$last_order_date = date_format(date_create($last_order[0]->get_date_created()->date('Y-m-d H:i:s')), 'M d, Y g:i:s A');
					return '<a href="' . esc_url(admin_url('post.php?post=' . intval($last_order_id) . '&action=edit')) . '">Order #' . esc_html($last_order_id) . ' – ' . esc_html($last_order_date) . '</a>';
				} else {
					return '-';
				}
			case 'actions':
				$current_url = admin_url('admin.php?page=customer-wallet');
				$manual_wallet_url = $current_url . '&action_update=' . $item->ID;
				return '<a href="' . esc_url(get_edit_user_link($item->ID)) . '" class="page-title-action">Edit User</a> 
                        <a href="' . esc_url(admin_url('admin.php?page=wc-orders&_customer_user=' . intval($item->ID))) . '" class="page-title-action">View Orders</a>
                        <a href="' . esc_url($manual_wallet_url) . '" class="page-title-action">Manual Transaction</a>';
			default:
				return print_r($item, true);
		}
	}

	public function extra_tablenav( $which ) {
		if ('top' == $which) {
			?>
			 <div class="alignleft actions">
					<label for="filter_wallet_balance" class="screen-reader-text"><?php esc_html_e('Wallet Balance', 'woo_addf_wm'); ?></label>
					<select name="wallet_filter_type" id="filter_wallet_balance">
						<option value=""><?php esc_html_e('Wallet Balance', 'woo_addf_wm'); ?></option>
						<option value="less" <?php selected('less', isset($_REQUEST['wallet_filter_type']) ? sanitize_text_field($_REQUEST['wallet_filter_type']) : ''); ?>><?php esc_html_e('Less Than', 'woo_addf_wm'); ?></option>
						<option value="more" <?php selected('more', isset($_REQUEST['wallet_filter_type']) ? sanitize_text_field($_REQUEST['wallet_filter_type']) : ''); ?>><?php esc_html_e('More Than', 'woo_addf_wm'); ?></option>
						<option value="between" <?php selected('between', isset($_REQUEST['wallet_filter_type']) ? sanitize_text_field($_REQUEST['wallet_filter_type']) : ''); ?>><?php esc_html_e('Between', 'woo_addf_wm'); ?></option>
					</select>

					<input type="number" name="wallet_min" id="wallet_min" value="<?php echo isset($_REQUEST['wallet_min']) ? esc_attr(sanitize_text_field($_REQUEST['wallet_min'])) : ''; ?>" />
					<input type="number" name="wallet_max" id="wallet_max" value="<?php echo isset($_REQUEST['wallet_max']) ? esc_attr(sanitize_text_field($_REQUEST['wallet_max'])) : ''; ?>" />
					<input type="submit" id="wallet-query-submit" class="button" value="<?php esc_html_e('Filter', 'woo_addf_wm'); ?>">

				</div>
	<?php
	
		}
	}

	public static function render_page() {
		$customer_wallet_list_table = new Customer_Wallet_List_Table();
		$customer_wallet_list_table->prepare_items();
		?>
		<div class="wrap">
			<h1><?php echo esc_html__('Customer Wallet', 'woo_addf_wm'); ?></h1>
			<form method="get">
				<input type="hidden" name="page" value="customer-wallet" />
				<?php $customer_wallet_list_table->search_box(__('Search Users', 'woo_addf_wm'), 'search_id'); ?>

				<?php $customer_wallet_list_table->display(); ?>
			</form>
		</div>


		<?php
	}
}



if (is_admin()) {
	Customer_Wallet_List_Table::render_page();
}
?>

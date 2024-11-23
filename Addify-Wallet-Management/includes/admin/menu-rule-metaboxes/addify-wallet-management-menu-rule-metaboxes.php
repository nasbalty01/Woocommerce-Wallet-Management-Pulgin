<?php 
defined('ABSPATH') || exit;



class Addify_Wallet_Management_Menu_Rule_Metaboxes {

   
	public function __construct() {
		 // add to menu
		add_action('admin_menu', array( $this, 'addf_custom_wallet_menu' ));

		add_action('add_meta_boxes', array( $this, 'addf_cashback_rule_meta_boxes' ));
		add_action('save_post', array( $this, 'addf_save_cashback_rule_meta_data' ));
		add_filter('manage_edit-cashback_rule_columns', array( $this, 'addf_reorder_custom_columns_in_cashback_rules_table' ));
		add_action('manage_cashback_rule_posts_custom_column', array( $this, 'addf_display_custom_data_in_cashback_rules_table' ), 10, 2);
		add_action('all_admin_notices', array( $this, 'display_tabs' ), 5);

		add_action('wp_ajax_post_purchase_offer_getproductsearch', array( $this, 'addf_getproductsearch_cb' ));
		add_action('wp_ajax_nopriv_post_purchase_offer_getproductsearch', array( $this, 'addf_getproductsearch_cb' ));
	}

	public function addf_getproductsearch_cb() {
		$return = array();
		$search = '';
		if (isset($_GET['q'])) {
		$search = sanitize_text_field(wp_unslash($_GET['q']));
		}
		if (
			isset($_POST['search_fields_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['q'], 'search_fields_nonce')))
		) {
			die(esc_html__('Sorry, your nonce did not verify.', 'woo_addf_ppp'));
		}

		$post_aurgs  = array(
			's' => $search,
			'post_type' => array( 'product', 'product_variation' ),
			'post_status' => 'publish',
			'posts_per_page' => 200,
			'orderby' => 'title',
			'order' => 'ASC',
			'fields' => 'ids',
		); 
		$search_results = get_posts( $post_aurgs);

		foreach ($search_results as $product_id) {
			$product = wc_get_product($product_id);
					$title = ( mb_strlen($product->get_name()) > 50 )
						? mb_substr($product->get_name(), 0, 49) . '...'
						: $product->get_name();
					$return[] = array( $product_id, $title );          
		}
		
		wp_send_json($return);
	}

	public function display_tabs() {
		global $post, $typenow;
		$screen = get_current_screen();

		if ($screen && in_array($screen->id, $this->get_tab_screen_ids(), true)) {
			// List of your tabs.
			$tabs = array(
			
				'customer-wallet'    => array(
					'title' => __('Customer Wallet', 'woo_addf_wm'), // title of tab
					'url'   => admin_url('admin.php?page=customer-wallet'), // URL of tab
				),
				'wallet-transactions'    => array(
					'title' => __('Transactions', 'woo_addf_wm'), // title of tab
					'url'   => admin_url('admin.php?page=wallet-transactions'), // URL of tab
				),
				'cashback_rule'        => array(
					'title' => __('Cashback Rules', 'woo_addf_wm'), // title of tab
					'url'   => admin_url('edit.php?post_type=cashback_rule'), // Url of tab
				),
				'wallet-recharge'    => array(
					'title' => __('Max Balance', 'woo_addf_wm'), // title of tab
					'url'   => admin_url('admin.php?page=wallet-recharge'), // URL of tab
				),
				'wallet-settings'    => array(
					'title' => __('Wallet Setting', 'woo_addf_wm'), // title of tab
					'url'   => admin_url('admin.php?page=wallet-settings'), // URL of tab
				),
				
			);

			if (is_array($tabs)) { ?>
			<div class="wrap woocommerce">
				<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
						<?php 
						$current_tab = $this->get_current_tab();
						foreach ($tabs as $id => $tab_data) {
							$class = $id === $current_tab ? array( 'nav-tab', 'nav-tab-active' ) : array( 'nav-tab' );
							printf('<a href="%1$s" class="%2$s">%3$s</a>', esc_url($tab_data['url']), implode(' ', array_map('sanitize_html_class', $class)), esc_html($tab_data['title']));
						} 
						?>
				</h2>
			</div>
				<?php 
			}
		}
	}

	public function get_tab_screen_ids() {
		$tabs_screens = array(
			'cashback_rule',
			'edit-cashback_rule',
			'woocommerce_page_customer-wallet', // replace woocommerce with menu slug if your are using any other menu
			'woocommerce_page_wallet-transactions', // replace woocommerce with menu slug if your are using any other menu
			'woocommerce_page_wallet-settings', // replace woocommerce with menu slug if your are using any other menu
			'woocommerce_page_wallet-recharge', // replace woocommerce with menu slug if your are using any other menu


		);
		return $tabs_screens;
	}

	public function get_current_tab() {
		$screen = get_current_screen();
		switch ($screen->id) {
			case 'cashback_rule':
			case 'edit-cashback_rule':
				return 'cashback_rule';
	   
			case 'woocommerce_page_customer-wallet': // replace woocommerce with menu slug if your are using any other menu
				return 'customer-wallet';
			case 'woocommerce_page_wallet-transactions': // replace woocommerce with menu slug if your are using any other menu
				return 'wallet-transactions';
			case 'woocommerce_page_wallet-settings': // replace woocommerce with menu slug if your are using any other menu
				return 'wallet-settings';
			case 'woocommerce_page_wallet-recharge': // replace woocommerce with menu slug if your are using any other menu
				return 'wallet-recharge';
		}
	}





	public function addf_custom_wallet_menu() {
		$sub_menus = array(
			'wallet_customer_wallet' => 'customer-wallet',
			'wallet_transactions' => 'wallet-transactions',
			'wallet_settings' => 'wallet-settings',
			'wallet_recharge' => 'wallet-recharge',
		);
		foreach ($sub_menus as $key => $value) {
			add_submenu_page(
				'woocommerce',
				'Wallet System',
				'Wallet System',
				'manage_options',
				$value,
				array( $this, $key ),
				4
			);
	
		}
		global $pagenow, $typenow;

		if (( 'edit.php' === $pagenow && 'cashback_rule' === $typenow )
		|| ( 'post.php' === $pagenow && isset($_GET['post']) && 'cashback_rule' === get_post_type( sanitize_text_field( $_GET['post'] ) ) )
	) {
		
			remove_submenu_page('woocommerce', 'customer-wallet');
			remove_submenu_page('woocommerce', 'wallet-transactions');
			remove_submenu_page('woocommerce', 'wallet-settings');
			remove_submenu_page('woocommerce', 'wallet-recharge');

		
		} elseif ( ( 'admin.php' === $pagenow && isset($_GET['page']) && 'wallet-transactions' ===  sanitize_text_field( $_GET['page'] ) ) ) {
			remove_submenu_page('woocommerce', 'edit.php?post_type=cashback_rule');
			remove_submenu_page('woocommerce', 'customer-wallet');
			remove_submenu_page('woocommerce', 'wallet-cashback-rules');
			remove_submenu_page('woocommerce', 'wallet-settings');
			remove_submenu_page('woocommerce', 'wallet-recharge');
		
		} elseif ( ( 'admin.php' === $pagenow && isset($_GET['page']) && 'customer-wallet' ===  sanitize_text_field( $_GET['page'] ) ) ) {
			remove_submenu_page('woocommerce', 'edit.php?post_type=cashback_rule');
			remove_submenu_page('woocommerce', 'wallet-transactions');
			remove_submenu_page('woocommerce', 'wallet-cashback-rules');
			remove_submenu_page('woocommerce', 'wallet-settings');
			remove_submenu_page('woocommerce', 'wallet-recharge');
		
		} elseif ( ( 'admin.php' === $pagenow && isset($_GET['page']) && 'wallet-recharge' ===  sanitize_text_field( $_GET['page'] ) ) ) {
			remove_submenu_page('woocommerce', 'edit.php?post_type=cashback_rule');
			remove_submenu_page('woocommerce', 'customer-wallet');
			remove_submenu_page('woocommerce', 'wallet-transactions');
			remove_submenu_page('woocommerce', 'wallet-cashback-rules');
			remove_submenu_page('woocommerce', 'wallet-settings');
		
		} elseif ( ( 'admin.php' === $pagenow && isset($_GET['page']) && 'wallet-settings' ===  sanitize_text_field( $_GET['page'] ) ) ) {
			remove_submenu_page('woocommerce', 'edit.php?post_type=cashback_rule');
			remove_submenu_page('woocommerce', 'customer-wallet');
			remove_submenu_page('woocommerce', 'wallet-transactions');
			remove_submenu_page('woocommerce', 'wallet-cashback-rules');
			remove_submenu_page('woocommerce', 'wallet-recharge');
		
		} else {
			remove_submenu_page('woocommerce', 'edit.php?post_type=cashback_rule');
			remove_submenu_page('woocommerce', 'wallet-transactions');
			remove_submenu_page('woocommerce', 'wallet-cashback-rules');
			remove_submenu_page('woocommerce', 'wallet-settings');
			remove_submenu_page('woocommerce', 'wallet-recharge');
			remove_submenu_page('woocommerce', 'email-settings');
		}
	}

	public function wallet_customer_wallet() {

		// Check if the 'action_update' parameter is set in the URL and sanitize it
		if (isset($_GET['action_update'])) {
			$action_customer_id = sanitize_text_field($_GET['action_update']);
			include_once ADDF_WM_DIR . 'includes/admin/views/addify-customer-wallet-manual-add.php';
		} else {
			// If the 'action_update' parameter is not set
			include_once ADDF_WM_DIR . 'includes/admin/views/addify-customer-wallet-page.php';
		}
	}

	public function wallet_transactions() {
	   
			// Check if the 'transaction_id' parameter is set in the URL
		if (isset($_GET['transaction_id'])) {
			// Get the transaction ID from the URL
			$transaction_id = sanitize_text_field($_GET['transaction_id']);
			include_once ADDF_WM_DIR . 'includes/admin/views/addify-wallet-transactions-details-page.php';
				
		} else {
			// If the 'transaction_id' parameter is not set
			include_once ADDF_WM_DIR . 'includes/admin/views/addify-wallet-transactions-page.php';
		}
	}

	public function wallet_cashback_rules_page() {
		include_once ADDF_WM_DIR . 'includes/admin/views/addify-wallet-cashback-rules-page.php';
	}

	public function wallet_settings() {
		include_once ADDF_WM_DIR . 'includes/admin/views/addify-wallet-settings-page.php';
	}
	public function wallet_recharge() {
		include_once ADDF_WM_DIR . 'includes/admin/views/addify-wallet-recharge-page.php';
	}
	

	// Add meta boxes for Cashback Rule custom fields
	public function addf_cashback_rule_meta_boxes() {
		add_meta_box('cashback_rule_details', 'Cashback Rule Details', array( $this, 'render_cashback_rule_meta_box' ), 'cashback_rule', 'normal', 'high');
	}
	public function addf_reorder_custom_columns_in_cashback_rules_table( $columns ) {
		// Reorder the columns as desired
		unset($columns['date']);
		$columns['cashback_for'] = esc_html__('Cashback for', 'woo_addf_wm');
		$columns['cashback_type'] = esc_html__('Cashback type', 'woo_addf_wm');
		$columns['min_amount'] = esc_html__('Minimum amount percentage', 'woo_addf_wm');
		$columns['max_amount'] = esc_html__('Maximum amount percentage', 'woo_addf_wm');
		$columns['cashback_amount'] = esc_html__('Cashback amount', 'woo_addf_wm');
		$columns['cart_total_from'] = esc_html__('Minimum amount', 'woo_addf_wm');
		$columns['cart_total_to'] = esc_html__('Maximum amount', 'woo_addf_wm');
		$columns['rule_categories'] = esc_html__('Categories', 'woo_addf_wm'); 
		$columns['products_message'] = esc_html__('Products message', 'woo_addf_wm');
		$columns['cart_message'] = esc_html__('Cart message', 'woo_addf_wm');
		$columns['user_product_select'] = esc_html__('Products', 'woo_addf_wm'); 
		$columns['user_roles_select'] = esc_html__('User role', 'woo_addf_wm');
		$columns['rule_start_date'] = esc_html__('Start date', 'woo_addf_wm');
		$columns['rule_end_date'] = esc_html__('Expiry date', 'woo_addf_wm');
		$columns['date'] = esc_html__('Date', 'woo_addf_wm');
	
		return $columns;
	}
	 
	public function addf_display_custom_data_in_cashback_rules_table( $column, $post_id ) {
		
		switch ($column) {
			case 'cashback_for':
				echo esc_attr( get_post_meta($post_id, 'cashback_for', true) );
				break;
			case 'cashback_type':
				echo esc_attr( get_post_meta($post_id, 'cashback_type', true) );
				break;
			case 'min_amount':
				echo esc_attr( get_post_meta($post_id, 'min_amount', true) );
				break;
			case 'max_amount':
				echo esc_attr( get_post_meta($post_id, 'max_amount', true) );
				break;
			case 'user_roles_select':
				$user_roles = get_post_meta($post_id, 'user_roles_select', true);
				if (is_array($user_roles)) {
					echo esc_attr( implode(', ', $user_roles) );
				} else {
					echo esc_attr( $user_roles );
				}
				break;
			case 'rule_categories':
				$selected_categories = (array) get_post_meta($post_id, 'selected_categories', true);
				if (!empty($selected_categories)) {
					foreach ($selected_categories as $category_id) {
						if (!empty($category_id)) {
							echo esc_attr(get_the_category_by_ID($category_id)) . ' , ';
						}
					}
				}
				break;
				
			case 'user_product_select':
					$user_product_select = (array) get_post_meta($post_id, 'user_product_select', true);
				if (!empty($user_product_select)) {
					foreach ($user_product_select as $pro) {
						if ( ! empty( $pro ) ) {
							echo esc_attr(  get_the_title( $pro ) . ' , '  );
						} 
					}
				}
				break;
					
			case 'cashback_amount':
				echo esc_attr( get_post_meta($post_id, 'cashback_amount', true) );
				break;
			case 'products_message':
				echo esc_attr( get_post_meta($post_id, 'products_message', true) );
				break;
			case 'cart_message':
				echo esc_attr( get_post_meta($post_id, 'cart_message', true) );
				break;
			case 'cart_total_from':
				echo esc_attr( get_post_meta($post_id, 'cart_total_from', true) );
				break;
			case 'cart_total_to':
				echo esc_attr( get_post_meta($post_id, 'cart_total_to', true) );
				break;
			case 'rule_start_date':
				$start_date = get_post_meta($post_id, 'rule_start_date', true);
				// $date_start = new DateTime($start_date);
				echo esc_attr($start_date);
				break;
			case 'rule_end_date':
				$end_date = get_post_meta($post_id, 'rule_end_date', true);
				// $date_end = new DateTime($end_date);
				echo esc_attr($end_date);
				break;
			default:
				break;
		}
	}
	

	public function render_cashback_rule_meta_box( $post ) {
		global $addify_wallet_management_nonce;
		$nonce = $addify_wallet_management_nonce;

		wp_nonce_field('cashback_rule_details_nonce', 'cashback_rule_details_nonce');
	
		$cashback_for = get_post_meta($post->ID, 'cashback_for', true);
		$cashback_type = get_post_meta($post->ID, 'cashback_type', true);
		$cashback_amount = get_post_meta($post->ID, 'cashback_amount', true);
		$min_amount = get_post_meta($post->ID, 'min_amount', true);
		$max_amount = get_post_meta($post->ID, 'max_amount', true);
		$cart_total_from = get_post_meta($post->ID, 'cart_total_from', true);
		$cart_total_to = get_post_meta($post->ID, 'cart_total_to', true);

		$roles = get_editable_roles();
		$selected_roles = get_post_meta($post->ID, 'user_roles_select', true);
		
		$categories = get_terms(array(
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		));
		$selected_categories = get_post_meta($post->ID, 'selected_categories', true);
		
		$user_product_select = get_post_meta($post->ID, 'user_product_select', true);
		$products_message = get_post_meta($post->ID, 'products_message', true);
		$cart_message = get_post_meta($post->ID, 'cart_message', true);
		$rule_start_date = get_post_meta($post->ID, 'rule_start_date', true);
		$rule_end_date = get_post_meta($post->ID, 'rule_end_date', true);
	
		?>
	<table class="form-table">
	<tbody>
	<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="cashback_for"><?php echo esc_html__('Cashback for', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<select name="cashback_for" id="cashback_for" class="cashback_for" required>
					<option value="products" <?php selected($cashback_for, 'products'); ?>><?php echo esc_html__('Purchase product', 'woo_addf_wm'); ?></option>
					<option value="recharge" <?php selected($cashback_for, 'recharge'); ?>><?php echo esc_html__('Recharge wallet', 'woo_addf_wm'); ?></option>
					<option value="cart" <?php selected($cashback_for, 'cart'); ?>><?php echo esc_html__('Cart subtotal', 'woo_addf_wm'); ?></option>
					<option value="last order" <?php selected($cashback_for, 'last order'); ?>><?php echo esc_html__('Last purchase subtotal', 'woo_addf_wm'); ?></option>
					<option value="purchase history" <?php selected($cashback_for, 'purchase history'); ?>><?php echo esc_html__('Purchase history', 'woo_addf_wm'); ?></option>
				</select>
				<p class="description"><?php echo esc_html__('Select the condition for cashback.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="cashback_type"><?php echo esc_html__('Cashback type', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<select name="cashback_type" id="cashback_type" class="cashback_type" required>
					<option value="fixed" <?php selected($cashback_type, 'fixed'); ?>><?php echo esc_html__('Fixed', 'woo_addf_wm'); ?></option>
					<option id = "hide_percentage" value="percentage" <?php selected($cashback_type, 'percentage'); ?>><?php echo esc_html__('Percentage', 'woo_addf_wm'); ?></option>
				</select>
				<p class="description"><?php echo esc_html__('You can set cashback type either fixed or percentage based. Cashback will be calculated based on this selection.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="cashback_amount"><?php echo esc_html__('Cashback amount', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<input type="number" name="cashback_amount" id="cashback_amount" class="cashback_amount" min="0" step="0.1" value="<?php echo esc_attr($cashback_amount); ?>" required>
				<p class="description"><?php echo esc_html__('Enter the cashback amount.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		<tr valign="top" class="min_max_both">
			<th scope="row" class="titledesc">
				<label for="min_amount"><?php echo esc_html__('Minimum cashback amount', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<input type="number" name="min_amount" id="min_amount" class="min_amount" min="0" step="0.1" value="<?php echo esc_attr($min_amount); ?>">
				<p class="description"><?php echo esc_html__('Enter the minimum  cashback amount.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		<tr valign="top" class="min_max_both">
			<th scope="row" class="titledesc">
				<label for="max_amount"><?php echo esc_html__('Maximum cashback amount', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<input type="number" name="max_amount" id="max_amount" class="max_amount" min="0" step="0.1" value="<?php echo esc_attr($max_amount); ?>">
				<p class="description"><?php echo esc_html__('Enter the maximum cashback amount.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>
		
		
		<tr valign="top" class = "min_max">
			<th scope="row" class="titledesc">
				<label for="cart_total_from" id="cart_total_from_label"><?php echo esc_html__('Minimum amount', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<input type="number" name="cart_total_from" id="cart_total_from" class="cart_total_from" min="0" step="0.1" value="<?php echo esc_attr($cart_total_from); ?>">
				<p class="description cart_total_from_des"><?php echo esc_html__('Enter the minimum amount of cashback.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>
		<tr valign="top" class = "min_max">
			<th scope="row" class="titledesc">
				<label for="cart_total_to" id="cart_total_to_label"><?php echo esc_html__('Maximum amount', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<input type="number" name="cart_total_to" id="cart_total_to" class="cart_total_to" min="0" step="0.1" value="<?php echo esc_attr($cart_total_to); ?>">
				<input type="hidden" name="addf_wm_nonce" value="<?php echo esc_attr($nonce); ?>" />
				<p class="description cart_total_to_des"><?php echo esc_html__('Enter the maximum amount of cashback.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		<tr valign="top" class = "products_mess">
			<th scope="row" class="titledesc">
				<label for="products_message"><?php echo esc_html__('Casback message products', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<textarea name="products_message" id="products_message" class="products_message" required><?php echo esc_attr($products_message); ?></textarea>
				<p class="description"><?php echo esc_html__('Cashback message on product page. like if cashback type is fixed and cashback amount = 10 then (Get $10 cashback on purchase this product) if cashback type is prcentage and cashback amount = 10 then (Get 10% cashback on purchase this product).', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		<tr valign="top" class = "cart_mess">
			<th scope="row" class="titledesc">
				<label for="cart_message"><?php echo esc_html__('Casback message cart', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<textarea name="cart_message" id="cart_message" class="cart_message" ><?php echo esc_attr($cart_message); ?></textarea>
				<p class="description"><?php echo esc_html__('Cashback message on cart page. like if cashback type is fixed and cashback amount = 10 then (You are eligible to get $20 cashback on the basic of cart subtotal amount) if cashback type is prcentage and cashback amount = 10 then (You are eligible to get 20% cashback on the basic of cart subtotal amount).', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		<tr valign="top" class = "product_category">
			<th scope="row" class="titledesc">
				<label for="selected_categories"><?php echo esc_html__('Categories', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<select name="selected_categories[]" id="selected_categories" data-placeholder="<?php echo esc_html__(' Select categories', 'wm_addf_wm'); ?>" multiple="multiple">
					
				 <?php foreach ($categories as $parent_product_cat) : ?>
					<option value="<?php echo esc_attr($parent_product_cat->term_id); ?>" 
						<?php
						if (!empty($selected_categories) && in_array($parent_product_cat->term_id, $selected_categories)) {
								  echo 'selected';
						}
						?>
						>
					<?php echo esc_html__($parent_product_cat->name, 'woo_addf_ppp'); ?>
					</option>
				<?php endforeach ?>
				

				</select>
				<p class="description"><?php echo esc_html__('Select categories for cashback.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		<tr valign="top" class = "product_category">
			<th scope="row" class="titledesc">
				<label for="user_product_select"><?php echo esc_html__('Products', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<select name="user_product_select[]" class="wc-product-search js_multipage_select_product chosen-select" id="user_product_selects" data-placeholder="<?php echo esc_html__(' Select products', 'wm_addf_wm'); ?>" multiple="multiple" >
				<?php
					
				if (!empty($user_product_select)) {
					foreach ($user_product_select as $pro) {
					   $prod_post = get_post($pro);

						?>
						<option value="<?php echo intval($pro); ?>" selected="selected"><?php echo esc_html__($prod_post->post_title, 'woo_addf_ppp'); ?></option>
						<?php
					}
				}
				?>
				</select>
				<p class="description"><?php echo esc_html__('Select produts for cashback.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="user_roles"><?php echo esc_html__('User role', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<select name="user_roles_select[]" id="user_roles" data-placeholder="<?php echo esc_html__(' Select user roles', 'wm_addf_wm'); ?>" multiple="multiple">
					<?php
					foreach ($roles as $role_name => $role_info) {
						if ('' === $selected_roles ) {
							echo '<option value="' . esc_attr__($role_name) . '">' . esc_attr__($role_info['name']) . '</option>';
						} else {
							$selected = in_array($role_name, $selected_roles) ? 'selected' : '';
							echo '<option value="' . esc_attr__($role_name) . '" ' . esc_attr__($selected) . '>' . esc_attr__($role_info['name']) . '</option>';
						}
					}
					?>
				</select>
				<p class="description"><?php echo esc_html__('Select the user role for cashback.', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="rule_start_date"><?php echo esc_html__('Start date', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<input type="date" name="rule_start_date" id="rule_start_date" class="rule_start_date" value="<?php echo esc_attr($rule_start_date); ?>">
				<p class="description"><?php echo esc_html__('Select the start date for cashback', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="rule_end_date"><?php echo esc_html__('Expiry date', 'woo_addf_wm'); ?></label>
			</th>
			<td class="">
				<input type="date" name="rule_end_date" id="rule_end_date" class="rule_end_date" value="<?php echo esc_attr($rule_end_date); ?>">
				<p class="description"><?php echo esc_html__('Select the expiry date for cashback', 'woo_addf_wm'); ?></p>
			</td>
		</tr>

	</tbody>
</table>


		<?php
	}
	
	// Save meta box data
	public function addf_save_cashback_rule_meta_data( $post_id ) {
		

		// For ajax Security check
		$nonce = isset($_POST['addf_wm_nonce']) ? sanitize_text_field($_POST['addf_wm_nonce']) : '';

		//For custom post type:
		$exclude_statuses = array(
			'auto-draft',
			'trash',
		);

		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

		if ( !in_array( get_post_status($post_id), $exclude_statuses ) && !is_ajax() && 'untrash' != $action ) {

			if (! wp_verify_nonce($nonce, 'addf_wm_nonce') ) {
			wp_die(esc_html__('Nonce verification failed!', 'woo_addf_wm'));
			}

		   // save posts previous total amount
			$cashback_for = isset($_POST['cashback_for']) ? sanitize_text_field( wp_unslash($_POST['cashback_for']) ) : '';
			update_post_meta($post_id, 'cashback_for', $cashback_for);

			$cashback_type = isset($_POST['cashback_type']) ? sanitize_text_field( wp_unslash($_POST['cashback_type']) ) : '';
			update_post_meta($post_id, 'cashback_type', $cashback_type);

			$min_amount = isset($_POST['min_amount']) ? sanitize_text_field( wp_unslash($_POST['min_amount']) ) : '';
			update_post_meta($post_id, 'min_amount', $min_amount);

			$max_amount = isset($_POST['max_amount']) ? sanitize_text_field( wp_unslash($_POST['max_amount']) ) : '';
			update_post_meta($post_id, 'max_amount', $max_amount);

			$user_roles_select = isset($_POST['user_roles_select']) ? sanitize_meta('', wp_unslash($_POST['user_roles_select']), '') : array();
		   update_post_meta($post_id, 'user_roles_select', $user_roles_select);

		   $user_product_select = isset($_POST['user_product_select']) ? sanitize_meta('', wp_unslash($_POST['user_product_select']), '') : array();
		   update_post_meta($post_id, 'user_product_select', $user_product_select);

		   $selected_categories = isset($_POST['selected_categories']) ? sanitize_meta('', wp_unslash($_POST['selected_categories']), '') : array();
		   update_post_meta($post_id, 'selected_categories', $selected_categories);



			$cashback_amount = isset($_POST['cashback_amount']) ? sanitize_text_field( wp_unslash($_POST['cashback_amount']) ) : '';
			update_post_meta($post_id, 'cashback_amount', $cashback_amount);

			$cart_total_from = isset($_POST['cart_total_from']) ? sanitize_text_field( wp_unslash($_POST['cart_total_from']) ) : '';
			update_post_meta($post_id, 'cart_total_from', $cart_total_from);

			$cart_total_to = isset($_POST['cart_total_to']) ? sanitize_text_field( wp_unslash($_POST['cart_total_to']) ) : '';
			update_post_meta($post_id, 'cart_total_to', $cart_total_to);

			$rule_start_date = isset($_POST['rule_start_date']) ? sanitize_text_field( wp_unslash($_POST['rule_start_date']) ) : '';
			update_post_meta($post_id, 'rule_start_date', $rule_start_date);

			$rule_end_date = isset($_POST['rule_end_date']) ? sanitize_text_field( wp_unslash($_POST['rule_end_date']) ) : '';
			update_post_meta($post_id, 'rule_end_date', $rule_end_date);

			$products_message = isset($_POST['products_message']) ? sanitize_text_field( wp_unslash($_POST['products_message']) ) : '';
			update_post_meta($post_id, 'products_message', $products_message);

			$cart_message = isset($_POST['cart_message']) ? sanitize_text_field( wp_unslash($_POST['cart_message']) ) : '';
			update_post_meta($post_id, 'cart_message', $cart_message);


			//Redirect to the post listing page for the 'cashback_rule' post type
				$redirect_url = admin_url('edit.php?post_type=cashback_rule');
				wp_redirect($redirect_url);
				exit;

		}
	}
}
new Addify_Wallet_Management_Menu_Rule_Metaboxes();

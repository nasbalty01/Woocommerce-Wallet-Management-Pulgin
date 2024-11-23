<?php

function af_get_created_post( $new_aurgs = array(), $check_rule_type = 'product', $product = '', $user_role = '', $af_current_user_id = '' ) {
	

	$args = array(
		'post_type'         => 'any', // Adjust post type if needed
		'post_status'       => 'publish',
		'posts_per_page'    => -1, // Retrieve all posts
		'orderby'           => 'menu_order',
		'order'             => 'ASC', // Order by ascending
		'fields'            => 'ids', 
	);

	if ( count( $new_aurgs ) >= 1 ) {
		$args = array_merge( $args, (array) $new_aurgs );
	} 
	if ( empty($af_current_user_id) ) {

		$current_user_id = get_current_user_id();
		$current_user = wp_get_current_user();
		
	} else {
		$current_user_id = $af_current_user_id;
		$current_user = get_user_by( 'ID', $af_current_user_id );
	}
	

	$current_user_role = !empty($user_role) ? $user_role :  current($current_user->roles);
	$today_date = gmdate('Y-m-d');

	$all_rules  = get_posts( $args);
	foreach ( $all_rules  as $rule_id ) {
		
		$selected_roles = (array) get_post_meta($rule_id, 'user_roles_select', true);
		$rule_start_date = !empty(get_post_meta($rule_id, 'rule_start_date', true)) ? get_post_meta($rule_id, 'rule_start_date', true) : $today_date;
		$rule_end_date = !empty(get_post_meta($rule_id, 'rule_end_date', true)) ? get_post_meta($rule_id, 'rule_end_date', true) : $today_date;
			
		if ( ( strtotime( $today_date ) < strtotime($rule_start_date ) ) || strtotime( $today_date ) > ( strtotime($rule_end_date ) ) || '–' === $rule_end_date ) {
			unset( $all_rules[ array_search($rule_id, $all_rules) ] );
		}
		if (  count( $selected_roles ) >= 1 && ! in_array( $current_user_role , $selected_roles ) ) {

			unset( $all_rules[ $rule_id ] );
		}
	}
	// echo $check_rule_type;
	if ( !empty($product) &&  'product' == $check_rule_type ) {
		
		$variation_id = 0;
		if ( ! is_object( $product ) ) {

			$product = wc_get_product( $product );
		}
		$product_price = $product->get_price();
		// echo '<br> product_price ==> ' . $product_price;

		if ( 'variation' == $product->get_type() ) {
			$variation_id = $product->get_id();

			$product = wc_get_product( wp_get_post_parent_id($product->get_id()) );
		}

		$product_id = $product->get_id();
		$cashback_amount_final =0;
  
	 
		foreach ( $all_rules  as $rule_id ) {
			$is_match_product = false;
			$user_product_select = (array) get_post_meta($rule_id, 'user_product_select', true);
			$selected_categories = (array) get_post_meta($rule_id, 'selected_categories', true);
			$cashback_amount = get_post_meta($rule_id, 'cashback_amount', true);
			$cashback_type = get_post_meta($rule_id, 'cashback_type', true);
			$min_amount = get_post_meta($rule_id, 'min_amount', true);
			$max_amount = get_post_meta($rule_id, 'max_amount', true);
			

			if ('products' === get_post_meta($rule_id, 'cashback_for', true)) {

				// echo 'sss===';

				if ( !empty( $user_product_select ) && ( in_array( $product_id , $user_product_select ) ) ) {
					// echo '<br> user_product_select ==> ' ;
					$is_match_product = true;
				}

				if ( !empty( $variation_id ) && !empty( $user_product_select ) &&  in_array( $variation_id , $user_product_select ) ) {
					// echo '<br> variation_id ==> ' ;
					$is_match_product = true;
				}

				if ( !empty( $selected_categories ) && has_term( $selected_categories, 'product_cat', $product_id) ) {
					// echo '<br> selected_categories ==> ' ;
					$is_match_product = true;
				}

				if ( empty( $user_product_select ) && empty( $selected_categories ) ) {
					// echo '<br> empty ==>  all ' ;
					$is_match_product = true;
				}
			


				if ( ! $is_match_product ) {
					unset( $all_rules[ array_search($rule_id, $all_rules) ] );
					continue;
				}

				// echo ' == cashback_amount ==> ' . $cashback_amount;
					// var_dump( $is_match_product );   
				if ( 'fixed' != $cashback_type) {
			
					$cashback_amount_percentage = ( $cashback_amount / 100 ) * $product_price ;

					if ($cashback_amount_percentage < $min_amount ||  $cashback_amount_percentage > $max_amount) {
						unset( $all_rules[ array_search($rule_id, $all_rules) ] );
						continue;
					}
	
					$cashback_amount_final = $cashback_amount_percentage;
				} else {
					$cashback_amount_final = $cashback_amount;

				}

					// echo '  cashback_amount_final ==> ' . $cashback_amount_final;

			} else {
				unset( $all_rules[ array_search($rule_id, $all_rules) ] );
			}

			

		}
		// echo '<br> cashback_amount_final ==> ' . $cashback_amount_final;

	}
	
	if ( 'cart' == $check_rule_type ) {

		 
		// $cart_total = $product && ! is_object($product) ? $product : wc()->cart->get_totals()['total'];
		$cart_total = $product && ! is_object($product) ? $product : wc()->cart->get_subtotal();
		

	  

		foreach ( $all_rules  as $rule_id ) {
			
			$cart_total_from    = get_post_meta( $rule_id, 'cart_total_from', true ) ? get_post_meta( $rule_id, 'cart_total_from', true ) : $cart_total;
			$cart_total_to      = get_post_meta( $rule_id, 'cart_total_to', true ) ? get_post_meta( $rule_id, 'cart_total_to', true ) : $cart_total;
			$cashback_amount = get_post_meta($rule_id, 'cashback_amount', true);
			$cashback_type = get_post_meta($rule_id, 'cashback_type', true);
			$min_amount = get_post_meta($rule_id, 'min_amount', true);
			$max_amount = get_post_meta($rule_id, 'max_amount', true);
			
			if ('cart' === get_post_meta($rule_id, 'cashback_for', true)) {

				if ( $cart_total < $cart_total_from  || $cart_total >  $cart_total_to) {
					unset( $all_rules[ array_search($rule_id, $all_rules) ] );
				}

			} else {
				unset( $all_rules[ array_search($rule_id, $all_rules) ] );
			}

			if ( 'fixed' === $cashback_type) {
				$cashback_amount_final = $cashback_amount;
			} else {
				$cashback_amount_percentage = ( $cashback_amount / 100 ) * $cart_total ;
				if ($cashback_amount_percentage >= $min_amount && $cashback_amount_percentage <= $max_amount) {
					$cashback_amount_final = $cashback_amount_percentage;
				} else {
					unset( $all_rules[ array_search($rule_id, $all_rules) ] );
				}
			}

		}

	}


	
	if ( 'last order' == $check_rule_type ) {

		
		$all_order_of_current_user = wc_get_orders(array(
			'status' => 'completed',
			'customer' => $current_user_id,
			'order' =>'DESC',
			'post_per_page'=> 1,
		));
		
		foreach ( $all_order_of_current_user  as $current_order ) {
			foreach ( $all_rules  as $rule_id ) {
				
				$cart_total_from    = get_post_meta( $rule_id, 'cart_total_from', true ) ? get_post_meta( $rule_id, 'cart_total_from', true ) : $current_order->get_total();
				$cart_total_to      = get_post_meta( $rule_id, 'cart_total_to', true ) ? get_post_meta( $rule_id, 'cart_total_to', true ) : $current_order->get_total();
				$cashback_amount = get_post_meta($rule_id, 'cashback_amount', true);
				$cashback_type = get_post_meta($rule_id, 'cashback_type', true);
				$min_amount = get_post_meta($rule_id, 'min_amount', true);
				$max_amount = get_post_meta($rule_id, 'max_amount', true);
				
				if ('last order' === get_post_meta($rule_id, 'cashback_for', true)) {

					if ( $current_order->get_total() < $cart_total_from  || $current_order->get_total() >  $cart_total_to) {
						unset( $all_rules[ array_search($rule_id, $all_rules) ] );
					}

				} else {
					unset( $all_rules[ array_search($rule_id, $all_rules) ] );
				}

				if ( 'fixed' === $cashback_type) {
					$cashback_amount_final = $cashback_amount;
				} else {
					$cashback_amount_percentage = ( $cashback_amount / 100 ) * $current_order->get_total();
					if ($cashback_amount_percentage >= $min_amount && $cashback_amount_percentage <= $max_amount) {
						$cashback_amount_final = $cashback_amount_percentage;
					} else {
						unset( $all_rules[ array_search($rule_id, $all_rules) ] );
					}
				}
			}

		}

	}
	if ( 'purchase history' == $check_rule_type ) {

		$all_order_of_current_user = wc_get_orders(array(
			'status' => 'completed',
			'customer' => $current_user_id,
			'order' =>'DESC',
			'post_per_page'=> -1,
		));
		$total_spend = 0;
		foreach ( $all_order_of_current_user  as $current_order ) {
			$total_spend += (float) $current_order->get_total();
		}
		foreach ( $all_rules  as $rule_id ) {
			
			$cart_total_from    = get_post_meta( $rule_id, 'cart_total_from', true ) ? get_post_meta( $rule_id, 'cart_total_from', true ) : $total_spend;
			$cart_total_to      = get_post_meta( $rule_id, 'cart_total_to', true ) ? get_post_meta( $rule_id, 'cart_total_to', true ) : $total_spend;
			$cashback_amount = get_post_meta($rule_id, 'cashback_amount', true);
			$cashback_type = get_post_meta($rule_id, 'cashback_type', true);
			$min_amount = get_post_meta($rule_id, 'min_amount', true);
			$max_amount = get_post_meta($rule_id, 'max_amount', true);
			
			if ('purchase history' === get_post_meta($rule_id, 'cashback_for', true)) {

				if ( $total_spend < $cart_total_from  || $total_spend >  $cart_total_to) {
					unset( $all_rules[ array_search($rule_id, $all_rules) ] );
				}

			} else {
				unset( $all_rules[ array_search($rule_id, $all_rules) ] );
			}

			if ( 'fixed' === $cashback_type) {
				$cashback_amount_final = $cashback_amount;
			} else {
				$cashback_amount_percentage = ( $cashback_amount / 100 ) * $total_spend;
				if ($cashback_amount_percentage >= $min_amount && $cashback_amount_percentage <= $max_amount) {
					$cashback_amount_final = $cashback_amount_percentage;
				} else {
					unset( $all_rules[ array_search($rule_id, $all_rules) ] );
				}
			}
		}

	}
	if ( 'recharge' == $check_rule_type ) {
		
		// here in parameter $product we will received wallet recharge amount.

		$cart_total = $product && ! is_object($product) ? $product : wc()->cart->get_subtotal();

		foreach ( $all_rules  as $rule_id ) {
			
			$cart_total_from    = get_post_meta( $rule_id, 'cart_total_from', true ) ? get_post_meta( $rule_id, 'cart_total_from', true ) : $cart_total;
			$cart_total_to      = get_post_meta( $rule_id, 'cart_total_to', true ) ? get_post_meta( $rule_id, 'cart_total_to', true ) : $cart_total;
			$cashback_amount = get_post_meta($rule_id, 'cashback_amount', true);
			$cashback_type = get_post_meta($rule_id, 'cashback_type', true);
			$min_amount = get_post_meta($rule_id, 'min_amount', true);
			$max_amount = get_post_meta($rule_id, 'max_amount', true);
			
			if ('recharge' === get_post_meta($rule_id, 'cashback_for', true)) {

				if ( $cart_total < $cart_total_from  || $cart_total >  $cart_total_to) {
					unset( $all_rules[ array_search($rule_id, $all_rules) ] );
				}

			} else {
				unset( $all_rules[ array_search($rule_id, $all_rules) ] );
			}

			if ( 'fixed' === $cashback_type) {
				$cashback_amount_final = $cashback_amount;
			} else {
				$cashback_amount_percentage = ( $cashback_amount / 100 ) * $cart_total;
				if ($cashback_amount_percentage >= $min_amount && $cashback_amount_percentage <= $max_amount) {
					$cashback_amount_final = $cashback_amount_percentage;
				} else {
					unset( $all_rules[ array_search($rule_id, $all_rules) ] );
				}
			}
		}

	}


	if ( empty($af_current_user_id) ) {
		return $all_rules;
	} else {
		return array(
			'rules' => $all_rules,
			'amount' => $cashback_amount_final,
		);
	}
}

<?php
/*
Plugin Name: Limit quantity for WooCommerce
Description: It allows admins to set a max limit of quantity for WooCommerce products.
Author: Mohit Agarwal
Version: 1.4.2
Stable tag: "trunk"
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Limit quantity for WooCommerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Limit quantity for WooCommerce is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Limit quantity for WooCommerce . If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/




/**
 * @package Limit quantity for WooCommerce me_limit_qty
 * @version 1.4.2
 */


 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



function me_limit_qty_inventory_custom_field(){

	// Create a custom text field for inventory page

	woocommerce_wp_text_input(array(
		'id' => 'limit_woo_max_qty',
		'label' => __('Limit quantity (Max):', 'woocommerce') ,
		'placeholder' => '',
		'desc_tip' => 'true',
		'description' => __('Enter the maximum quantity possible for a user to add to cart.', 'woocommerce') ,
		'type' => 'number',
		'custom_attributes' => array(
			'step' => 'any',
			'min' => '0'
		)
	));
	
}
add_action( 'woocommerce_product_options_inventory_product_data', 'me_limit_qty_inventory_custom_field' );

// Saving the data.
add_action( 'woocommerce_process_product_meta' , 'me_limit_qty_custom_save');

function me_limit_qty_custom_save( $post_id ) {
 
    if ( ! ( isset( $_POST['woocommerce_meta_nonce'], $_POST[ 'limit_woo_max_qty'] ) || wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) ) {
        return false;
    }
 
    $woo_max_qty_limit = absint($_POST[ 'limit_woo_max_qty' ]);
	
	if ( $woo_max_qty_limit > 0 ) {
		update_post_meta($post_id, 'limit_woo_max_qty', $woo_max_qty_limit);
	}else{
		delete_post_meta($post_id, 'limit_woo_max_qty');
	}
    
}

function me_limit_qty_max( $qty, $product ) { // change allowed max value on product page
	if ( ! $product->is_type( 'variable' ) ) { //applicable only for products that are not variable. Such as ;simple;. 
            $new_max  = get_post_meta($product->id, 'limit_woo_max_qty',true);
            $woo_max = $product->get_max_purchase_quantity();
            return ( -1 == $woo_max || $new_max < $woo_max ? $new_max : $woo_max );
    } else {
            return $qty;
    }
}

add_filter( 'woocommerce_quantity_input_max', 'me_limit_qty_max', PHP_INT_MAX, 2 );


/* 

If item already exists in cart in greater quantity, reduce quantity to lower number. 

 */

function me_limit_qty_reduce_to_max() { // make sure cart page too has limited qty.
	
	$max_crossed = false;
	
    foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
		$max_meta = get_post_meta($values['product_id'], 'limit_woo_max_qty',true);
		if(! empty( $max_meta ) ){
			if ( $values['quantity'] > $max_meta) {
				WC()->cart->set_quantity($cart_item_key, $max_meta);
				$max_crossed = true;
			}
		}
	}
    
    
    if ( $max_crossed ) {
        
        // render a notice to explain why qty has been reduced
        wc_add_notice( 'Hi there! Looks like your cart had items that were more than the max quantity allowed. Quantity has been automatically reduced to the maximum.', 'notice' );
    }
}
add_action( 'woocommerce_check_cart_items', 'me_limit_qty_reduce_to_max' );
 
 
 
 
function me_limit_qty_enqueue_script() {   
    wp_enqueue_script( 'limit_qty_max_admin', plugin_dir_url( __FILE__ ) . 'js/limit_qty_max_admin.js', array('jquery'), '1.0' );
}
add_action('admin_enqueue_scripts', 'me_limit_qty_enqueue_script');




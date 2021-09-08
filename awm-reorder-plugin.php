<?php
/*
Plugin Name: AWM ReOrder Plugin
Plugin URI: https://www.aptwebmedia.com/reorder-plugin/
Description: Allow users to reorder their previously completed order.
Version: 0.0.1
Author: Albert Tagaban
Author URI: https://www.aptwebmedia.com
Text Domain: awm-reorder-plugin
*/


/*
 * display the reorder button on users orders page 
*/
add_filter( 'woocommerce_my_account_my_orders_actions', 'awm_add_myaccount_orderactions', 10, 2 );
function awm_add_myaccount_orderactions( $actions, $order ) {
    $order_status = $order->get_status();

    // display the reorder button only if the order was previously completed
    if ($order_status == 'completed') {
        $actions['reorder'] = array(
            // adjust URL as needed
            'url'  => site_url() .'?awmreorder=' . $order->get_order_number(),
            'name' => __( 'Re-Order', 'awm-reorder-plugin' ),
        );
    }

    return $actions;
}

/*
 * Let's register the query variable awmreorder. 
 * We will use the variable to check if the user 
 * is trying to re-order a previous order and that
 * also indicates our Order ID
*/

add_filter( 'query_vars', 'awm_add_query_vars_filter' );

function awm_add_query_vars_filter( $vars ){
    $vars[] = "awmreorder";
    return $vars;
}


/*
 * Let's hook to template_redirect and 
 * check if the awmreorder variable is available on query variables.
 * If awmreorder exists then we add the products from that order by that user.
 */ 

add_action('template_redirect', 'awm_reorder_callback');

function awm_reorder_callback(  ) {
    $order_id  = get_query_var('awmreorder');
    //check that order id is numeric 
    if ( is_numeric($order_id) ) {
        //let's empty the cart before adding anything
        WC()->cart->empty_cart();
        // get the details of the order
        $order = new WC_Order( $order_id );
        $items  = $order -> get_items();
               
        // loop through the products
        foreach ( $items as $item_id => $item ) {
            
            $product_id   = $item -> get_product_id();
            $quantity     = $item -> get_quantity();
            $product      = $item -> get_product();
            $type         = $product -> get_type();
                
            if( $product->is_type('variation') ){
                $variation_id = $item->get_variation_id();
                WC()->cart->add_to_cart( $product_id,  $quantity, $variation_id );
            } else {
                WC()->cart->add_to_cart( $product_id,  $quantity );
            }
        }
         
        $url = wc_get_checkout_url();
        wp_safe_redirect( $url );
        exit();
         
    }
}




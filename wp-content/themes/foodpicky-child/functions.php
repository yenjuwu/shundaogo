<?php
/**
 * Shundao functions and definitions.
 */
/* Dash child theme functions */
function my_theme_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
    wp_enqueue_script('sd-underscore', get_stylesheet_directory_uri().'/js/underscore-min.js');
    wp_enqueue_script('sd-location', get_stylesheet_directory_uri().'/js/shundao-location.js');
    wp_enqueue_script('sd-init', get_stylesheet_directory_uri().'/js/shundao-init.js');

}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
add_action('wp_ajax_nopriv_add_delivery_cost','add_delivery_cost');
function add_delivery_cost(){
   $delivery_cost =filter_input(INPUT_POST, 'delivery_cost',FILTER_VALIDATE_FLOAT);
   $cost = floatval($delivery_cost);
   if($cost!=0.0){
       $cart = WC()->cart;
       $cart->calculate_fees();
       $cart->add_fee(__('Delivery Charge','shundao'), $cost, false);
       error_log(print_r($cart,true));
       // figure out how to add custom cost to wc
       echo json_encode(array("status"=>1,"message"=>"Delivery Charge has been added"));
   }else{
       echo json_encode(array("status"=>0,"message"=>"There is an issue calculating your charge"));
   }
   die();
}

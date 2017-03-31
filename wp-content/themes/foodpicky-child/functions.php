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


add_action('wp_ajax_nopriv_add_delivery_cost','add_delivery_cost',10);
add_action('wp_ajax_add_delivery_cost', 'add_delivery_cost', 10);

function add_delivery_cost(){
        
   $delivery_cost =filter_input(INPUT_POST, 'delivery_cost',FILTER_VALIDATE_FLOAT);
   $cost = floatval($delivery_cost);
   if($cost!=0.0){
        session_start();
        $_SESSION['delivery_cost'] = $cost;
       // figure out how to add custom cost to wc
       echo json_encode(array("status"=>1,"message"=>__("Delivery Charge has been added","shundao")));
   }else{
       echo json_encode(array("status"=>0,"message"=>__("There is an issue calculating your charge","shundao")));
   }
   die();
}
add_action('woocommerce_cart_calculate_fees', 'add_delivery_cost_from_session');

function add_delivery_cost_from_session() {
    session_start();
    $delivery_cost = $_SESSION['delivery_cost'];
    $shipping_label=__("Delivery Cost:","shundao");
    WC()->cart->add_fee($shipping_label, $delivery_cost);
}

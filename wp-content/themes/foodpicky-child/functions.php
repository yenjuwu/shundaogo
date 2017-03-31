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
    wp_enqueue_script('google_distance_api','https://maps.googleapis.com/maps/api/js?key=AIzaSyAIzHH1OX9CG41YFcZq1XHfHGJ1rV_mAGA&callback=myCallback');
    wp_enqueue_script('sd-underscore', get_stylesheet_directory_uri().'/js/underscore-min.js');
    wp_enqueue_script('sd-location', get_stylesheet_directory_uri().'/js/shundao-location.js');
    wp_enqueue_script('sd-init', get_stylesheet_directory_uri().'/js/shundao-init.js');

}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );


<<<<<<< HEAD
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
=======
//add hidden vendor address so we can calculate the delivery cost
// add_filter('woocommerce_form_field_hidden', 'wcds_form_field_hiddenv2', 999, 4);
add_action('woocommerce_review_order_before_cart_contents', 'wcds_form_field_hiddenv2');

function wcds_form_field_hiddenv2() {

      //echo "what is up world";  

   
}

function getDeliveryTime() {
    $result = array();
    $timeFormat = 'h:i a.';

    $date = new DateTime(date('H:i'));

    $timearr = explode(':', $date->format('H:i'));
    $time = $timearr[1];

    
    if($time < 15) {
        $date->sub(new DateInterval('PT' . $time . 'M'));

    }else if($time < 30 && $time >= 15) {
        $date->sub(new DateInterval('PT' . ($time - 15) . 'M'));
    } else if($time >= 30 && $time <35) {
         $date->sub(new DateInterval('PT' . ($time - 30) . 'M'));
    } else {
        $date->sub(new DateInterval('PT' . ($time - 45) . 'M'));
    }


    $date->add(new DateInterval("PT45M"));

    for($i =0; $i < 4; $i++) {
        $interval = $date->format($timeFormat) . " - ";
        $date->add(new DateInterval('PT15M'));
        $interval = $interval . $date->format($timeFormat);
        $result[] = $interval;
    }

    return $result;


}

function wcds_form_field_hidden($no_parameter, $key, $args, $value) {

    $field = '<p class="test-me form-row ' . implode( ' ', $args['class'] ) .'" id="' . $key . '_field">
        <input type="hidden" class="input-hidden" name="' . $key . '" id="' . $key . '" placeholder="' . $args['placeholder'] . '" value="'. $value.'" />
        </p>' . $after;

      echo $field;  

    return $field;
>>>>>>> 6f1803bcb32dcd643ff6ed0fc261b4c2ceb6ad28
}
add_action('woocommerce_cart_calculate_fees', 'add_delivery_cost_from_session');

<<<<<<< HEAD
function add_delivery_cost_from_session() {
    session_start();
    $delivery_cost = $_SESSION['delivery_cost'];
    $shipping_label=__("Delivery Cost:","shundao");
    WC()->cart->add_fee($shipping_label, $delivery_cost);
=======
function getSampleDeliveryCosts() {
    echo 'You can have whatever you like';
}

function add_vc_profile_address($cart_item){    


    //<?php $('#deliveryCost').html("Some cost")
    //var_dump($cart_item);
>>>>>>> 6f1803bcb32dcd643ff6ed0fc261b4c2ceb6ad28
}

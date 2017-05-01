<?php
/**
 * Shundao functions and definitions.
 */
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
    wp_enqueue_script('sd-google-autocomplete', get_stylesheet_directory_uri().'/js/google-autocomplete.js');
    wp_enqueue_script('sd-init', get_stylesheet_directory_uri().'/js/shundao-init.js');

}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );


add_action('wp_ajax_nopriv_add_delivery_cost','add_delivery_cost',10);
add_action('wp_ajax_add_delivery_cost', 'add_delivery_cost', 10);

add_action('wp_ajax_nopriv_add_tip_cost','add_tip',10);

add_action('wp_ajax_add_tip_cost','add_tip',10);

function add_tip(){
    $cost = filter_input(INPUT_POST,'tip',FILTER_VALIDATE_FLOAT);
    $tip = floatval($cost);
    if($tip!=0.0){
        session_start();
        $_SESSION['tip_cost']=$tip;
        echo json_encode(array("status"=>1,"message"=>__("Tip has been added","shundao")));
    }else{
        echo json_encode(array("status"=>0,"message"=>__("Failed to add tip","shundao")));
    }
    exit();
}

function add_delivery_cost(){
        
   $delivery_cost =filter_input(INPUT_POST, 'delivery_cost',FILTER_VALIDATE_FLOAT);
   $cost = floatval($delivery_cost);
   if($cost!=0.0){
        session_start();
        $_SESSION['delivery_cost'] = $cost;
       // figure out how to add custom cost to wc
        $message=__('Delivery Charge has been added','shundao');
        error_log($message);
       echo json_encode(array("status"=>1,"message"=>$message));
   }else{
       $message=__("There is an issue calculating your charge","shundao");
       error_log($message);
       echo json_encode(array("status"=>0,"message"=>$message));
   }
   die();
}
add_action('woocommerce_cart_calculate_fees', 'add_fee_cost_from_session');

function add_fee_cost_from_session() {
    session_start();
    $delivery_cost = $_SESSION['delivery_cost'];
    if($delivery_cost!=null){
        $shipping_label=__("Delivery Cost:","shundao");
        WC()->cart->add_fee($shipping_label, $delivery_cost);
    }
    
    $tip=$_SESSION['tip_cost'];
    if($tip!=null){
        $tip_label=__("Tip","shundao");
        WC()->cart->add_fee($tip_label,$tip);
    }
}

    add_filter('woocommerce_add_to_cart_product_id','shundao_add_to_cart');
    function shundao_add_to_cart($pid){
        global $woocommerce;
        $cart = $woocommerce->cart;
        if($cart->is_empty()){
            // if cart is empty we don't need to do any check at all
            return $pid;
        }else{
            $items = $cart->get_cart();
            $_product = array();
            foreach($items as $item => $values) {
                $_product[] = $values['data']->post;
            }
            if(isset($_product[0]->ID)){ //getting first item from cart
                $in_cart_id = $_product[0]->ID;
                $diff_vendor = is_from_different_vendor($in_cart_id, $pid);
                if($diff_vendor){
                    session_start();
                    $_SESSION['sd_error']=__("you cannot order from multiple restaurants", "shundao");
                    return null;
                }
            } 
            return $pid;
        }
        return $pid;
    }
    function is_from_different_vendor($in_cart_pid,$pid){
        $diff_vendor=TRUE;
        $in_cart_product= get_post($in_cart_pid);
        $buying_product= get_post($pid);
        if($in_cart_product->post_type=="product_variation"){
            //meaing the item for the in cart product is an variation item
            $in_cart_pid = $in_cart_product->post_parent;// if it's an variation only care about the parent post 
        }
        if($buying_product->post_type=="product_variation"){
            $pid = $buying_product->post_parent;// if it's a variation only care about the parent post
        }
        $in_cart_vid = get_post_field( 'post_author', $in_cart_pid);
        $buying_vid = get_post_field('post_author',$pid);
        if($in_cart_vid == $buying_vid){
            $diff_vendor=FALSE;
        }
        return $diff_vendor;
    }
    
    function delivery_tip_sanity_check(){
        global $woocommerce;
        $cart = $woocommerce->cart;
        $fees = $cart->get_fees();
        $hasDeliveryFee= FALSE;
        foreach($fees as $cartFee){
            if($cartFee->id=="delivery-cost"){
                $hasDeliveryFee=TRUE;
                break;
            }
        }
        if($hasDeliveryFee==FALSE){
            wc_add_notice( __( "请输入正确的外卖地址格式 Address, City, State Zipcode","shundao" ), 'error' );
        }
    }
    add_action( 'woocommerce_before_checkout_process', 'delivery_tip_sanity_check' );    
    function filter_woocommerce_get_order_item_totals($total_rows,$value='',$display=0){
        if(count($total_rows)>0){
            unset($total_rows['shipping']);
        }
        return $total_rows;

    }
    add_filter( 'woocommerce_get_order_item_totals', 'filter_woocommerce_get_order_item_totals', 10, 3 ); 

     

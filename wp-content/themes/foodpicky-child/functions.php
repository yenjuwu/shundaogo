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

//add google map api to the end

//add hidden vendor address so we can calculate the delivery cost
add_filter('woocommerce_form_field_hidden', 'wcds_form_field_hidden', 999, 4);

function wcds_form_field_hidden($no_parameter, $key, $args, $value) {

    $field = '<p class="test-me form-row ' . implode( ' ', $args['class'] ) .'" id="' . $key . '_field">
        <input type="hidden" class="input-hidden" name="' . $key . '" id="' . $key . '" placeholder="' . $args['placeholder'] . '" value="'. $value.'" />
        </p>' . $after;

    return $field;
}


function add_vc_profile_address($cart_item){
    //var_dump($cart_item);
}
add_action('woocommerce_review_order_after_cart_contents', 'add_vc_profile_address');


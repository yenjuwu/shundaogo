<?php
/**
 * The template for displaying the store settings form
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/
 *
 * @package    WCVendors_Pro
 * @version    1.3.7
 */

$settings_social 		= (array) WC_Vendors::$pv_options->get_option( 'hide_settings_social' );
$social_total 		= count( $settings_social ); 
$social_count = 0; 
foreach ( $settings_social as $value) { if ( 1 == $value ) $social_count +=1;  }

?>

<h3><?php _e( 'Settings', 'wcvendors-pro' ); ?></h3>

<form method="post" action="" class="wcv-form wcv-formvalidator"> 

<?php WCVendors_Pro_Store_Form::form_data(); ?>

<div class="wcv-tabs top" data-prevent-url-change="true">

	<?php WCVendors_Pro_Store_Form::store_form_tabs( ); ?>

	<!-- Store Settings Form -->
	
	<div class="tabs-content" id="store">

		<!-- Store Name -->
		<?php WCVendors_Pro_Store_Form::store_name( $store_name ); ?>

		<?php do_action( 'wcvendors_settings_after_shop_name' ); ?>

		<!-- Store Description -->
		<?php WCVendors_Pro_Store_Form::store_description( $store_description ); ?>	
		
		<?php do_action( 'wcvendors_settings_after_shop_description' ); ?>
		<br />

		<!-- Seller Info -->
		<?php WCVendors_Pro_Store_Form::seller_info( ); ?>	
		
		
		<?php do_action( 'wcvendors_settings_after_seller_info' ); ?>

		<br />

		<!-- Company URL -->
		<?php do_action( 'wcvendors_settings_before_company_url' ); ?>
		<?php WCVendors_Pro_Store_Form::company_url( ); ?>
		<?php do_action(  'wcvendors_settings_after_company_url' ); ?>



		<!-- Store Phone -->
		<?php do_action( 'wcvendors_settings_before_store_phone' ); ?>
		<?php WCVendors_Pro_Store_Form::store_phone( ); ?>
		<?php do_action(  'wcvendors_settings_after_store_phone' ); ?>

		<!-- Store Address -->
		<?php do_action( 'wcvendors_settings_before_address' ); ?>
		<?php WCVendors_Pro_Store_Form::store_address_country( ); ?>
		<?php WCVendors_Pro_Store_Form::store_address1( ); ?>
		<?php WCVendors_Pro_Store_Form::store_address2( ); ?>
		<?php WCVendors_Pro_Store_Form::store_address_city( ); ?>
		<?php WCVendors_Pro_Store_Form::store_address_state( ); ?>
		<?php WCVendors_Pro_Store_Form::store_address_postcode( ); ?>
		<?php do_action(  'wcvendors_settings_after_address' ); ?>

		<!-- Store Vacation Mode -->
		<?php do_action( 'wcvendors_settings_before_vacation_mode' ); ?>
		<?php WCVendors_Pro_Store_Form::vacation_mode( ); ?>
		<?php do_action(  'wcvendors_settings_after_vacation_mode' ); ?>


	</div>

	<div class="tabs-content" id="payment">
		<!-- Paypal address -->
		<?php do_action( 'wcvendors_settings_before_paypal' ); ?>

		<?php WCVendors_Pro_Store_Form::paypal_address( ); ?>

		<?php do_action( 'wcvendors_settings_after_paypal' ); ?>
	</div>

	<div class="tabs-content" id="branding">
		<?php do_action( 'wcvendors_settings_before_branding' ); ?>

		<!-- Store Banner -->
		<?php WCVendors_Pro_Store_Form::store_banner( ); ?>	

		<!-- Store Icon -->
		<?php WCVendors_Pro_Store_Form::store_icon( ); ?>	

		<?php do_action( 'wcvendors_settings_after_branding' ); ?>
	</div>

	<div class="tabs-content" id="shipping">

		<?php do_action( 'wcvendors_settings_before_shipping' ); ?>

		<!-- Shipping Rates -->
		<?php WCVendors_Pro_Store_Form::shipping_rates( ); ?>

		<?php do_action( 'wcvendors_settings_after_shipping' ); ?>

		<!-- Shiping Information  -->

		<?php WCVendors_Pro_Store_Form::product_handling_fee( $shipping_details ); ?>
		<?php WCVendors_Pro_Store_Form::shipping_policy( $shipping_details ); ?>
		<?php WCVendors_Pro_Store_Form::return_policy( $shipping_details ); ?>
		<?php WCVendors_Pro_Store_Form::shipping_from( $shipping_details ); ?>
		<?php WCVendors_Pro_Store_Form::shipping_address( $shipping_details ); ?>
		
	</div>

	<?php if ( $social_count != $social_total ) :  ?> 
		<div class="tabs-content" id="social">
			<?php do_action( 'wcvendors_settings_before_social' ); ?>
			<!-- Twitter -->
			<?php WCVendors_Pro_Store_Form::twitter_username( ); ?>
			<!-- Instagram -->
			<?php WCVendors_Pro_Store_Form::instagram_username( ); ?>
			<!-- Facebook -->
			<?php WCVendors_Pro_Store_Form::facebook_url( ); ?>
			<!-- Linked in -->
			<?php WCVendors_Pro_Store_Form::linkedin_url( ); ?>
			<!-- Youtube URL -->
			<?php WCVendors_Pro_Store_Form::youtube_url( ); ?>
			<!-- Pinterest URL -->
			<?php WCVendors_Pro_Store_Form::pinterest_url( ); ?>
			<!-- Google+ URL -->
			<?php WCVendors_Pro_Store_Form::googleplus_url( ); ?>
			<!-- Snapchat -->
			<?php WCVendors_Pro_Store_Form::snapchat_username( ); ?>
			<?php do_action(  'wcvendors_settings_after_social' ); ?>
		</div>
	<?php endif; ?>

	<!-- </div> -->
		<!-- Submit Button -->
		<!-- DO NOT REMOVE THE FOLLOWING TWO LINES -->
		<?php WCVendors_Pro_Store_Form::save_button( __( 'Save Changes', 'wcvendors-pro') ); ?>
</div>
	</form>

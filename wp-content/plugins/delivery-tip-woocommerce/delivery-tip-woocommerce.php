<?php

// Start the session
session_start();


/*
Plugin Name: Delivery Tip for Woocommerce 

Plugin URI: http://plugins.brazilloops.com/

Description: Add user amount/percentage "tip" at checkout. After activation, please go to Settings -> Delivery Tip and fill the form to show how it will appear at checkout page.

Version: 1.0

Author: Eduardo Sallada

Author URI: http://plugins.brazilloops.com/

License: GPL2
*/


  if ( ! defined( 'ABSPATH' ) ) die();
  load_plugin_textdomain('deliverytip', false, dirname(plugin_basename(__FILE__)) . '/languages/');

// Admin

if ( is_admin() ){
    add_action( 'admin_menu', 'deliverytip_admin_menu' );


// Settings in the plugin row

	add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'plugin_deliverytip', -10);
    add_action( 'admin_init', 'deliverytip_setup' );

}

    function deliverytip_setup() {	
        register_setting( 'deliverytip_options', 'deltip_settings' );

}

// Settings submenu

function deliverytip_admin_menu() {

    add_options_page( __('Delivery Tip', 'deliverytip'), __('Delivery Tip', 'deliverytip'), 'manage_options', __FILE__ , 'deliverytip__options_page');
}

// Page content 

function deliverytip__options_page() {

    if (!current_user_can('manage_options'))

    {

      wp_die( __('Sorry, you do not have sufficient permissions to access this page.') );

    }


$options = get_option( 'deltip_settings' );

// Database

update_option( 'deltip_settings', $options );



?>



<?php 



// Display the settings screen

    
   
    echo "<h2>" . __( 'Delivery Tip WooCommerce', 'deliverytip' ) . "</h2>";
    echo "<p>" . __( 'Please fill before running the plugin:', 'deliverytip' ) . "</p>";

?>



<form name="form" method="post" action="options.php" id="delform">

<table class="widefat fixed" border="0" style="width:60%; margin-top:40px;">
  
  <?php

    settings_fields( 'deliverytip_options' );

	$options = get_option( 'deltip_settings' );

?>  

  
  <tbody>
    <tr>
    <td><?php _e('Enable Delivery Tip' , 'deliverytip' ); ?></td>
    <td><input name="deltip_settings[enable_deltip]" type="checkbox" value="1" 
     <?php if (  1 == ($options['enable_deltip']) ) echo 'checked="checked"'; ?>   /></td>
    </tr>

 </tbody>
  
 <tbody>
     <tr>
	   <td><?php _e('Tip name:' , 'deliverytip' ); ?></td>
	   <td><input style="width:40%;" type="text" name="deltip_settings[tip_name]" value="<?php echo esc_attr( $options['tip_name'] ); ?>" placeholder="Tip"/></td>
     </tr>

 </tbody>
  
 <tbody>
     <tr>
	   <td><?php _e('Tip message:' , 'deliverytip' ); ?></td>
     <td><textarea style="width:100%;" type="text" name="deltip_settings[first_call]" placeholder="Hi! Would you like to add tip to the delivery driver?"><?php echo esc_attr( $options['first_call'] ); ?></textarea> </td>
     </tr>
 </tbody>
 
 <tbody>
     <tr>
	   <td><?php _e('Enter custom amount text:' , 'deliverytip' ); ?></td>
     <td><input style="width:40%;" type="text" name="deltip_settings[tip_holder]" value="<?php echo esc_attr( $options['tip_holder'] ); ?>" placeholder="Custom amount" /></td>
     </tr>
  </tbody>
   
  <tbody>
     <tr>
	   <td><?php _e('Button text:' , 'deliverytip' ); ?></td>
     <td><input style="width:40%;" type="text" name="deltip_settings[button]" value="<?php echo esc_attr( $options['button'] ); ?>" placeholder="Apply" /></td>
     </tr>
  </tbody>

  <tbody>
     <tr>
	   <td><?php _e('Success text:' , 'deliverytip' ); ?></td>
     <td><input style="width:100%;" type="text" name="deltip_settings[success_message]" 
                value="<?php echo esc_attr( $options['success_message'] ); ?>" placeholder="Thank you! Tip added successfully." /></td>
     </tr>
   </tbody>

   <tbody>
     <tr>
	   <td><?php _e('Error text:' , 'deliverytip' ); ?></td>
     <td><input style="width:100%;" type="text" name="deltip_settings[empty_message]" 
                value="<?php echo esc_attr( $options['empty_message'] ); ?>" placeholder="Tip amount is empty." /></td>
     </tr>
   </tbody>
<?php
  
  //filters
  
  add_filter('sanitize_option_tip_name','wp_filter_kses');
  add_filter('sanitize_option_first_call','wp_filter_kses');
  add_filter('sanitize_option_tip_holder','wp_filter_kses');
  add_filter('sanitize_option_button','wp_filter_kses');
  add_filter('sanitize_option_success_message','wp_filter_kses');
  add_filter('sanitize_option_empty_message','wp_filter_kses');
  
?> 
  

</table>
  
  <?php submit_button(); ?>
  
  
  
</form>


<?php

}

// Links to the plugin row

function plugin_deliverytip($links) {

       $deltip_plugin_links = array(

          '<a href="options-general.php?page=delivery-tip-woocommerce/delivery-tip-woocommerce.php">'.__('Settings').'</a>',
          '<a href="http://plugins.brazilloops.com" >'.__('Support').'</a>'

           );

return array_merge( $deltip_plugin_links, $links );

}

// enable delivery tip

       add_action('woocommerce_after_checkout_billing_form', 'deltip_checkbox');

            function deltip_checkbox(){
                global $woocommerce, $post, $wpdb;
                $options = get_option( 'deltip_settings' );	
                   if ( $options['enable_deltip'] == '1' ) { 
					 

// Form at checkout page

?>




<div class="delivery-tip-woocommerce">

<p class="woocommerce-info" id="deltip">

		<?php echo $options['first_call']; ?>  

	</p></div>



<div class="delivery-tip-woocommerce1" style="border: 1px solid #e0dadf; 
padding: 20px;
margin: 2em 0 2em 0; 
text-align: left;
-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 5px;"
>
  
<form action="" method="post">  
<center class= "tip-ex">
  
  
  <?php global $woocommerce;?>   
  <h4><?php echo _e("Tips Guide","deliverytip"); ?></h2>
  
  <?php $sug1 = $woocommerce->cart->cart_contents_total * 0.10;?>
  <?php $sug2 = $woocommerce->cart->cart_contents_total * 0.15;?>
  <?php $sug3 = $woocommerce->cart->cart_contents_total * 0.20;?> 
  <?php $sug4 = $woocommerce->cart->cart_contents_total * 0.18;?>

  <!--
  <input type="radio" name="tipsug" onclick="setTipValue(this.value)" value="<?php echo (number_format($sug1,2));?>">
  <?php echo "10% ($".(number_format($sug1,2)).")";?><br>
  
  
  <input type="radio" name="tipsug" onclick="setTipValue(this.value)" value="<?php echo (number_format($sug2,2));?>">
  <?php echo "15% ($".(number_format($sug2,2)).")";?> <br>
  
 
  <input type="radio" name="tipsug" onclick="setTipValue(this.value)" value= "<?php echo (number_format($sug3,2));?>">  
  <?php echo "20% ($". (number_format($sug3,2)).")";?> <br>
  
  <input type="radio" name="tipsug" onclick="setTipValue(this.value)" value="cash"> <br>
  <?php echo " CASH";?>  

  -->



  <select onchange="setTipValue(this.value)">
    <option value="0">Select</option>
    <option value="<?php echo (number_format($sug1,2));?>"><?php echo "10% ($".(number_format($sug1,2)).")";?></option>
    <option value="<?php echo (number_format($sug2,2));?>"><?php echo "15% ($".(number_format($sug2,2)).")";?></option>
    <option value="<?php echo (number_format($sug4,2));?>"><?php echo "18% ($".(number_format($sug4,2)).")";?></option>
    <option value="<?php echo (number_format($sug3,2));?>"><?php echo "20% ($".(number_format($sug3,2)).")";?></option>
  </select>

<script>		
function setTipValue(tipsug) {
    document.getElementById("value_deltip").value = tipsug;
}
  
  
</script>
  
  </center>
  
	
	  Amount: <input style="line-height:1.5em;text-align: center;width: 47%;" type="text" 
    name="value_deltip" class="input-text-deltip" 
    placeholder="<?php echo $options['tip_holder']; ?>" 
    id="value_deltip" value="">
   

    <!-- 
  value for entry box (original)
   <?php echo $_POST['value_deltip']; ?>
    -->
 
	<p class="form-row form-row-last" style="float:left;width: 59%;margin-top: 15px;">
	  
		<input type="submit"  class="button" id="submit_deltip" name="apply_amount" value="<?php echo $options['button'];?>">
        
	</p>

	<div class="clear"></div>
	    <script type="text/javascript">
    $("deltip").resetForm();
    </script>
	
</form></div>



<?php 
					 
 //stick to the session and check erros
if($_SESSION['value_deltip']) {
  $_SESSION['value_deltip'] = $_POST['value_deltip'];
}

  

if ( empty( $_POST['value_deltip'] ) && isset( $_POST['value_deltip'] ) ) {

echo '<ul class="woocommerce-error">

			<li>'.$options['empty_message'].'</li>

	</ul>';

}



if ( !empty( $_POST['value_deltip'] ) && isset( $_POST['value_deltip'] ) ) {

update_option('new_value_deltip', $_POST['value_deltip'] );



echo '<div class="woocommerce-message">'.$options['success_message'].'</div>';

}



if ( ! isset( $_POST['value_deltip'] ) && empty( $_POST['value_deltip'] ) ) {

update_option('new_value_deltip', 0 );

}

}}

//finalizando to checkout

add_action( 'woocommerce_cart_calculate_fees','woocommerce_delivery_tip' );

function woocommerce_delivery_tip() {

global $woocommerce, $post;

$options = get_option( 'deltip_settings' );

if ( get_option('new_value_deltip') !== '0'  ) {

if ( $options['enable_deltip'] == '1' ) { 



	$tip_name = ''.$options['tip_name'].'';

	$value_deltip = get_option('new_value_deltip');

    $del_tip = ( $value_deltip ) ;


  $woocommerce->cart->add_fee( $tip_name, $del_tip, false, '' );
  
}}}








<?php
/*
Plugin Name: WP Payeezy Pay
Version: 2.78
Plugin URI: http://richard-rottman.com/
Description: Connects a WordPress site to First Data's Payeezy Gateway using the Payment Page/Hosted Checkout method of integration. 
Author: Richard Rottman
Author URI: http://richard-rottman.com/
*/
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wp_payeezy_pay_action_links' );

function wp_payeezy_pay_action_links( $links ) {
   $links[] = '<a href="'. esc_url( get_admin_url(null, 'options-general.php?page=wp-payeezy-pay%2Fwp-payeezy-pay.php') ) .'">Settings</a>';
   return $links;
}

function wppayeezypaymentform() {
$x_login = get_option('x_login');
$x_recurring_billing_id = get_option('x_recurring_billing_id');
$x_currency_code = get_option('x_currency_code');
$mode = get_option ('mode') ; // production or demo
$mode2 = get_option ('mode2') ; // payments or donations
$button_text= get_option ('button_text') ; // 
$wp_payeezy_stylesheet = plugins_url('wp-payeezy-pay/css/stylesheet.css');
$url_to_stylesheet = $wp_payeezy_stylesheet; 


if ( $mode2 == "pay") {
  $pay_file = plugins_url('wp-payeezy-pay/pay.php'); 
 
}
// Payments WITH the option of making the payment recurring.
  elseif ( $mode2 == "pay-rec" ) {
      $pay_file = plugins_url('wp-payeezy-pay/pay-rec.php'); 
}

// Payments WITH the option of making the payment recurring.
elseif ( $mode2 == "pay-rec-req" ) {
  $pay_file = plugins_url('wp-payeezy-pay/pay-rec.php'); 
}

// Donations WITHOUT the option of making the donation recurring.
elseif ( $mode2 == "donate"  ) {
    $pay_file = plugins_url('wp-payeezy-pay/donate.php'); 
}

// Donations WITH the option of making the donation recurring.
else {
    $pay_file = plugins_url('wp-payeezy-pay/donate-rec.php'); 
}

if ( $button_text == "pay-now") {
  $button = 'Pay Now'; 
}

elseif ( $button_text == "donate-now") {
      $button = 'Donate Now'; 
}

elseif ( $button_text == "continue") {
      $button = 'Continue'; 
}

elseif ( $button_text == "make-it-so") {
      $button = 'Make it so'; 
}

else {
      $button = 'Continue to Secure Payment Form'; 
}


// This is the Ref. Num that shows in Transactions on the front page.
$x_invoice_num = get_option('x_invoice_num');

// This is the Cust. Ref. Num that shows in Transactions on the front page. Also referred
// to as Purchase Order or PO number. It's a reference number submitted by the customer
// for their own record keeping.

$x_po_num = get_option('x_po_num');

// This shows up on the final order form as "Item" unless Invoice Number is used.
// If there is an Invoice Number sent, that overrides the Description. 

$x_description = get_option('x_description');

// Just an extra reference number if Invoice Number and Customer Reference Number are
// not enough referance numbers for your purposes. 

$x_reference_3 = get_option('x_reference_3');

// Next three are custom fields that if passed over to Payeezy, will show populated on
// the secure order form and the information collected will be passed a long with all the
// other info. 

$x_user1 = get_option('x_user1') ;
$x_user2 = get_option('x_user2') ;
$x_user3 = get_option('x_user3') ;

// If you want to collect the customer's phone number and/or email address, you can do so
// by giving these two fields a name, such as "phone" and "email."

$x_phone = get_option('x_phone') ;
$x_email = get_option('x_email') ;

// 
$x_amount = get_option('x_amount') ;
$x_company = get_option('x_company') ;


ob_start(); // stops the shortcode output from appearing at the very top of the post/page.
?>

<!-- v.2.76 -->
<style>
#x_first_name{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_last_name{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_company{width:250px;height:30px;padding:0 0 0 6px;border-color:#222}#x_address{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_city{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_state{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}select#x_state,select#x_country{padding:0 0 0 6px;-webkit-appearance:none;-moz-appearance:none;-webkit-border-radius:0;border-radius:0;-webkit-box-shadow:none;box-shadow:none}#x_country{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_invoice_num{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_po_num{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_reference_3{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_user_1{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_user_2{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_user_3{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_email{width:250px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_phone{width:125px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_zip{width:125px;height:30px;padding:0 0 0 6px;border:1px solid #222}#entered_coupon{width:200px;height:30px;padding:0 0 0 6px;border:1px solid #222}#x_description{padding:0 0 0 6px;border:1px solid #222}label{font-weight:700;display:block}#x_amount{width:100px;height:30px;padding:0 0 0 6px;border-color:#222;border-width:1px;margin-bottom:20px;margin-right:10px;display:inline}#x_amount2{width:100px;height:30px;padding:0 0 0 6px;border-color:#222;border-width:1px;margin-bottom:20px;display:inline}#wp_payeezy_payment_form input[type="submit"]{width:auto}
</style>
<div id="wp_payeezy_payment_form">
<form action="<?php echo $pay_file;?>" method="post">
<input name="x_recurring_billing_id" value="<?php echo $x_recurring_billing_id;?>" type="hidden" >
<input name="x_login" value="<?php echo $x_login;?>" type="hidden" >
<input name="mode" value="<?php echo $mode;?>" type="hidden" >
<input name="x_type" value="AUTH_CAPTURE" type="hidden" >
<input name="x_currency_code" value="<?php echo $x_currency_code;?>" type="hidden" >
<p><label>First Name</label><input name="x_first_name" value="" id="x_first_name" type="text" required></p> 
<p><label>Last Name</label><input name="x_last_name" id="x_last_name" value="" type="text" required></p> 
<?php if (!empty($x_company)) {
  echo '<p><label>';
  echo $x_company;
  echo '</label>';
  echo '<input name="x_company" value="" type="text" id="x_company" required>';
  echo '</p>';
}
else {
  echo '<input name="x_company" value="" type="hidden" >';
  }?>
<p><label>Street Address</label><input name="x_address" id="x_address" value="" type="text" required></p> 
<p><label>City</label><input name="x_city" id="x_city" value="" type="text" required></p> 
<p><label>State/Province</label><select name="x_state" id="x_state" required>
<option value="" selected="selected">Select a State/Province</option>
<option value="Alabama">Alabama</option>
<option value="Alaska">Alaska</option>
<option value="Arizona">Arizona</option>
<option value="Arkansas">Arkansas</option>
<option value="California">California</option>
<option value="Colorado">Colorado</option>
<option value="Connecticut">Connecticut</option>
<option value="Delaware">Delaware</option>
<option value="District of Columbia">District of Columbia</option>
<option value="Florida">Florida</option>
<option value="Georgia">Georgia</option>
<option value="Hawaii">Hawaii</option>
<option value="Idaho">Idaho</option>
<option value="Illinois">Illinois</option>
<option value="Indiana">Indiana</option>
<option value="Iowa">Iowa</option>
<option value="Kansas">Kansas</option>
<option value="Kentucky">Kentucky</option>
<option value="Louisiana">Louisiana</option>
<option value="Maine">Maine</option>
<option value="Maryland">Maryland</option>
<option value="Massachusetts">Massachusetts</option>
<option value="Michigan">Michigan</option>
<option value="Minnesota">Minnesota</option>
<option value="Mississippi">Mississippi</option>
<option value="Missouri">Missouri</option>
<option value="Montana">Montana</option>
<option value="Nebraska">Nebraska</option>
<option value="Nevada">Nevada</option>
<option value="New Hampshire">New Hampshire</option>
<option value="New Jersey">New Jersey</option>
<option value="New Mexico">New Mexico</option>
<option value="New York">New York</option>
<option value="North Carolina">North Carolina</option>
<option value="North Dakota">North Dakota</option>
<option value="Ohio">Ohio</option>
<option value="Oklahoma">Oklahoma</option>
<option value="Oregon">Oregon</option>
<option value="Pennsylvania">Pennsylvania</option>
<option value="Puerto Rico">Puerto Rico</option>
<option value="Rhode Island">Rhode Island</option>
<option value="South Carolina">South Carolina</option>
<option value="South Dakota">South Dakota</option>
<option value="Tennessee">Tennessee</option>
<option value="Texas">Texas</option>
<option value="Utah">Utah</option>
<option value="Vermont">Vermont</option>
<option value="Virginia">Virginia</option>
<option value="Washington">Washington</option>
<option value="West Virginia">West Virginia</option>
<option value="Wisconsin">Wisconsin</option>
<option value="Wyoming">Wyoming</option>
<option value="" disabled="disabled">-------------</option>
<option value="Alberta">Alberta</option>
<option value="British Columbia">British Columbia</option>
<option value="Manitoba">Manitoba</option>
<option value="New Brunswick">New Brunswick</option>
<option value="Newfoundland">Newfoundland</option>
<option value="Northwest Territories">Northwest Territories</option>
<option value="Nova Scotia">Nova Scotia</option>
<option value="Nunavut">Nunavut</option>
<option value="Ontario">Ontario</option>
<option value="Prince Edward Island">Prince Edward Island</option>
<option value="Quebec">Quebec</option>
<option value="Saskatchewan">Saskatchewan</option>
<option value="Yukon">Yukon</option>
<option value="" disabled="disabled">-------------</option>
<option value="N/A">Not Applicable</option>
</select></p>
<p><label>Zip Code</label><input name="x_zip" id="x_zip" value="" type="text" required></p> 
<p><label>Country</label><select id="x_country" name="x_country" onchange="switch_province()" tabindex="10">
<option value="" selected="selected">Select a Country</option>
<option value="United States">United States</option>
<option value="Canada">Canada</option>
<option value="" disabled="disabled">-------------</option>
<option value="Afghanistan">Afghanistan</option>
<option value="Aland Islands">Aland Islands</option>
<option value="Albania">Albania</option>
<option value="Algeria">Algeria</option>
<option value="American Samoa">American Samoa</option>
<option value="Andorra">Andorra</option>
<option value="Angola">Angola</option>
<option value="Anguilla">Anguilla</option>
<option value="Antarctica">Antarctica</option>
<option value="Antigua and Barbuda">Antigua and Barbuda</option>
<option value="Argentina">Argentina</option>
<option value="Armenia">Armenia</option>
<option value="Aruba">Aruba</option>
<option value="Australia">Australia</option>
<option value="Austria">Austria</option>
<option value="Azerbaijan">Azerbaijan</option>
<option value="Bahamas">Bahamas</option>
<option value="Bahrain">Bahrain</option>
<option value="Bangladesh">Bangladesh</option>
<option value="Barbados">Barbados</option>
<option value="Belarus">Belarus</option>
<option value="Belgium">Belgium</option>
<option value="Belize">Belize</option>
<option value="Benin">Benin</option>
<option value="Bermuda">Bermuda</option>
<option value="Bhutan">Bhutan</option>
<option value="Bolivia">Bolivia</option>
<option value="Bonaire, Sint Eustatius and Saba">Bonaire, Sint Eustatius and Saba</option>
<option value="Bosnia and Herzegowina">Bosnia and Herzegowina</option>
<option value="Botswana">Botswana</option>
<option value="Bouvet Island">Bouvet Island</option>
<option value="Brazil">Brazil</option>
<option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
<option value="Brunei Darussalam">Brunei Darussalam</option>
<option value="Bulgaria">Bulgaria</option>
<option value="Burkina Faso">Burkina Faso</option>
<option value="Burundi">Burundi</option>
<option value="Cambodia">Cambodia</option>
<option value="Cameroon">Cameroon</option>
<option value="Canada">Canada</option>
<option value="Cape Verde">Cape Verde</option>
<option value="Cayman Islands">Cayman Islands</option>
<option value="Central African Republic">Central African Republic</option>
<option value="Chad">Chad</option>
<option value="Chile">Chile</option>
<option value="China">China</option>
<option value="Christmas Island">Christmas Island</option>
<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
<option value="Colombia">Colombia</option>
<option value="Comoros">Comoros</option>
<option value="Congo">Congo</option>
<option value="Congo, the Democratic Republic of the">Congo, the Democratic Republic of the</option>
<option value="Cook Islands">Cook Islands</option>
<option value="Costa Rica">Costa Rica</option>
<option value="Cote D&#x27;Ivoire">Cote D&#x27;Ivoire</option>
<option value="Croatia">Croatia</option>
<option value="Cuba">Cuba</option>
<option value="Curacao">Curacao</option>
<option value="Cyprus">Cyprus</option>
<option value="Czech Republic">Czech Republic</option>
<option value="D.P.R. Korea">D.P.R. Korea</option>
<option value="Denmark">Denmark</option>
<option value="Djibouti">Djibouti</option>
<option value="Dominica">Dominica</option>
<option value="Dominican Republic">Dominican Republic</option>
<option value="Ecuador">Ecuador</option>
<option value="Egypt">Egypt</option>
<option value="El Salvador">El Salvador</option>
<option value="Equatorial Guinea">Equatorial Guinea</option>
<option value="Eritrea">Eritrea</option>
<option value="Estonia">Estonia</option>
<option value="Ethiopia">Ethiopia</option>
<option value="Falkland Islands">Falkland Islands</option>
<option value="Faroe Islands">Faroe Islands</option>
<option value="Fiji">Fiji</option>
<option value="Finland">Finland</option>
<option value="France">France</option>
<option value="French Guiana">French Guiana</option>
<option value="French Polynesia">French Polynesia</option>
<option value="French Southern Territories">French Southern Territories</option>
<option value="Gabon">Gabon</option>
<option value="Gambia">Gambia</option>
<option value="Georgia">Georgia</option>
<option value="Germany">Germany</option>
<option value="Ghana">Ghana</option>
<option value="Gibraltar">Gibraltar</option>
<option value="Greece">Greece</option>
<option value="Greenland">Greenland</option>
<option value="Grenada">Grenada</option>
<option value="Guadeloupe">Guadeloupe</option>
<option value="Guam">Guam</option>
<option value="Guatemala">Guatemala</option>
<option value="Guernsey">Guernsey</option>
<option value="Guinea">Guinea</option>
<option value="Guinea-Bissau">Guinea-Bissau</option>
<option value="Guyana">Guyana</option>
<option value="Haiti">Haiti</option>
<option value="Heard and McDonald Islands">Heard and McDonald Islands</option>
<option value="Honduras">Honduras</option>
<option value="Hong Kong SAR, PRC">Hong Kong SAR, PRC</option>
<option value="Hungary">Hungary</option>
<option value="Iceland">Iceland</option>
<option value="India">India</option>
<option value="Indonesia">Indonesia</option>
<option value="Iran">Iran</option>
<option value="Iraq">Iraq</option>
<option value="Ireland">Ireland</option>
<option value="Isle of Man">Isle of Man</option>
<option value="Israel">Israel</option>
<option value="Italy">Italy</option>
<option value="Jamaica">Jamaica</option>
<option value="Japan">Japan</option>
<option value="Jersey">Jersey</option>
<option value="Jordan">Jordan</option>
<option value="Kazakhstan">Kazakhstan</option>
<option value="Kenya">Kenya</option>
<option value="Kiribati">Kiribati</option>
<option value="Korea">Korea</option>
<option value="Kuwait">Kuwait</option>
<option value="Kyrgyzstan">Kyrgyzstan</option>
<option value="Lao People&#x27;s Republic">Lao People&#x27;s Republic</option>
<option value="Latvia">Latvia</option>
<option value="Lebanon">Lebanon</option>
<option value="Lesotho">Lesotho</option>
<option value="Liberia">Liberia</option>
<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
<option value="Liechtenstein">Liechtenstein</option>
<option value="Lithuania">Lithuania</option>
<option value="Luxembourg">Luxembourg</option>
<option value="Macau">Macau</option>
<option value="Macedonia">Macedonia</option>
<option value="Madagascar">Madagascar</option>
<option value="Malawi">Malawi</option>
<option value="Malaysia">Malaysia</option>
<option value="Maldives">Maldives</option>
<option value="Mali">Mali</option>
<option value="Malta">Malta</option>
<option value="Marshall Islands">Marshall Islands</option>
<option value="Martinique">Martinique</option>
<option value="Mauritania">Mauritania</option>
<option value="Mauritius">Mauritius</option>
<option value="Mayotte">Mayotte</option>
<option value="Mexico">Mexico</option>
<option value="Micronesia">Micronesia</option>
<option value="Moldova">Moldova</option>
<option value="Monaco">Monaco</option>
<option value="Mongolia">Mongolia</option>
<option value="Montenegro">Montenegro</option>
<option value="Montserrat">Montserrat</option>
<option value="Morocco">Morocco</option>
<option value="Mozambique">Mozambique</option>
<option value="Myanmar">Myanmar</option>
<option value="Namibia">Namibia</option>
<option value="Nauru">Nauru</option>
<option value="Nepal">Nepal</option>
<option value="Netherlands">Netherlands</option>
<option value="New Caledonia">New Caledonia</option>
<option value="New Zealand">New Zealand</option>
<option value="Nicaragua">Nicaragua</option>
<option value="Niger">Niger</option>
<option value="Nigeria">Nigeria</option>
<option value="Niue">Niue</option>
<option value="Norfolk Island">Norfolk Island</option>
<option value="Northern Mariana Islands">Northern Mariana Islands</option>
<option value="Norway">Norway</option>
<option value="Not Available">Not Available</option>
<option value="Oman">Oman</option>
<option value="Pakistan">Pakistan</option>
<option value="Palau">Palau</option>
<option value="Palestine, State of">Palestine, State of</option>
<option value="Panama">Panama</option>
<option value="Papua New Guinea">Papua New Guinea</option>
<option value="Paraguay">Paraguay</option>
<option value="Peru">Peru</option>
<option value="Philippines">Philippines</option>
<option value="Pitcairn">Pitcairn</option>
<option value="Poland">Poland</option>
<option value="Portugal">Portugal</option>
<option value="Puerto Rico">Puerto Rico</option>
<option value="Qatar">Qatar</option>
<option value="Reunion">Reunion</option>
<option value="Romania">Romania</option>
<option value="Russian Federation">Russian Federation</option>
<option value="Rwanda">Rwanda</option>
<option value="Saint Barthelemy">Saint Barthelemy</option>
<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
<option value="Saint Lucia">Saint Lucia</option>
<option value="Saint Martin (French part)">Saint Martin (French part)</option>
<option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
<option value="Samoa">Samoa</option>
<option value="San Marino">San Marino</option>
<option value="Sao Tome and Principe">Sao Tome and Principe</option>
<option value="Saudi Arabia">Saudi Arabia</option>
<option value="Senegal">Senegal</option>
<option value="Serbia">Serbia</option>
<option value="Seychelles">Seychelles</option>
<option value="Sierra Leone">Sierra Leone</option>
<option value="Singapore">Singapore</option>
<option value="Sint Maarten (Dutch part)">Sint Maarten (Dutch part)</option>
<option value="Slovakia">Slovakia</option>
<option value="Slovenia">Slovenia</option>
<option value="Solomon Islands">Solomon Islands</option>
<option value="Somalia">Somalia</option>
<option value="South Africa">South Africa</option>
<option value="South Georgia and the South Sandwich Islands">South Georgia and the South Sandwich Islands</option>
<option value="South Sudan">South Sudan</option>
<option value="Spain">Spain</option>
<option value="Sri Lanka">Sri Lanka</option>
<option value="St Helena">St Helena</option>
<option value="St Pierre and Miquelon">St Pierre and Miquelon</option>
<option value="Sudan">Sudan</option>
<option value="Suriname">Suriname</option>
<option value="Svalbard and Jan Mayen Islands">Svalbard and Jan Mayen Islands</option>
<option value="Swaziland">Swaziland</option>
<option value="Sweden">Sweden</option>
<option value="Switzerland">Switzerland</option>
<option value="Syrian Arab Republic">Syrian Arab Republic</option>
<option value="Taiwan Region">Taiwan Region</option>
<option value="Tajikistan">Tajikistan</option>
<option value="Tanzania">Tanzania</option>
<option value="Thailand">Thailand</option>
<option value="Timor-Leste">Timor-Leste</option>
<option value="Togo">Togo</option>
<option value="Tokelau">Tokelau</option>
<option value="Tonga">Tonga</option>
<option value="Trinidad and Tobago">Trinidad and Tobago</option>
<option value="Tunisia">Tunisia</option>
<option value="Turkey">Turkey</option>
<option value="Turkmenistan">Turkmenistan</option>
<option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
<option value="Tuvalu">Tuvalu</option>
<option value="Uganda">Uganda</option>
<option value="Ukraine">Ukraine</option>
<option value="United Arab Emirates">United Arab Emirates</option>
<option value="United Kingdom">United Kingdom</option>
<option value="United States">United States</option>
<option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
<option value="Uruguay">Uruguay</option>
<option value="Uzbekistan">Uzbekistan</option>
<option value="Vanuatu">Vanuatu</option>
<option value="Vatican City State (Holy See)">Vatican City State (Holy See)</option>
<option value="Venezuela">Venezuela</option>
<option value="Viet Nam">Viet Nam</option>
<option value="Virgin Islands (British)">Virgin Islands (British)</option>
<option value="Virgin Islands (US)">Virgin Islands (US)</option>
<option value="Wallis and Futuna Islands">Wallis and Futuna Islands</option>
<option value="Western Sahara">Western Sahara</option>
<option value="Yemen">Yemen</option>
<option value="Zambia">Zambia</option>
<option value="Zimbabwe">Zimbabwe</option>
</select></p>     
<?php

//// Invoice ////
if (!empty($x_invoice_num)) {
  echo '<p><label>';
  echo $x_invoice_num;
  echo '</label>';
  echo '<input name="x_invoice_num" value="" type="text" id="x_invoice_num" required>';
  echo '</p>';
}
else {
  echo '<input name="x_invoice_num" value="" type="hidden" >';
  }

//// PO Number ////
  if (!empty($x_po_num)) {
    echo '<p><label>';
  echo $x_po_num;
  echo '</label>';
  echo '<input name="x_po_num" value="" type="text" id="x_po_num" required>';
  echo '</p>';
}

else {
  echo '<input name="x_po_num" value="" type="hidden">';
  }
//// Reference Number 3 ////
if (!empty($x_reference_3)) {
    echo '<p><label>';
  echo $x_reference_3;
  echo '</label>';
  echo '<input name="x_reference_3" value="" type="text" id="x_reference_3" required>';
  echo '</p>';
}

else {
  echo '<input name="x_reference_3" value="" type="hidden">';
  }

//// User Defined 1 //// 
if (!empty($x_user1)) {                                                              
    echo '<p><label>';
  echo $x_user1;
  echo '</label>';
  echo '<input name="x_user1" value="" type="text" id="x_user_1" required>';
  echo '</p>';
}

else {
  echo '<input name="x_user1" value="" type="hidden">';
  }

//// User Defined 2 ////
if (!empty($x_user2)) {
    echo '<p><label>';
  echo $x_user2;
  echo '</label>';
  echo '<input name="x_user2" value="" type="text" id="x_user_2" required>';
  echo '</p>';
}

else {
  echo '<input name="x_user2" value="" type="hidden">';
  }

//// User Defined 3 ////
if (!empty($x_user3)) {
    echo '<p><label>';
  echo $x_user3;
  echo '</label>';
  echo '<input name="x_user3" value="" type="text" id="x_user_3" required>';
  echo '</p>';
}

else {
  echo '<input name="x_user3" value="" type="hidden">';
  }

//// Email ////
if (!empty($x_email)) {
  echo '<p><label>';
  echo $x_email;
  echo '</label>';
  echo '<input name="x_email" value="" type="email" id="x_email" required>';
  echo '</p>';
}

else {
  echo '<input name="x_email" value="" type="hidden">';
  }

//// Phone Number ////
if (!empty($x_phone)) {
  echo '<p><label>';
  echo $x_phone;
  echo '</label>';
  echo '<input name="x_phone" value="" type="tel" id="x_phone" required>';
  echo '</p>';
}

else {
  echo '<input name="x_phone" value="" type="hidden">';
  }

//// Description ////
if ( !empty( $x_description ) ) {
  echo '<p><label>';
  echo $x_description;
  echo '</label>';
  echo '<textarea cols="40" rows="5" name="x_description" id="x_description"></textarea>';
  echo '</p>';
}

else {
  echo '<input name="x_description" value="" type="hidden">';
}


if (!empty($x_amount)) {
  
  echo '<input name="x_amount" value="';
  echo $x_amount;
  echo '" type="hidden" id="x_amount" >';
  
  }

else {

if (($mode2 == "donate") || ($mode2 == "donate-rec")) {
?>
<p><label>Donation Amount</label>
<?php
//$wp_payeezy_donation_amounts = plugins_url('wp-payeezy-pay/select/donation_amounts.php'); 
//echo file_get_contents( "$wp_payeezy_donation_amounts" ); ?>
<input type="radio" name="x_amount1" value="10.00"> $10&nbsp;<?php echo $x_currency_code;?></br>
<input type="radio" name="x_amount1" value="25.00"> $25&nbsp;<?php echo $x_currency_code;?></br>
<input type="radio" name="x_amount1" checked="checked" value="50.00"> $50&nbsp;<?php echo $x_currency_code;?></br>
<input type="radio" name="x_amount1" value="75.00"> $75&nbsp;<?php echo $x_currency_code;?></br>
<input type="radio" name="x_amount1" value="100.00"> $100&nbsp;<?php echo $x_currency_code;?></br>
<input type="radio" name="x_amount1" value="0.00"> Other $ <input name="x_amount2" id="x_amount2" value="" min="1" step="0.01" type="number">&nbsp;<?php echo $x_currency_code;?></br>
</p>
<?php
}
 
else {
echo '<p><label>Amount</label><input name="x_amount" id="x_amount" value="" min="1" step="0.01" type="number">&nbsp;';
echo $x_currency_code;
echo '</p>';
}

}

if ($mode2 == "donate-rec" ) {
      echo '<p><input type="checkbox" name="recurring" id="recurring" value="TRUE" >&nbsp;Automatically repeat this same donation once a month, beginning in 30 days.</p>';
}
// Pay with optional Recurring
if ($mode2 == "pay-rec" ) {
    echo '<p><input type="checkbox" name="recurring" id="recurring" value="TRUE" >&nbsp;Automatically repeat this same payment once a month, beginning in 30 days.</p> ';
}

// Pay with required Recurring
if ($mode2 == "pay-rec-req" ) {
    echo '<input type="hidden" name="recurring" value="TRUE" >';
}
?>
<p><input type="submit" id="submit" value="<?php echo $button;?>"></p>
</form>
<br>

</div>
<?php
return ob_get_clean();

}

// create custom plugin settings menu
add_action('admin_menu', 'wppayeezypay_create_menu');
function wppayeezypay_create_menu() {

//create new top-level menu
add_menu_page(
  'WP Payeezy Pay', // page title
   'WP Payeezy Pay', // menu title display
    'administrator', // minimum capability to view the menu
     'wp-payeezy-pay/wp-payeezy-pay.php', // the slug
      'wppayeezypay_settings_page', // callback function used to display page content
       plugin_dir_url( __FILE__ ) . 'images/icon.png');

//call register settings function
add_action( 'admin_init', 'register_wppayeezypay_settings' );
}

add_shortcode('wp_payeezy_payment_form', 'wppayeezypaymentform');



if ( !file_exists(plugin_dir_path(__FILE__) . get_option('x_login') . '.php') ) {
add_action( 'admin_notices', 'wppayeezypay_no_transaction_key' );
}

function wppayeezypay_no_transaction_key() {  
      
      // Begin process of saving the transaction key to a seperate php file.
    $transaction_key = ( get_option('transaction_key') );
    $base = dirname(__FILE__); // That's the directory path
    $filename = get_option('x_login') . '.php';
    $fileUrl = $base . '/' . $filename;
    $data = '<?php $transaction_key = "'. get_option('transaction_key') . '"?>';
    file_put_contents($fileUrl, $data);
    // end of process of saving transaction key


}

function register_wppayeezypay_settings() {
//register our settings
register_setting( 'wppayeezypay-group', 'x_login' );
register_setting( 'wppayeezypay-group', 'transaction_key' );
register_setting( 'wppayeezypay-group', 'response_key' );
register_setting( 'wppayeezypay-group', 'x_recurring_billing_id' );
register_setting( 'wppayeezypay-group', 'x_currency_code' );
register_setting( 'wppayeezypay-group', 'x_amount' );
register_setting( 'wppayeezypay-group', 'x_user1' );
register_setting( 'wppayeezypay-group', 'x_user2' );
register_setting( 'wppayeezypay-group', 'x_user3' );
register_setting( 'wppayeezypay-group', 'mode' ); // Production or Demo
register_setting( 'wppayeezypay-group', 'mode2' ); // Payments of Donations
register_setting( 'wppayeezypay-group', 'button_text' );
register_setting( 'wppayeezypay-group', 'x_invoice_num' );
register_setting( 'wppayeezypay-group', 'x_po_num' );
register_setting( 'wppayeezypay-group', 'x_description' );
register_setting( 'wppayeezypay-group', 'x_reference_3' );
register_setting( 'wppayeezypay-group', 'x_phone' );
register_setting( 'wppayeezypay-group', 'x_email' );
register_setting( 'wppayeezypay-group', 'x_company' );
}

function wppayeezypay_settings_page() {
$readme_wp_payeezy_pay = plugins_url('wp-payeezy-pay/readme.txt');
?>
<div class="wp-payeezy-pay-wrap">
<style>
a {
  text-decoration: none;
}

input[type=text],
 .wp-admin select {
  width: 100%;
}

.x_amount {
  width: 100px !important;
}

h3 a {
  color: #000;
}
</style>
  <h2>WP Payeezy Pay version 2.78</h2>
  By <a href="https://profiles.wordpress.org/rickrottman/">Richard Rottman</a><br>
  <div style="background-color: transparent;border: none;color: #444;margin: 0; float:left;padding: none;width:950px;">    
    <form method="post" action="options.php">
      <?php settings_fields( 'wppayeezypay-group' ); ?>
      <?php do_settings_sections( 'wppayeezypay-group' ); ?>
       <div style="background: none repeat scroll 0 0 #fff;border: 1px solid #bbb;color: #444;margin: 10px 20px 0 0; float:left;padding: 20px;text-shadow: 1px 1px #FFFFFF;width:500px">
       <h3>Required Settings</h3>
           
      <table class="form-table">

      <tr valign="top">
        <th scope="row">Payment Page ID</th>
          <td valign="top"><input type="text" style="font-family:'Lucida Console', Monaco, monospace;" size="35" name="x_login" value="<?php echo esc_attr( get_option('x_login') ); ?>" required/></td>
      </tr>
      <tr valign="top">
      <th scope="row">Transaction Key</th>
        <td valign="top"><input type="text" style="font-family:'Lucida Console', Monaco, monospace;" size="35" name="transaction_key" value="<?php echo esc_attr( get_option('transaction_key') ); ?>" required/></td>  
      </tr>

      <tr valign="top">
      <th scope="row">Currency Code</th>
       <td><select name="x_currency_code">
        <option value="USD" <?php if( get_option('x_currency_code') == "USD" ): echo 'selected'; endif;?> >USD (Unted States Dollar)</option>
        <option value="AUD" <?php if( get_option('x_currency_code') == "AUD" ): echo 'selected'; endif;?> >AUD (Australian Dollar)</option>
        <option value="BRL" <?php if( get_option('x_currency_code') == "BRL" ): echo 'selected'; endif;?> >BRL (Brazilian Real)</option>
        <option value="CZK" <?php if( get_option('x_currency_code') == "CZK" ): echo 'selected'; endif;?> >CZK (Czech Koruna)</option>
        <option value="DKK" <?php if( get_option('x_currency_code') == "DKK" ): echo 'selected'; endif;?> >DKK (Danish Krone)</option>
        <option value="EUR" <?php if( get_option('x_currency_code') == "EUR" ): echo 'selected'; endif;?> >EUR (Euro)</option>
        <option value="HKD" <?php if( get_option('x_currency_code') == "HKD" ): echo 'selected'; endif;?> >HKD (Hong Kong Dollar)</option>
        <option value="HUF" <?php if( get_option('x_currency_code') == "HUF" ): echo 'selected'; endif;?> >HUF (Hungarian Forint)</option>
        <option value="ILS" <?php if( get_option('x_currency_code') == "ILS" ): echo 'selected'; endif;?> >ILS (Israeli New Sheqel)</option>
        <option value="JPY" <?php if( get_option('x_currency_code') == "JPY" ): echo 'selected'; endif;?> >JPY (Japanese Yen)</option>
        <option value="MYR" <?php if( get_option('x_currency_code') == "MYR" ): echo 'selected'; endif;?> >MYR (Malaysian Ringgit)</option>
        <option value="MXN" <?php if( get_option('x_currency_code') == "MXN" ): echo 'selected'; endif;?> >MXN (Mexican Peso)</option>
        <option value="NOK" <?php if( get_option('x_currency_code') == "NOK" ): echo 'selected'; endif;?> >NOK (Norwegian Krone)</option>
        <option value="NZD" <?php if( get_option('x_currency_code') == "NZD" ): echo 'selected'; endif;?> >NZD (New Zealand Dollar)</option>
        <option value="PHP" <?php if( get_option('x_currency_code') == "PHP" ): echo 'selected'; endif;?> >PHP (Philippine Peso)</option>
        <option value="PLN" <?php if( get_option('x_currency_code') == "PLN" ): echo 'selected'; endif;?> >PLN (Polish Zloty)</option>
        <option value="CZK" <?php if( get_option('x_currency_code') == "CZK" ): echo 'selected'; endif;?> >CZK (Czech Koruna)</option>
        <option value="GBP" <?php if( get_option('x_currency_code') == "GBP" ): echo 'selected'; endif;?> >GBP (Pound Sterling)</option>
        <option value="SGD" <?php if( get_option('x_currency_code') == "SGD" ): echo 'selected'; endif;?> >SGD (Singapore Dollar)</option>
        <option value="SEK" <?php if( get_option('x_currency_code') == "SEK" ): echo 'selected'; endif;?> >SEK (Swedish Krona)</option>
        <option value="CHF" <?php if( get_option('x_currency_code') == "CHF" ): echo 'selected'; endif;?> >CHF (Swiss Franc)</option>
        <option value="TWD" <?php if( get_option('x_currency_code') == "TWD" ): echo 'selected'; endif;?> >TWD (Taiwan New Dollar)</option>
        <option value="THB" <?php if( get_option('x_currency_code') == "THB" ): echo 'selected'; endif;?> >THB (Thai Baht)</option>
        <option value="TRY" <?php if( get_option('x_currency_code') == "TRY" ): echo 'selected'; endif;?> >TRY (Turkish Lira)</option>
      </select><br>
        Needs to match the Currency Code of the terminal. 
        </td>
      </tr>
      
      <tr valign="top">
        <th scope="row">Mode</th>
          <td><select name="mode"/>
            <option value="live" <?php if( get_option('mode') == "live" ): echo 'selected'; endif;?> >Live</option>
            <option value="demo" <?php if( get_option('mode') == "demo" ): echo 'selected'; endif;?> >Demo</option>
            </select><br>
           To get a free Payeezy demo account,<br> <a href="https://provisioning.demo.globalgatewaye4.firstdata.com/signup" target="_blank">go here.
          </td>

      </tr>
      <tr valign="top">
        <th scope="row">Type of Transactions</th>
          <td><select name="mode2"/>
            <option value="pay" <?php if( get_option('mode2') == "pay" ): echo 'selected'; endif;?> >Payments</option>
            <option value="pay-rec" <?php if( get_option('mode2') == "pay-rec" ): echo 'selected'; endif;?> >Payments with optional Recurring</option>
            <option value="pay-rec-req" <?php if( get_option('mode2') == "pay-rec-req" ): echo 'selected'; endif;?> >Payments with automatic Recurring</option>
            <option value="donate" <?php if( get_option('mode2') == "donate" ): echo 'selected'; endif;?> >Donations</option>
            <option value="donate-rec" <?php if( get_option('mode2') == "donate-rec" ): echo 'selected'; endif;?> >Donations with optional Recurring</option>
            </select>
          </td>
      </tr>

      <tr valign="top">
        <th scope="row">Button Text</th>
          <td><select name="button_text"/>
            <option value="pay-now" <?php if( get_option('button_text') == "pay-now" ): echo 'selected'; endif;?> >Pay Now</option>
            <option value="donate-now" <?php if( get_option('button_text') == "donate-now" ): echo 'selected'; endif;?> >Donate Now</option>
            <option value="make-it-so" <?php if( get_option('button_text') == "make-it-so" ): echo 'selected'; endif;?> >Make it so</option>
            <option value="continue" <?php if( get_option('button_text') == "continue" ): echo 'selected'; endif;?> >Continue</option>
            <option value="continue-to-secure" <?php if( get_option('button_text') == "continue-to-secure" ): echo 'selected'; endif;?> >Continue to Secure Payment Form</option>
            </select><br>
           This is the text that is displayed on the button a cardholder selects to go to the secure form hosted by First Data.</td>
      </tr>

    </table>
    <hr>
      <h3>Optional Settings</h3>
      <table class="form-table">
      <tr valign="top">
      <th scope="row">Response Key</th>
        <td valign="top"><input type="text" style="font-family:'Lucida Console', Monaco, monospace;" size="35" name="response_key" value="<?php echo esc_attr( get_option('response_key') ); ?>"/><br>
        <p>Used for <strong><a href="http://richard-rottman.com/wp-payeezy-results/" target="_blank" >WP Payeezy Results</a></strong>.</p></td>  
      </tr>
      
      <tr valign="top">
      <th scope="row">Amount</th>
       <td valign="top"><span class="large">$</span> <input type="text" class="x_amount" name="x_amount" value="<?php echo esc_attr( get_option('x_amount') ); ?>" /><br>
        If an amount is entered above, the card holder will not have the option of entering an amount. They will be charged what you enter here.</td>  
      </tr>

      
      <tr valign="top">
        <th scope="row">Recurring Billing ID</th>
          <td valign="top"><input type="text" style="font-family:'Lucida Console', Monaco, monospace;" size="35" name="x_recurring_billing_id" value="<?php echo esc_attr( get_option('x_recurring_billing_id') ); ?>" /><br>
          Leave blank unless processing recurring transactions. The recurring plan <b>must</b> have the Frequecy set to "Monthly."</td>
        <?php
        // If one of the recurring modes is selected and there is not a Recurring Plan ID entered,
        // a red warning appears next to the field pointing out that one needs to be entered. 
        $recurring = get_option('x_recurring_billing_id');
        if (empty($recurring)) {
        if (( get_option('mode2') === "pay-rec") || ( get_option('mode2') === "donate-rec" ) || ( get_option('mode2') === "pay-rec-req" )){ 
          echo "<td valign='top' style='color:red'>&#8656; Please enter a Recurring Billing ID</td>";
        }}
          ?>
        </tr>
       
    </table>
    <hr>
    <h3>Optional Payment Form Fields</h3>
    <table class="form-table">
      <tr valign="top"> If you would like to use any of these fields, just assign a name to them
        and they will appear on your form with that name. Do not assign a name, and they will not appear. If a field appears on your form,
        the cardholder cannot proceed to Payeezy until they enter a value. </tr>
      <tr valign="top">
        <th scope="row">x_invoice_num</th>
        <td><input type="text" name="x_invoice_num" value="<?php echo esc_attr( get_option('x_invoice_num') ); ?>" /><br>
        Truncated to the first 20 characters and becomes part of the transaction. It appears in column “Ref Num” under Transaction Search.</td>
      </tr>
      <tr valign="top">
        <th scope="row">x_po_num</th>
        <td><input type="text" name="x_po_num" value="<?php echo esc_attr( get_option('x_po_num') ); ?>" /><br>
        Purchase order number. Truncated to the first 20 characters and becomes part of the transaction. It appears in column “Customer Reference Number” under Transaction Search.</td>
      </tr>
      <tr valign="top">
        <th scope="row">x_reference_3</th>
        <td><input type="text" name="x_reference_3" value="<?php echo esc_attr( get_option('x_reference_3') ); ?>" /><br>
        Additional reference data. Maximum length 30 and becomes part of the transaction. It appears in column "Reference Number 3" under Transaction Search.</td>
      </tr>
      <tr valign="top">
        <th scope="row">x_user1</th>
        <td><input type="text" name="x_user1" value="<?php echo esc_attr( get_option('x_user1') ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">x_user2</th>
        <td><input type="text" name="x_user2" value="<?php echo esc_attr( get_option('x_user2') ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">x_user3</th>
        <td><input type="text" name="x_user3" value="<?php echo esc_attr( get_option('x_user3') ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">x_phone</th>
        <td><input type="text" name="x_phone" value="<?php echo esc_attr( get_option('x_phone') ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">x_email</th>
        <td><input type="text" name="x_email" value="<?php echo esc_attr( get_option('x_email') ); ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">x_description</th>
        <td><input type="text" name="x_description" value="<?php echo esc_attr( get_option('x_description') ); ?>" /><br>
        This field is a large textarea input that the customer can write a note or memo.</td>
      </tr>
      <tr valign="top">
        <th scope="row">x_company</th>
        <td><input type="text" name="x_company" value="<?php echo esc_attr( get_option('x_company') ); ?>" /><br>
       </td>
      </tr>
    </table>
    
<?php
   submit_button('Save Settings'); 

   // Begin process of saving the transaction key to a seperate php file.
    $transaction_key = ( get_option('transaction_key') );
    $base = dirname(__FILE__); // That's the directory path
    $filename = get_option('x_login') . '.php';
    $fileUrl = $base . '/' . $filename;
    $data = '<?php $transaction_key = "'. get_option('transaction_key') . '"?>';
    file_put_contents($fileUrl, $data);
    // end of process of saving transaction key
?>
    
</form>
 </div>
 <div style="background: none repeat scroll 0 0 #fff;border: 1px solid #bbb;color: #444;margin: 10px 0; float:left;padding: 5px 20px;text-shadow: 1px 1px #FFFFFF;width:280px">
<h3>The Shortcode</h3>
<p>To add the Payeezy payment form to a Page or a Post, add the following <a href="https://codex.wordpress.org/Shortcode" target="_blank">shortcode</a> in the Page or Post's content:<br>
<p style="text-align:center;font-size: 120%;font-family:'Lucida Console', Monaco, monospace;">[wp_payeezy_payment_form]</p> 
</div>

<div style="background: none repeat scroll 0 0 #fff;border: 1px solid #bbb;color: #444;margin: 10px 0; float:left;padding: 5px 20px;text-shadow: 1px 1px #FFFFFF;width:280px">
<h3><a href="http://richard-rottman.com/wp-payeezy-results/" target="_blank">WP Payeezy Results</a></h3>
<p>I have created a premium plugin that allows First Data Payeezy to send transaction information back to WordPress. The information can then be accessed in WordPress. </p>
<p><a class="button" href="http://richard-rottman.com/wp-payeezy-results/" target="_blank">Find out More</a></p>
</div>

<div style="background: none repeat scroll 0 0 #fff;border: 1px solid #bbb;color: #444;margin: 10px 0; float:left;padding: 5px 20px;text-shadow: 1px 1px #FFFFFF;width:280px">
<h3>Support and Modifications</h3>
<p>Feel free to contact me if you have any questions or sugestions. I can also modify the basic plugin to better suit your specific needs.</p>
Email: <a href="mailto:rlrottman@gmail.com">rlrottman@gmail.com</a><br>
Twitter: <a href="https://twitter.com/RLRottman/">@rlrottman</a><br>
Website: <a href="http://richard-rottman.com/contact-gravity-rocket/">www.richard-rottman.com</a><br>
 </p>
</div>
 
</div>
<?php } ?>
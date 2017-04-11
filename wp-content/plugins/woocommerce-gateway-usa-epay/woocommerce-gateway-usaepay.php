<?php
/**
 * Plugin Name: WooCommerce USA ePay Gateway
 * Plugin URI: http://woocommerce.com/woocommerce
 * Description: Extends WooCommerce with a <a href="https://www.usaepay.com" target="_blank">USA ePay</a> gateway. A USA ePay gateway account, and a server with SSL support and an SSL certificate is required for security reasons.
 * Author: SkyVerge
 * Author URI: http://www.woocommerce.com/
 * Version: 1.8.0
 * Text Domain: wc-gateway-usaepay
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2014-2017, SkyVerge, Inc.
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Gateway-USA-ePay
 * @author    SkyVerge
 * @category  Gateways
 * @copyright Copyright (c) 2014-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '8672c81a80cc602c7b9aade5bd701f9c', '18700' );

// USA ePay lib
require_once( plugin_dir_path( __FILE__ ) . 'lib/usaepay.php' );

add_action( 'plugins_loaded', 'woocommerce_gateway_usaepay_init', 0 );

function woocommerce_gateway_usaepay_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'wc-gateway-usaepay', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );

	define( 'USAEPAY_DIR', WP_PLUGIN_DIR . "/" . plugin_basename( dirname(__FILE__) ) . '/' );


	/**
	 * USA ePay Gateway Class
	 **/
	class WC_Gateway_Usaepay extends WC_Payment_Gateway {

		public function __construct() {

			$this->id                 = 'usaepay';
			$this->method_title       = __( 'USA ePay', 'wc-gateway-usaepay' );
			$this->method_description = __( 'USA ePay allows customers to checkout using a credit card', 'wc-gateway-usaepay' );
			$this->logo               = untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/images/usaepay.jpg';
			$this->icon               = untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/images/cards.png';
			$this->has_fields         = false;

			// Load the form fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Get setting values
			$this->enabled         = $this->settings['enabled'];
			$this->title           = $this->settings['title'];
			$this->description     = $this->settings['description'];
			$this->trandescription = $this->settings['trandescription'];
			$this->sourcekey       = $this->settings['sourcekey'];
			$this->pin             = $this->settings['pin'];
			$this->command         = $this->settings['command'];
			$this->custreceipt     = $this->settings['custreceipt'];
			$this->testmode        = $this->settings['testmode'];
			$this->usesandbox      = $this->settings['usesandbox'];
			$this->gatewayurl      = $this->settings['gatewayurl'];
			$this->cvv             = $this->settings['cvv'];
			$this->debugon         = $this->settings['debugon'];
			$this->debugrecipient  = $this->settings['debugrecipient'];

			// Actions
			add_action( 'admin_notices', array( $this, 'usaepay_ssl_check') );

			if ( is_admin() ) {
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			}
		}


		/**
		 * Check if SSL is enabled and notify the user
		 */
		function usaepay_ssl_check() {

			if ( 'no' == get_option('woocommerce_force_ssl_checkout' ) && 'yes' == $this->enabled ) {

				echo '<div class="error"><p>';

					/* translators: Placeholders: %1$s - <a>, %2$s - </a> */
					printf( esc_html__( 'USA ePay is enabled and the %1$sForce secure checkout%2$s option is disabled; your checkout is not secure! Please enable this feature and ensure your server has a valid SSL certificate installed.', 'wc-gateway-usaepay' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '">',
						'</a>'
					);

				echo '</p></div>';
			}
		}


		/**
		 * Initialize Gateway Settings Form Fields
		 */
		function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'wc-gateway-usaepay' ),
					'label'       => __( 'Enable USA ePay Gateway', 'wc-gateway-usaepay' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-usaepay' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'wc-gateway-usaepay' ),
					'default'     => __( 'Credit card', 'wc-gateway-usaepay' )
				),
				'description' => array(
					'title'       => __( 'Description', 'wc-gateway-usaepay' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description which is displayed to the customer.', 'wc-gateway-usaepay' ),
					'default'     => 'Pay with your MasterCard, Visa, Discover or American Express'
				),
				'trandescription' => array(
					'title'       => __( 'Transaction Description', 'wc-gateway-usaepay' ),
					'type'        => 'textarea',
					'description' => __( 'This controls the description that is added to the transaction sent to USA ePay.', 'wc-gateway-usaepay' ),
					'default'     => 'Order from WooCommerce.'
				),
				'sourcekey' => array(
					'title'       => __( 'Source Key', 'wc-gateway-usaepay' ),
					'type'        => 'text',
					'description' => __( 'Source Key generated by the Merchant Console', 'wc-gateway-usaepay' ),
					'default'     => ''
				),
				'pin' => array(
					'title'       => __( 'PIN', 'wc-gateway-usaepay' ),
					'type'        => 'text',
					'description' => __( 'Pin for Source Key. This field is required only if the merchant has set a Pin in the merchant console.', 'wc-gateway-usaepay' ),
					'default'     => ''
				),
				'command' => array(
					'title'       => __( 'Payment Type', 'wc-gateway-usaepay' ),
					'type'        => 'select',
					'description' => __( 'Payment command to run. ', 'wc-gateway-usaepay' ),
					'options'     => array(
						'sale'     => 'Sale',
						'authonly' => 'Authorize Only'
					),
					'default'     => ''
				),
				'custreceipt' => array(
					'title'       => __( 'Receipt Email', 'wc-gateway-usaepay' ),
					'label'       => __( 'Send receipt email', 'wc-gateway-usaepay' ),
					'type'        => 'checkbox',
					'description' => __( 'If checked USA ePay will send a receipt email to the customer in addition to WooCommerce order email.', 'wc-gateway-usaepay' ),
					'default'     => 'no'
				),
				'testmode' => array(
					'title'       => __( 'Test Mode', 'wc-gateway-usaepay' ),
					'label'       => __( 'Enable Test Mode', 'wc-gateway-usaepay' ),
					'type'        => 'checkbox',
					'description' => __( 'If checked then the transaction will be simulated by USA ePay, but not actually processed.', 'wc-gateway-usaepay' ),
					'default'     => 'no'
				),
				'usesandbox' => array(
					'title'       => __( 'Sandbox', 'wc-gateway-usaepay' ),
					'label'       => __( 'Enable Sandbox', 'wc-gateway-usaepay' ),
					'type'        => 'checkbox',
					'description' => __( 'If checked the sandbox server will be used. Overrides the gateway url parameter.', 'wc-gateway-usaepay' ),
					'default'     => 'no'
				),
				'gatewayurl' => array(
					'title'       => __( 'Gateway URL Override', 'wc-gateway-usaepay' ),
					'type'        => 'text',
					'description' => __( 'Override for URL of USA ePay gateway processor. Optional, leave blank for default URL.', 'wc-gateway-usaepay' ),
					'default'     => ''
				),
				'cvv' => array(
					'title'       => __( 'CVV', 'wc-gateway-usaepay' ),
					'label'       => __( 'Require customer to enter credit card CVV code', 'wc-gateway-usaepay' ),
					'type'        => 'checkbox',
					'default'     => 'no'
				),
				'debugon' => array(
					'title'       => __( 'Debugging', 'wc-gateway-usaepay' ),
					'label'       => __( 'Enable debug emails', 'wc-gateway-usaepay' ),
					'type'        => 'checkbox',
					'description' => __( 'Receive emails containing the data sent to and from USA ePay.', 'wc-gateway-usaepay' ),
					'default'     => 'no'
				),
				'debugrecipient' => array(
					'title'       => __( 'Debugging Email', 'wc-gateway-usaepay' ),
					'type'        => 'text',
					'description' => __( 'Who should receive the debugging emails.', 'wc-gateway-usaepay' ),
					'default'     =>  get_option( 'admin_email' )
				),
			);
		}


		/**
		 * Admin Panel Options
		 * - Options for bits like 'title' and availability on a country-by-country basis
		 */
		public function admin_options() {
			?>
			<h3><?php esc_html_e( 'USA ePay', 'wc-gateway-usaepay' ); ?></h3>
			<p><?php esc_html_e( 'USA ePay allows customers to checkout using a credit card by adding credit card fields on the checkout page and then sending the details to USA ePay for verification.', 'wc-gateway-usaepay' ); ?></p>
			<table class="form-table">
				<?php $this->generate_settings_html(); ?>
			</table><!--/.form-table-->
			<?php
		}


		/**
		 * Payment fields for USA ePay.
		 */
		function payment_fields() {
			?>
			<fieldset>
				<p class="form-row form-row-first">
					<label for="usaepay_ccnum"><?php esc_html_e( 'Credit card number', 'wc-gateway-usaepay' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text" id="usaepay_ccnum" name="usaepay_ccnum" />
				</p>
				<div class="clear"></div>

				<p class="form-row">
					<label for="ccnum"><?php esc_html_e( 'Card holder name', 'wc-gateway-usaepay'); ?> <span class="required">*</span></label>
					<input type="text" class="input-text" id="usaepay_cardholdername" name="usaepay_cardholdername" />
				</p>
				<div class="clear"></div>

				<p class="form-row form-row-first">
					<label for="usaepay_expmonth"><?php esc_html_e( 'Expiration date', 'wc-gateway-usaepay' ); ?> <span class="required">*</span></label>
					<select name="usaepay_expmonth" id="usaepay_expmonth" class="woocommerce-select woocommerce-cc-month">
						<option value=""><?php esc_html_e( 'Month', 'wc-gateway-usaepay' ); ?></option>
						<?php
							$months = array();
							for ( $i = 1; $i <= 12; $i++ ) {
								$timestamp = mktime(0, 0, 0, $i, 1);
								$months[ date( 'm', $timestamp ) ] = date( 'F', $timestamp );
							}
							foreach ( $months as $num => $name ) {
								printf( '<option value="%s">%s</option>', $num, $name );
							}
						?>
					</select>
					<select name="usaepay_expyear" id="usaepay_expyear" class="woocommerce-select woocommerce-cc-year">
						<option value=""><?php esc_html_e( 'Year', 'wc-gateway-usaepay' ); ?></option>
						<?php
							for ( $i = date( 'y' ); $i <= date( 'y' ) + 15; $i++ ) {
								printf( '<option value="%u">20%u</option>', $i, $i );
							}
						?>
					</select>
				</p>

				<?php if ( $this->cvv == 'yes' ) : ?>
					<p class="form-row form-row-last">
						<label for="usaepay_cvv"><?php esc_html_e( 'Card security code', 'wc-gateway-usaepay' ); ?> <span class="required">*</span></label>
						<input type="text" class="input-text" id="usaepay_cvv" name="usaepay_cvv" maxlength="4" style="width: 65px" />
					</p>
				<?php endif; ?>

				<div class="clear"></div>
				<p><?php echo wp_kses_post( $this->description ); ?></p>
			</fieldset>
			<?php
		}


		/**
		 * Process the payment and return the result
		 **/

		function process_payment( $order_id ) {

			$order = wc_get_order( $order_id );

			// Create request
			$tran = new umTransaction;

			$tran->key = $this->sourcekey;
			$tran->pin = $this->pin;
			$tran->ip  = $_SERVER['REMOTE_ADDR']; // This allows fraud blocking on the customers ip address

			$tran->cabundle = ABSPATH . WPINC . '/certificates/ca-bundle.crt';

			if ( 0 != strlen( $this->gatewayurl ) ) {
				$tran->gateway = $this->gatewayurl;
				$gatewayurl = $this->gatewayurl;
			} else {
				if ( 'yes' == $this->usesandbox ) {
					$gatewayurl = "https://sandbox.usaepay.com/gate";
				} else {
					$gatewayurl = "https://www.usaepay.com/gate";
				}
			}

			if ( 'yes' == $this->custreceipt ) {
				$tran->custreceipt = 'yes';
			}

			if ( 'yes' == $this->testmode ) {
				$tran->testmode = 'yes';
			} else {
				$tran->testmode = 0;
			}

			if ( 'yes' == $this->usesandbox ) {
				$tran->usesandbox = true;
			} else {
				$tran->usesandbox = false;
			}

			$tran->command = $this->command;

			$tran->card         = $this->get_post( 'usaepay_ccnum' );
			$tran->exp          = $this->get_post( 'usaepay_expmonth' ) . $this->get_post( 'usaepay_expyear' );
			$tran->amount       = $order->get_total();
			$tran->invoice      = ltrim( $order->get_order_number(), _x( '#', 'hash before the order number', 'wc-gateway-usaepay' ) );
			$tran->cardholder   = $this->get_post( 'usaepay_cardholdername' );
			$tran->street       = $this->get_order_prop( $order, 'billing_address_1' );
			$tran->zip          = $this->get_order_prop( $order, 'billing_postcode' );
			$tran->description  = $this->trandescription;

			if ( 'yes' == $this->cvv ) {
				$tran->cvv2   = $this->get_post( 'usaepay_cvv' );
			}

			// Billing Fields
			$tran->billfname    = $this->get_order_prop( $order, 'billing_first_name' );
			$tran->billlname    = $this->get_order_prop( $order, 'billing_last_name' );
			$tran->billcompany  = $this->get_order_prop( $order, 'billing_company' );
			$tran->billstreet   = $this->get_order_prop( $order, 'billing_address_1' );
			$tran->billstreet2  = $this->get_order_prop( $order, 'billing_address_2' );
			$tran->billcity     = $this->get_order_prop( $order, 'billing_city' );
			$tran->billstate    = $this->get_order_prop( $order, 'billing_state' );
			$tran->billzip      = $this->get_order_prop( $order, 'billing_postcode' );
			$tran->billcountry  = $this->get_order_prop( $order, 'billing_country' );
			$tran->billphone    = $this->get_order_prop( $order, 'billing_phone' );
			$tran->email        = $this->get_order_prop( $order, 'billing_email' );

			// Shipping Fields
			$tran->shipfname    = $this->get_order_prop( $order, 'shipping_first_name' );
			$tran->shiplname    = $this->get_order_prop( $order, 'shipping_last_name' );
			$tran->shipcompany  = $this->get_order_prop( $order, 'shipping_company' );
			$tran->shipstreet   = $this->get_order_prop( $order, 'shipping_address_1' );
			$tran->shipstreet2  = $this->get_order_prop( $order, 'shipping_address_2' );
			$tran->shipcity     = $this->get_order_prop( $order, 'shipping_city' );
			$tran->shipstate    = $this->get_order_prop( $order, 'shipping_state' );
			$tran->shipzip      = $this->get_order_prop( $order, 'shipping_postcode' );
			$tran->shipcountry  = $this->get_order_prop( $order, 'shipping_country' );

			$usaepay_request = array(
				"key"         => $this->sourcekey,
				"pin"         => $this->pin,
				"customer ip" => $_SERVER['REMOTE_ADDR'],
				"gatewayurl"  => $gatewayurl,
				"testmode"    => $this->testmode,
				"usessandbox" => $this->usesandbox,
				"command"     => $this->command,
				"card"        => "[Card number not available for debug]",
				"expiration"  => $this->get_post( 'usaepay_expmonth' ) . $this->get_post( 'usaepay_expyear' ),
				"amount"      => $order->get_total(),
				"order id"    => ltrim( $order->get_order_number(), _x( '#', 'hash before the order number', 'wc-gateway-usaepay' ) ),
				"cardholder"  => $this->get_post( 'usaepay_cardholdername' ),
				"street"      => $this->get_order_prop( $order, 'billing_address_1' ),
				"zip"         => $this->get_order_prop( $order, 'billing_postcode' ),
				"description" => $this->trandescription,
				"cvv"         => $this->get_post( 'usaepay_cvv' ),
				"billfname"   => $this->get_order_prop( $order, 'billing_first_name' ),
				"billlname"   => $this->get_order_prop( $order, 'billing_last_name' ),
				"billcompany" => $this->get_order_prop( $order, 'billing_company' ),
				"billstreet"  => $this->get_order_prop( $order, 'billing_address_1' ),
				"billstreet2" => $this->get_order_prop( $order, 'billing_address_2' ),
				"billcity"    => $this->get_order_prop( $order, 'billing_city' ),
				"billstate"   => $this->get_order_prop( $order, 'billing_state' ),
				"billzip"     => $this->get_order_prop( $order, 'billing_postcode' ),
				"billcountry" => $this->get_order_prop( $order, 'billing_country' ),
				"billphone"   => $this->get_order_prop( $order, 'billing_phone' ),
				"email"       => $this->get_order_prop( $order, 'billing_email' ),
				"shipfname"   => $this->get_order_prop( $order, 'shipping_first_name' ),
				"shiplname"   => $this->get_order_prop( $order, 'shipping_last_name' ),
				"shipcompany" => $this->get_order_prop( $order, 'shipping_company' ),
				"shipstreet"  => $this->get_order_prop( $order, 'shipping_address_1' ),
				"shipstreet2" => $this->get_order_prop( $order, 'shipping_address_2' ),
				"shipcity"    => $this->get_order_prop( $order, 'shipping_city' ),
				"shipstate"   => $this->get_order_prop( $order, 'shipping_state' ),
				"shipzip"     => $this->get_order_prop( $order, 'shipping_postcode' ),
				"shipcountry" => $this->get_order_prop( $order, 'shipping_country' )
			);

			$this->send_debugging_email( "USA ePay Gateway: " . $gatewayurl . "\n\nSENDING REQUEST:" . print_r( $usaepay_request, true ) );

			// ************************************************
			// Process request

			if ( $tran->Process() ) {
				// Successful payment

				// Send debug email
				$success_text  = "URL: " . $gatewayurl . "\n\nRESULT: Card Approved\n";
				$success_text .= "Authcode: " . $tran->authcode . "\n";
				$success_text .= "Result: " . $tran->result . "\n";
				$success_text .= "AVS Result: " . $tran->avs . "\n";

				if ( 'yes' == $this->cvv ) {
					$success_text.= "CVV2 Result: " . $tran->cvv2 . "\n";
				} else {
					$success_text.= "CVV2 not collected\n";
				}

				// save transaction ID/auth code to order meta
				update_post_meta( $this->get_order_prop( $order, 'id' ), '_transaction_id', $tran->refnum );
				update_post_meta( $this->get_order_prop( $order, 'id' ), '_wc_usa_epay_credit_card_trans_id', $tran->refnum );
				update_post_meta( $this->get_order_prop( $order, 'id' ), '_wc_usa_epay_credit_card_authorization_code', $tran->authcode );

				$this->send_debugging_email( $success_text );

				$order->add_order_note( __( 'USA ePay payment completed', 'wc-gateway-usaepay' ) . ' (Result: ' . $tran->result . ')' );
				$order->payment_complete();
				WC()->cart->empty_cart();

				// Empty awaiting payment session
				unset( WC()->session->order_awaiting_payment );

				// Return thank you redirect
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);

			} else {

				// Send debug email
				$error_text  = "USA ePay Gateway Error.\nResponse reason text: " . $tran->error . "\n";
				$error_text .= "Result: " .$tran->result . "\n";

				if ( @$tran->curlerror ) {
					$error_text .= "\nCurl Error: ". $tran->curlerror;
				}

				$this->send_debugging_email( $error_text );

				$cancelNote = __('USA ePay payment failed', 'wc-gateway-usaepay') .' '.
					__('Payment was rejected due to an error', 'wc-gateway-usaepay') . ': "' . $tran->error . '". ';

				$order->add_order_note( $cancelNote );

				$this->debug( __( 'There was an error processing your payment', 'wc-gateway-usaepay' ) . ': ' . $tran->result . '', 'error' );
			}
		}

		/**
		 * validate_fields function.
		 */
		public function validate_fields() {

			$cardName            = $this->get_post( 'usaepay_cardholdername' );
			$cardNumber          = $this->get_post( 'usaepay_ccnum' );
			$cardCSC             = $this->get_post( 'usaepay_cvv' );
			$cardExpirationMonth = $this->get_post( 'usaepay_expmonth' );
			$cardExpirationYear  = '20' . $this->get_post( 'usaepay_expyear' );

			if ( 'yes' == $this->cvv ) {

				//check security code
				if ( ! ctype_digit( $cardCSC ) ) {
					$this->debug( __( 'Card security code is invalid (only digits are allowed)', 'wc-gateway-usaepay' ), 'error' );
					return false;
				}
			}

			//check expiration data
			$currentYear = date('Y');

			if ( ! ctype_digit( $cardExpirationMonth ) || ! ctype_digit( $cardExpirationYear ) ||
				$cardExpirationMonth > 12 ||
				$cardExpirationMonth < 1 ||
				$cardExpirationYear < $currentYear ||
				$cardExpirationYear > $currentYear + 20
			) {
				$this->debug( __( 'Card expiration date is invalid', 'wc-gateway-usaepay' ), 'error' );
				return false;
			}

			//check card number
			$cardNumber = str_replace( array( ' ', '-' ), '', $cardNumber );

			if ( empty($cardNumber) || ! ctype_digit( $cardNumber ) ) {
				$this->debug( __( 'Card number is invalid', 'wc-gateway-usaepay' ), 'error' );
				return false;
			}

			if ( empty( $cardName ) ) {
				$this->debug( __( 'You must enter the name of card holder', 'wc-gateway-usaepay' ), 'error' );
				return false;
			}

			return true;
		}

		/**
		 * Gets an order property.
		 *
		 * This method exists for WC 3.0+ compatibility.
		 *
		 * @since 1.8.0
		 * @param \WC_Order $order the order object
		 * @param string $prop the property to get
		 * @return mixed the property value
		 */
		private function get_order_prop( WC_Order $order, $prop ) {

			$wc_version = defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;

			if ( $wc_version && version_compare( $wc_version, '3.0', '>=' ) && is_callable( array( $order, "get_{$prop}" ) ) ) {
				$value = $order->{"get_{$prop}"}( 'edit' );
			} else {
				$value = $order->$prop;
			}

			return $value;
		}

		/**
		 * Get post data if set
		 **/
		private function get_post( $name ) {
			if ( isset( $_POST[$name] ) ) {
				return $_POST[$name];
			}
			return NULL;
		}

		/**
		 * Output a message or error
		 * @param  string $message
		 * @param  string $type
		 */
		public function debug( $message, $type = 'notice' ) {
			wc_add_notice( $message, $type );
		}

		/**
		 * Send debugging email
		 **/
		private function send_debugging_email( $debug ) {

			if ( 'yes' != $this->debugon ) {
				return; // Debug must be enabled
			}

			if ( ! $this->debugrecipient ) {
				return; // Recipient needed
			}

			// Send the email
			wp_mail( $this->debugrecipient, __('USA ePay Debug', 'wc-gateway-usaepay'), $debug );
		}


	}

	/**
	 * Plugin page links
	 */
	function wc_usa_epay_plugin_links( $links ) {

		$wc_version = defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;

		if ( $wc_version && version_compare( $wc_version, '2.6', '>=' ) ) {
			$section_id = 'usaepay';
		} else {
			$section_id = 'wc_gateway_usaepay';
		}

		$plugin_links = array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section_id ) ) . '">' . __( 'Settings', 'wc-gateway-usaepay' ) . '</a>',
			'<a href="https://woocommerce.com/my-account/tickets/">' . __( 'Support', 'wc-gateway-usaepay' ) . '</a>',
			'<a href="http://docs.woocommerce.com/document/usa-epay">' . __( 'Docs', 'wc-gateway-usaepay' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_usa_epay_plugin_links' );

	/**
	 * Add the gateway to woocommerce
	 **/
	function add_usaepay_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Usaepay'; return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'add_usaepay_gateway' );
}

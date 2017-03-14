<?php
/**
 * The WCVendors Pro Abstract Controller class
 *
 * This is the abstract controller class for all front end actions 
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public
 * @author     Jamie Madden <support@wcvendors.com>
 */
class WCVendors_Pro_Shipping_Method extends WC_Shipping_Method {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $wcvendors_pro    The ID of this plugin.
	 */
	private $wcvendors_pro;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Is the plugin in debug mode 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      bool    $debug    plugin is in debug mode 
	 */
	private static $debug;

	/**
	 * Is the plugin base directory 
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $base_dir  string path for the plugin directory 
	 */
	private $base_dir;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $wcvendors_pro       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( ) {

		$this->wcvendors_pro 	= 'wcvendors-pro'; 
		$this->version 			= '1.0.4';
		self::$debug 			= false;
		$this->base_dir			= plugin_dir_path( dirname(__FILE__) ); 

		$this->id                 	= 'wcv_pro_vendor_shipping';
		$this->method_title     	= __( 'Vendor Shipping', 'wcvendors-pro' );
		$this->method_description   = __( 'This shipping module is for your vendors to input their own shipping prices on their Pro Dashboard.  <p>The prices you enter for Product Cost and Handling Fees will only be used if a vendor has not entered their own prices on their Pro Vendor Dashboard.  <p>The simplest shipping system is Flat Rate, where vendors can input a cost within their country, and outside of their country.  Country Table Rate will require vendors to enter country codes that they ship to and set prices for each country.  <p><strong><a href="https://www.wcvendors.com/kb/pro-shipping/" target="prodocs">WC Vendors Pro Shipping Documentation</a></strong>', 'wcvendors-pro' );

		$this->init_form_fields(); 
		$this->init_settings(); 

        $this->enabled						= $this->settings[ 'enabled' ];
		$this->title 						= $this->settings[ 'title' ];
		$this->availability 				= $this->settings[ 'availability' ];
		$this->countries 					= $this->settings[ 'countries' ];
		$this->tax_status					= $this->settings[ 'tax_status' ];
		$this->shipping_system				= $this->settings[ 'shipping_system' ];
		$this->national_cost 				= $this->settings[ 'national_cost' ];
		$this->national_qty_override		= $this->settings[ 'national_qty_override' ];
		$this->international_cost 			= $this->settings[ 'international_cost' ];
		$this->international_qty_override	= $this->settings[ 'international_qty_override' ]; 
		$this->product_fee					= $this->settings[ 'product_fee' ];
		$this->shipping_policy				= $this->settings[ 'shipping_policy' ];
		$this->return_policy				= $this->settings[ 'return_policy' ];
		$this->product_shipping				= array(); 

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
     * Initialise Gateway Settings Form Fields
     *
     * @access public
     * @return void
     */
    public function init_form_fields() {
    	global $woocommerce;

    	$this->form_fields = array(
    		'enabled' => array(
					'title' 		=> __( 'Standalone Method', 'wcvendors-pro' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable WC Vendors Pro Shipping as a standalone shipping method', 'wcvendors-pro' ),
					'default' 		=> 'yes'
				),
			'title' => array(
					'title' 		=> __( 'Method Title', 'wcvendors-pro' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the title which the user sees during checkout.', 'wcvendors-pro' ),
					'default'		=> __( 'Vendor Shipping', 'wc_shipping_per_product' )
				),
			'tax_status' => array(
					'title' 		=> __( 'Tax Status', 'wcvendors-pro' ),
					'type' 			=> 'select',
					'class'         => 'wc-enhanced-select',
					'description' 	=> '',
					'default' 		=> 'none',
					'options'		=> array(
						'taxable' 	=> __( 'Taxable', 'wcvendors-pro' ),
						'none' 		=> __( 'None', 'wcvendors-pro' ),
					),
				),
			'shipping_system' => array(
					'title' 		=> __( 'Shipping System', 'wcvendors-pro' ),
					'type' 			=> 'select',
					'default' 		=> 'flat',
					'class'			=> 'wc-enhanced-select wcv-shipping-system',
					'description'	=> __( 'Your vendors have a simple flat rate for national and international shipping or a per country rate table. This can be overridden on a per vendor basis.', 'wcvendors-pro' ),
					'options'		=> WCVendors_Pro_Shipping_Controller::shipping_types(), 
				),
			'national_cost' => array(
					'title' 		=> __( 'Product Cost Nationally', 'wcvendors-pro' ),
					'type' 			=> 'text',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Default per product cost excluding tax for products on a per vendor level. e.g. 5.50.', 'wcvendors-pro' ),
					'default' 		=> '',
				),
			'national_free' => array(
					'title' 		=> __( 'Free National shipping', 'wcvendors-pro' ),
					'label' 		=> __( 'Enable store wide free national shipping', 'wcvendors-pro' ),
					'type' 			=> 'checkbox',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Check this to enable free national shipping', 'wcvendors-pro' ),
					'default' 		=> '',
				),
			'national_disable' => array(
					'title' 		=> __( 'Disable National shipping', 'wcvendors-pro' ),
					'label' 		=> __( 'Disable national shipping', 'wcvendors-pro' ),
					'type' 			=> 'checkbox',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Check this to disable national shipping', 'wcvendors-pro' ),
					'default' 		=> '',
				),
			'national_qty_override' => array(
					'title' 		=> __( 'Product Qty Override National', 'wcvendors-pro' ),
					'label' 		=> __( 'Charge once for national shipping, even if more than one is purchased.', 'wcvendors-pro' ),
					'type' 			=> 'checkbox',
					'description'	=> __( 'Disable the product qty in shipping calculations on a per product basis.', 'wcvendors-pro' ),
					'default' 		=> '',
				),
			'international_cost' => array(
					'title' 		=> __( 'Product Cost Internationally', 'wcvendors-pro' ),
					'type' 			=> 'text',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Default per product cost excluding tax for products on a per vendor level. e.g. 5.50.', 'wcvendors-pro' ),
					'default' 		=> '',
				),
			'international_free' => array(
					'title' 		=> __( 'Free International shipping', 'wcvendors-pro' ),
					'label' 		=> __( 'Enable store wide free international shipping', 'wcvendors-pro' ),
					'type' 			=> 'checkbox',
					'description'	=> __( 'Check this to enable free international shipping', 'wcvendors-pro' ),
					'default' 		=> '',
				),
			'international_disable' => array(
					'title' 		=> __( 'Disable International Shipping', 'wcvendors-pro' ),
					'label' 		=> __( 'Disable store wide international shipping', 'wcvendors-pro' ),
					'type' 			=> 'checkbox',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Check this to disable international shipping', 'wcvendors-pro' ),
					'default' 		=> '',
				),
			'international_qty_override' => array(
					'title' 		=> __( 'Product Qty Override International', 'wcvendors-pro' ),
					'label' 		=> __( 'Charge once for international shipping, even if more than one is purchased.', 'wcvendors-pro' ),
					'type' 			=> 'checkbox',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Disable the product qty in shipping calculations on a per product basis.', 'wcvendors-pro' ),
					'default' 		=> '',
				),
			'product_fee' => array(
					'title' 		=> __( 'Default product Handling Fee (per vendor)', 'wcvendors-pro' ),
					'type' 			=> 'text',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Product handling fee excluding tax. Fixed amount (5.00) or add a percentage sign for a percentage (5%). Leave blank to disable.', 'wcvendors-pro' ),
					'default'		=> '',
				),
			'shipping_policy' => array(
					'title' 		=> __( 'Default shipping policy', 'wcvendors-pro' ),
					'type' 			=> 'textarea',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Default shipping policy, displayed if a vendor has not set a shipping policy at store level.', 'wcvendors-pro' ),
					'default'		=> '',
				),
			'return_policy' => array(
					'title' 		=> __( 'Default return policy', 'wcvendors-pro' ),
					'type' 			=> 'textarea',
					'class'         => 'wcv-flat-rate',
					'description'	=> __( 'Default return policy, displayed if a vendor has not set a return policy at store level.', 'wcvendors-pro' ),
					'default'		=> '',
				),
			'availability' => array(
					'title' 		=> __( 'Method availability', 'wcvendors-pro' ),
					'type' 			=> 'select',
					'default' 		=> 'all',
					'class'			=> 'availability wc-enhanced-select wcv-flat-rate',
					'options'		=> array(
						'all' 		=> __('All allowed countries', 'wcvendors-pro' ),
						'specific' 	=> __('Specific Countries',  'wcvendors-pro' )
					)
				),
			'countries' => array(
					'title' 		=> __( 'Specific Countries', 'wcvendors-pro' ),
					'type' 			=> 'multiselect',
					'class'			=> 'chosen_select',
					'css'			=> 'width: 450px;',
					'default' 		=> '',
					'options'		=> $woocommerce->countries->get_allowed_countries()
				)
			);
    }

	/**
	 *  Calculate the shipping 
	 * 
	 * @since    1.1.0
	 * @param 	 mixed 	$package  the shipping package data 
	 */
	public function calculate_shipping( $package = array() ) {
		
		global $woocommerce;

    	$_tax 	= new WC_Tax();
		$taxes 	= array();
    	$shipping_cost 	= 0;

    	$settings = array(
    		'national_cost' 				=> $this->national_cost, 
    		'international_cost'			=> $this->international_cost, 
    		'product_fee'					=> $this->product_fee, 
    		'shipping_system'				=> $this->shipping_system, 
    		'national_qty_override' 		=> $this->national_qty_override, 
    		'international_qty_override'	=> $this->international_qty_override
    	); 

    	// This shipping method loops through products. 
    	if ( sizeof( $package['contents'] ) > 0 ) {

			foreach ( $package['contents'] as $item_id => $cart_item ) {

				if ( $cart_item['quantity'] > 0 ) {

					if ( $cart_item['data']->needs_shipping() ) {

						$item_shipping_cost = 0; 
						$rate = false; 
	
						// Currently uses the parent's shipping costs for now.
						// Eventually allow to set variation shipping costs by changing the product_id to the variation_id
						if ( $cart_item[ 'variation_id'] ) {	
							$rate = self::get_shipping_rate( $cart_item[ 'product_id'], $cart_item['data']->post->post_author, $package, $settings ); 
						} 					

						if ( ! $rate ) {
							$rate = self::get_shipping_rate( $cart_item[ 'product_id'], $cart_item['data']->post->post_author, $package, $settings ); 	
						}

						if ( $rate ) {

							$qty = ( $rate->qty_override === 'yes' ) ? 1 : $cart_item[ 'quantity' ];

							$item_shipping_cost += $rate->fee * $qty; 

							// Product handling fee. 
							$product_fee = $this->get_fee( $rate->product_fee, $item_shipping_cost ) * $qty; 
							$item_shipping_cost += $product_fee;
					
							$item_taxes = 0; 

							if ( $this->tax_status === 'taxable' &&  wc_tax_enabled() ) {
							
								$tax_rates		= $_tax->get_shipping_tax_rates( $cart_item['data']->get_tax_class() );
								$item_taxes 	= $_tax->calc_shipping_tax( $item_shipping_cost, $tax_rates );

								// Add up the item taxes
								foreach ( array_keys( $taxes + $item_taxes ) as $key ) {
									$taxes[ $key ] = ( isset( $item_taxes[ $key ] ) ? $item_taxes[ $key ] : 0 ) + ( isset( $taxes[ $key ] ) ? $taxes[ $key ] : 0);
								}
							}

							$item_shipping = array(
								'shipping_total'	=> $item_shipping_cost, 
								'shipping_tax'		=> $item_taxes
							); 

							$shipping_cost += $item_shipping_cost; 

						} else {
							// No fees found for this product. 
							return;
						}
					}
				}
			}
		}
	
		// // Add rate
		$this->add_rate( array(
			'id' 		=> $this->id,
			'label' 	=> $this->title,
			'cost' 		=> ( float ) $shipping_cost,
			'taxes' 	=> $taxes,  // We calc tax in the method
			'package' 	=> $package
		));

	} // calculate_shipping() 
	
	/**
	 *  Get the shipping rate 
	 * 
	 * @since    1.1.0
	 * @param 	 object 	$product  the product to get the rate for 
 	 * @param 	 mixed 		$package  the shipping package data 
	 */
	public static function get_shipping_rate( $product_id, $vendor_id, $package, $settings ) {

		$customer_country 		= strtolower( $package[ 'destination' ][ 'country' ] );
		$customer_state			= strtolower( $package[ 'destination' ][ 'state' ] ); 
		$store_shipping_type	= get_user_meta( $vendor_id, '_wcv_shipping_type', true ); 
		$store_rates			= get_user_meta( $vendor_id, '_wcv_shipping', true ); 
		$store_country 			= ( $store_rates && $store_rates['shipping_from'] == 'other' ) ? strtolower( $store_rates['shipping_address']['country'] ) : strtolower( get_user_meta( $vendor_id, '_wcv_store_country', true ) ); 
		$store_state 			= ( $store_rates && $store_rates['shipping_from'] == 'other' ) ? strtolower( $store_rates['shipping_address']['state'] ) : strtolower( get_user_meta( $vendor_id, '_wcv_store_state', true ) ); 
		$shipping_rate 			= new stdClass(); 
		$product_rates 			= get_post_meta( $product_id, '_wcv_shipping_details', true );  

		$shipping_rate->product_id = $product_id; 

		// Check if the store has a shipping type override. 
		$shipping_type = ( $store_shipping_type != '' ) ? $store_shipping_type : $settings[ 'shipping_system' ]; 

		// Get default country for admin.  
		if ( ! WCV_Vendors::is_vendor( $vendor_id ) ) $store_country = WC()->countries->get_base_country(); 

		if ( $shipping_type == 'flat' ) {

			if ( $customer_country == $store_country ) { 

				// if ( ( is_array( $product_rates ) && array_key_exists( 'national_disable', $product_rates ) && 'yes' === $product_rates[ 'national_disable'] ) || ( is_array( $store_rates ) && array_key_exists('national_disable', $store_rates ) && 'yes' ===  $store_rates['national_disable'] ) ) { 
				// 	return $shipping_rate = false;
				// }

				if ( ( is_array( $product_rates ) && array_key_exists( 'national_disable', $product_rates ) && 'yes' === $product_rates[ 'national_disable'] ) ) { 
					
					return $shipping_rate = false;

				} elseif ( is_array( $product_rates ) && ( strlen( $product_rates['national_disable'] ) === 0 && ( strlen( trim( $product_rates['national'] ) ) > 0 || strlen( trim( $product_rates[ 'national_free' ] ) ) > 0 ) ) ) {
					// Is free shipping enabled ?
					if ( 'yes' === $product_rates[ 'national_free' ] ){ 
						$shipping_rate->fee 			= 0; 
					} else { 
						$shipping_rate->fee 			= $product_rates[ 'national' ];  
					}
					$shipping_rate->product_fee 	= $product_rates[ 'handling_fee' ]; 
					$shipping_rate->qty_override 	= $product_rates[ 'national_qty_override' ]; 

					if ( ( is_array( $product_rates ) && array_key_exists( 'national_disable', $product_rates ) && 'yes' === $product_rates[ 'national_disable'] ) ) { 
						return $shipping_rate = false;
					}

				} elseif ( ( is_array( $store_rates ) && array_key_exists('national_disable', $store_rates ) && 'yes' ===  $store_rates['national_disable'] ) ) { 

					return $shipping_rate = false;

				} elseif( is_array( $store_rates ) && ( strlen( $store_rates[ 'national_disable' ] ) === 0 && ( strlen( trim( $store_rates['national'] ) ) > 0 || strlen( $store_rates[ 'national_free' ] ) > 0 ) ) ) {

					// Is free shipping enabled at store level?
					if ( 'yes' === $store_rates[ 'national_free' ] ){ 
						$shipping_rate->fee 			= 0; 
					} else { 
						$shipping_rate->fee 			= $store_rates['national']; 
					}
					$shipping_rate->product_fee 	= $store_rates[ 'product_handling_fee' ];
					$shipping_rate->qty_override 	= $store_rates[ 'national_qty_override' ]; 
				} elseif ( ( float ) trim( $settings[ 'national_cost' ] ) > 0 ) { 
					$shipping_rate->fee 			= $settings[ 'national_cost' ]; 
					$shipping_rate->product_fee 	= $settings[ 'product_fee']; 
					$shipping_rate->qty_override 	= $settings[ 'national_qty_override']; 
				} else { 
						$shipping_rate = false; 
				}

			} else { 

				// International shipping 
				if ( ( is_array( $product_rates ) && array_key_exists( 'international_disable', $product_rates ) && 'yes' === $product_rates['international_disable'] ) ) {
					
					return $shipping_rate = false;
				
				} elseif ( is_array( $product_rates ) && ( strlen( $product_rates['international_disable'] ) === 0 && ( strlen( trim( $product_rates['international'] ) ) > 0 || strlen( $product_rates[ 'international_free' ] ) > 0 ) ) ) {
					// Is free shipping enabled ?
					if ( 'yes' === $product_rates[ 'international_free' ] ){  
						$shipping_rate->fee 			= 0; 
					} else { 
						$shipping_rate->fee 			= $product_rates[ 'international' ]; 
					}
					$shipping_rate->product_fee 	= $product_rates[ 'handling_fee' ];
					$shipping_rate->qty_override 	= $product_rates[ 'international_qty_override' ]; 

				} elseif ( is_array( $store_rates ) && array_key_exists( 'international_disable', $store_rates ) &&  'yes' ===  $store_rates['international_disable'] )  { 

					return $shipping_rate = false;

				} elseif( is_array( $store_rates ) && ( strlen( $store_rates['international_disable'] ) === 0 && ( strlen( trim( $store_rates['international'] ) ) > 0 || strlen( $store_rates[ 'international_free' ] ) > 0 ) ) ) {

					if ( 'yes' === $store_rates[ 'international_free' ] ){ 
						$shipping_rate->fee 			= 0; 
					} else { 
						$shipping_rate->fee 			= $store_rates[ 'international' ];

					}	
					$shipping_rate->product_fee 	= $store_rates[ 'product_handling_fee' ]; 
					$shipping_rate->qty_override 	= $store_rates[ 'international_qty_override']; 
				} elseif ( ( float ) trim( $settings[ 'international_cost' ] ) > 0 ) { 
					$shipping_rate->fee 			= $settings[ 'international_cost' ]; 
					$shipping_rate->product_fee 	= $settings[ 'product_fee' ]; 
					$shipping_rate->qty_override 	= $settings[ 'international_qty_override']; 
				} else { 
						$shipping_rate = false; 
				}
			} 

		} else { 

			$product_shipping_table = get_post_meta( $product_id, '_wcv_shipping_rates',  true );
			$store_shipping_table = get_user_meta( $vendor_id, '_wcv_shipping_rates',  true ); 

			// Check to see if the product has any rates set.
			if ( is_array( $product_shipping_table ) ) {  

				$shipping_rate->product_fee = ( is_array( $product_rates ) && array_key_exists( 'handling_fee', $product_rates ) ) ? $product_rates['handling_fee'] : 0; 
				$shipping_rate->qty_override = ''; 

				foreach ( $product_shipping_table as $rate ) {

					//  Country and state match 
					if ( strtolower( $customer_country ) === strtolower( $rate[ 'country' ] ) && strtolower( $customer_state ) === strtolower( $rate[ 'state' ] ) ) { 
						$shipping_rate->fee = $rate[ 'fee' ]; 
						return $shipping_rate; 
					}

					// Country and state is any 
					if ( strtolower( $customer_country ) === strtolower( $rate[ 'country' ] ) && empty( $rate[ 'state' ] ) ) { 
						$shipping_rate->fee = $rate[ 'fee' ]; 
						return $shipping_rate; 
					}

					// Country and state is any 
					if ( $rate[ 'country' ] === '' && $rate[ 'state' ] === '' ) { 
						$shipping_rate->fee = $rate[ 'fee' ]; 
						return $shipping_rate; 
					} 

				}

			}

			// Check to see if the store has any rates set. 
			if ( is_array( $store_shipping_table ) ){ 
				
				$shipping_rate->product_fee = ( is_array( $store_rates ) && array_key_exists( 'product_handling_fee', $store_rates ) ) ?  $store_rates[ 'product_handling_fee' ] : 0;
				$shipping_rate->qty_override = 0; 

				foreach ( $store_shipping_table as $rate ) {

					// Country and state 
					if ( strtolower( $customer_country ) == strtolower( $rate[ 'country' ] ) && strtolower( $customer_state ) == strtolower( $rate[ 'state' ] ) ) { 
						$shipping_rate->fee = $rate[ 'fee' ]; 
						return $shipping_rate; 
					}

					// Country and state is any 
					if ( strtolower( $customer_country ) == strtolower( $rate[ 'country' ] ) && empty( $rate[ 'state' ] )  ) { 
						$shipping_rate->fee = $rate[ 'fee' ]; 
						return $shipping_rate;  
					}

					// Country is any and state is any 
					if ( $rate[ 'country' ] == '' && $rate[ 'state' ] == '' ) { 
						$shipping_rate->fee = $rate[ 'fee' ]; 
						return $shipping_rate; 
					}

				}

			}

			// Attempting to use default rates. 
			$shipping_rate->product_fee = $settings[ 'product_fee']; 
			$shipping_rate->qty_override = 0; 

			// Default shipping applies as no shipping found above. 
			if ( $customer_country == $store_country ) { 

				// If the national default cost is set then use it. 
				if ( $settings[ 'national_cost' ] != '' ) { 
					$shipping_rate->fee 		= $settings[ 'national_cost' ]; 

				} else { 
					$shipping_rate = false; 
				}

			} else { 

				// If the international default cost is set then use it. 
				if ( $settings[ 'international_cost' ] != '' ) { 
					$shipping_rate->fee 		= $settings[ 'international_cost' ]; 
				} else { 
					$shipping_rate = false; 
				}	
			}


		}

		return $shipping_rate; 

	} // get_shipping_rate() 

	/**
	 * Class logger so that we can keep our debug and logging information cleaner 
	 *
	 * @since 1.3.4
	 * @access public
	 * 
	 * @param mixed - $data the data to go to the error log could be string, array or object
	 */
	public static function log( $data ){ 

		if ( is_array( $data ) || is_object( $data ) ) { 
			error_log( print_r( $data, true ) ); 
		} else { 
			error_log( $data );
		}

	} // log() 
}
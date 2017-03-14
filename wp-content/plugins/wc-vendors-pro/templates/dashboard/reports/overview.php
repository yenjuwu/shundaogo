<?php
/**
 * The template for displaying the vendor store information including total sales, orders, products and commission
 *
 * Override this template by copying it to yourtheme/wc-vendors/dashboard/report
 *
 * @package    WCVendors_Pro
 * @version    1.2.3
 */
?>


<div class="wcv_dashboard_datepicker wcv-cols-group"> 
	
	<div class="all-100">
	<hr />
	<form method="post" action="" class="wcv-form"> 
		<?php $store_report->date_range_form(); ?>
	</form>
	</div>
</div>

<div class="wcv_dashboard_overview wcv-cols-group wcv-horizontal-gutters"> 

	<div class="xlarge-50 large-50 medium-100 small-100 tiny-100">
		<h3><?php _e( 'Commission Due', 'wcvendors-pro'); ?></h3>
		<table role="grid" class="wcvendors-table wcvendors-table-recent_order wcv-table">

	  	<tbody>
		    <tr>
		      <td><?php _e( 'Products', 'wcvendors-pro'); ?></td>
		      <td><?php echo woocommerce_price( $store_report->commission_due ) ;?></td>
		    </tr>
		    <tr>
		      <td><?php _e( 'Shipping', 'wcvendors-pro'); ?></td>
		      <td><?php echo woocommerce_price( $store_report->commission_shipping_due ) ;?></td>
		    </tr>
		    <tr>
		      <td><strong><?php _e( 'Totals', 'wcvendors-pro'); ?></strong></td>
		      <td><?php echo woocommerce_price( $store_report->commission_due + $store_report->commission_shipping_due ) ;?></td>
		    </tr>
	  	</tbody>

		</table>
	</div>

	<div class="xlarge-50 large-50 medium-100 small-100 tiny-100">
		<h3><?php _e( 'Commission Paid', 'wcvendors-pro'); ?></h3>
		<table role="grid" class="wcvendors-table wcvendors-table-recent_order wcv-table">
	  	<tbody>
		    <tr>
		      <td><?php _e( 'Products', 'wcvendors-pro'); ?></td>
		      <td><?php echo woocommerce_price( $store_report->commission_paid ) ;?></td>
		    </tr>
		    <tr>
		      <td><?php _e( 'Shipping', 'wcvendors-pro'); ?></td>
		      <td><?php echo woocommerce_price( $store_report->commission_shipping_paid ) ;?></td>
		    </tr>
		    <tr>
		      <td><strong><?php _e( 'Totals', 'wcvendors-pro'); ?></strong></td>
		      <td><?php echo woocommerce_price( $store_report->commission_paid + $store_report->commission_shipping_paid ) ;?></td>
		    </tr>
	  	</tbody>

		</table>
	</div>

</div>

<?php if ( 'something' == 'somethingelse' ) : ?> 

<div class="wcv_dashboard_overview wcv-cols-group"> 

	  <div class="xlarge-25 large-25 medium-50 small-50 tiny-100 stats">
        	<span><?php _e( 'Orders', 'wcvendors-pro'); ?></span>
        	<h3><?php echo $store_report->total_orders; ?></h3>
	        		<!-- <i class="fa fa-shopping-cart fa-3x orders"></i> -->
	  </div>
	  <div class="xlarge-25 large-25 medium-50 small-50 tiny-100 stats">
            <span><?php _e( 'Total Products Sold', 'wcvendors-pro'); ?></span>
            <h3><?php echo $store_report->total_products_sold; ?></h3>
	  </div>
	  <div class="xlarge-25 large-25 medium-50 small-50 tiny-100 stats">
	   	  <span><?php _e( 'Commission Owed', 'wcvendors-pro'); ?></span>
	      <h3><?php echo woocommerce_price( $store_report->commission_due ) ;?></h3>
	  </div>
	  <div class="xlarge-25 large-25 medium-50 small-50 tiny-100 stats">
	   	 	<span><?php _e( 'Commission Paid', 'wcvendors-pro'); ?></span>
	        <h3><?php echo woocommerce_price( $store_report->commission_paid ); ?></h3>
	  </div>

</div>

<?php endif; ?>
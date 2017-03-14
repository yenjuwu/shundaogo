<?php

/**
 * The admin country rate shipping for user and product pages
 *
 * This file is used to display the shipping type override in the edit user screen
 *
 * @link       http://www.wcvendors.com
 * @since      1.3.3 
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/admin/partials/store
 */ 
?>

<!-- Country Rate Table -->
<?php do_action( 'wcv_admin_before_country_rate_shipping', $user ); ?>
<div class="wcv-country_rate_shipping wcv-shipping-rates wcv-shipping-country">
<?php if ( $screen->id == 'user-edit' ) : ?>
<h3><?php _e( 'Country Rate Shipping', 'wcvendors-pro' ); ?></h3>
<?php endif; ?>

<div id="shipping"> 
		<div class="form-field wcv_shipping_rates">
			<table>
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						<th align="left"><?php _e( 'Country', 'wcvendors-pro' ); ?></th>
						<th align="left"><?php _e( 'State', 'wcvendors-pro' ); ?> </th>
						<th align="left"><?php _e( 'Shipping Fee', 'wcvendors-pro' ); ?></th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>

					<?php if ( $shipping_rates ) : ?>
						<?php foreach ( $shipping_rates as $rate ) : ?>
						<tr>
							<td class="sort"><i class="fa fa-sort"></i></td>
							<td class="country"><input type="text" placeholder="<?php _e( "Country", 'wcvendors-pro' ); ?>" name="_wcv_shipping_countries[]" value="<?php echo esc_attr( $rate['country'] ); ?>" /></td>
							<td class="state"><input type="text" placeholder="<?php _e( "State", 'wcvendors-pro' ); ?>" name="_wcv_shipping_states[]" value="<?php echo esc_attr( $rate['state'] ); ?>" /></td>
							<td class="fee"><input type="text" data-rules="decimal"  data-error="<?php _e( 'This should be a number.', 'wcvendors-pro' ); ?>" placeholder="<?php _e( "Fee", 'wcvendors-pro' ); ?>" name="_wcv_shipping_fees[]" value="<?php echo esc_attr( $rate['fee'] ); ?>" /></td>
							<td width="1%"><a href="#" class="delete"><i class="fa fa-times"></i></a></td>
						</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="5">
							<br /> <br />
							<a href="#" class="button insert" style="float: left;" data-row="
							<?php
								$rate = array(
									'country'	=> '',
									'state' 	=> '', 
									'fee' 		=> '',
								);
								$file_data_row = '<tr>
									<td class="sort"><i class="fa fa-sort"></i></td>
									<td class="country"><input type="text" placeholder="'. __( "Country", "wcvendors-pro" ) .'" name="_wcv_shipping_countries[]" value="' .esc_attr( $rate["country"] ). '" /></td>
									<td class="state"><input type="text" placeholder="'. __( "State", "wcvendors-pro" ). '" name="_wcv_shipping_states[]" value="'. esc_attr( $rate["state"] ) .'" /></td>
									<td class="fee"><input type="text" data-error="'.__( "This should be a number.", "wcvendors-pro" ) .'" data-rules="decimal" placeholder="'. __( "Fee", "wcvendors-pro" ). '" name="_wcv_shipping_fees[]" value="'. esc_attr( $rate["fee"] ) .'" /></td>
									<td width="1%"><a href="#" class="delete"><i class="fa fa-times"></i></a></td>
								</tr>';

								echo esc_attr( $file_data_row );
							?>">
						<?php _e( 'Add Rate', 'wcvendors-pro' ); ?></a><br /><br /><?php echo $helper_text; ?>
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<?php do_action( 'wcv_admin_after_country_rate_shipping', $user ); ?>
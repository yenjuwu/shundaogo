<?php

/**
 * Product Table Main Actions 
 *
 * This file is used to add the table actions before and after a table
 *
 * @link       http://www.wcvendors.com
 * @since      1.2.4
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/partials/product
 */ 
?>

<div class="wcv_dashboard_table_header wcv-cols-group"> 
	<div class="all-50">
		<a href="<?php echo $add_url; ?>" class="wcv-button button"><?php echo __('Add Product', 'wcvendors-pro' ); ?></a>
	</div>
	<div class="all-50">
	<?php 
		echo $pagination_wrapper[ 'wrapper_start' ];
		echo paginate_links( apply_filters( 'wcv_product_pagination_args', array(  
			    'base' 			=> get_pagenum_link( ) . '%_%',  
			    'format' 		=> 'page/%#%/',  
			    'current' 		=> ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1,  
			    'total' 		=> $this->max_num_pages,  
			    'prev_next'    	=> false,  
			    'type'         	=> 'list',  
				), ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1, $this->max_num_pages
			) );
		echo $pagination_wrapper[ 'wrapper_end' ];
		?>
	</div>
</div>
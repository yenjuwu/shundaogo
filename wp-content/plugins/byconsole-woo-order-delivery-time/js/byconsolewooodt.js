//synchornization of widget radior selection to caheout field radio selection
jQuery(document).ready(function () {

        /////////////ADDED BY JAY 3/30/2017/////////////////
	if(((window.location.pathname).indexOf("cart") > 0) || ((window.location.pathname).indexOf("checkout") > 0)){
            jQuery('#byconsolewooodt_checkout_field').prepend('<label for="delivery_datetime" class="">Delivery Time & Date <abbr class="required" title="required">*</abbr></label>');

           if(jQuery('div').hasClass('woocommerce-shipping-fields')){
                document.getElementsByClassName("woocommerce-shipping-fields")[0].appendChild(document.getElementById("byconsolewooodt_checkout_field"));
              }
          if(jQuery('#byconsolewooodt_delivery_type_field').length){
                document.getElementById("byconsolewooodt_delivery_type_field").innerHTML = "";
            }
           var monthNames = ["January", "February", "March", "April", "May", "June",
               "July", "August", "September", "October", "November", "December"
           ];
           var date = new Date();
           var datePlusHour = new Date(date);
           var remainder = 15 - date.getMinutes() % 15;
           datePlusHour.setMinutes(date.getMinutes() + remainder + 60);
           var dateString = monthNames[date.getMonth()] + " " + date.getDate() + ", " + date.getFullYear();
           var time = datePlusHour.toLocaleString('en-US', { hour: 'numeric', minute:'numeric', hour12: true });
           if(jQuery('#byconsolewooodt_delivery_date').length){
            document.getElementById("byconsolewooodt_delivery_date").value = dateString;
            document.getElementById("byconsolewooodt_delivery_time").value = time;
          }
           jQuery('#byconsolewooodt_delivery_time').timepicker({
             'minTime': time,
             'maxTime': '11:00pm'
           });
           jQuery('#byconsolewooodt_delivery_time').addClass('ui-timepicker-input');
          }
	////////////////////////////////////////////////////

		jQuery('input[name="byconsolewooodt_delivery_type"]').on('click',function(){

        // Get the element index , which one we click on
        var indx = jQuery(this).index('input[name="byconsolewooodt_delivery_type"]');

        // Trigger a click on the same index in the second radio set

        jQuery('input[name="byconsolewooodt_widget_type_field"]')[indx].click();

		//save the widget form
		jQuery('input[name="byconsolewooodt_widget_submit"]').click();
    });

	//to avoid wrong parameters for time drop-down when the delivery type radio selection has changed
	jQuery('input[name="byconsolewooodt_widget_type_field"]').on('click',function(){
		//remove the value relected for previous option
		jQuery('input[name="byconsolewooodt_widget_time_field"]').val('');
		//reload the widget to get new values for time drop-down from admin settings
		jQuery('input[name="byconsolewooodt_widget_submit"]').click();
		});

	//synchornize check out date time field value with widget date time field value
		jQuery('input[name="byconsolewooodt_widget_date_field"]').on('change',function(){
		jQuery('input[name="byconsolewooodt_delivery_date"]').val(jQuery(this).val());
		});
		jQuery('input[name="byconsolewooodt_widget_time_field"]').on('change',function(){
		jQuery('input[name="byconsolewooodt_delivery_time"]').val(jQuery(this).val());
		});
		jQuery('input[name="byconsolewooodt_delivery_date"]').on('change',function(){
		jQuery('input[name="byconsolewooodt_widget_date_field"]').val(jQuery(this).val());
		});
		jQuery('input[name="byconsolewooodt_delivery_time"]').on('change',function(){
		jQuery('input[name="byconsolewooodt_widget_time_field"]').val(jQuery(this).val());
		});

		jQuery('.byconsolewooodt_widget_time_field').on('click',function(){
			if(! jQuery('.byconsolewooodt_widget_time_field').hasClass('ui-timepicker-input')){
				alert("Please select date first");
				}
			});
		// jQuery('#byconsolewooodt_delivery_time').on('click',function(){
		// 	if(! jQuery('#byconsolewooodt_delivery_time').hasClass('ui-timepicker-input')){
		// 		alert("Please select checkout date first");
		// 		}
		// 	})


});

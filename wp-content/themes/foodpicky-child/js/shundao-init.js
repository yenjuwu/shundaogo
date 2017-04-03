;jQuery(document).ready(function(){

    //bind the keypress
    var regex = /(\d+) ((\w+[ ,])+ ){2}([a-zA-Z]){2} (\d){5}/;

    jQuery('input#delivery_address').keydown( _.debounce( function(e){
        var address = e.currentTarget.value;
        if((currentMatch = regex.exec(address)) !==null && currentMatch.length>0 ){
            // find a way to return vendor address
            jQuery(this).removeClass("error").addClass("success");
            addDeliveryChargeProcessing();
            var vendorAddress=jQuery('input#vendor_address').val();
            getDistanceMatrix(address,vendorAddress);
        }else{
            jQuery(this).removeClass("success").addClass("error");
        }
    }, 800 ) );
    jQuery('input#delivery_address').bind("paste",function(e){
        var address = e.originalEvent.clipboardData.getData('text');
        if((currentMatch = regex.exec(address)) !==null && currentMatch.length>0 ){
            // find a way to return vendor address
            jQuery(this).removeClass("error").addClass("success");
            var vendorAddress=jQuery('input#vendor_address').val();
            getDistanceMatrix(address,vendorAddress);
        }else{
            jQuery(this).removeClass("success").addClass("error");
        }
    });
    jQuery('input#delivery_address').on('change keyup copy paste cut', function() {
        if(jQuery('input#delivery_address').val()===''){
            removeAllVisualStatus();
        }
    });
    
    // handle store close for product
    for(var i = 0; i < jQuery('.is-open').length; i++){
        if(jQuery('.is-open').eq(i).text() === 'Closed'){
            jQuery('.add_to_cart_button').text('Closed');
            jQuery('.add_to_cart_button').replaceWith('<a class="button">Closed</a>');
          }
     }
     
     //handle restaurant
     if(jQuery('.is-open').length){
        if(jQuery('.is-open').text() === "Closed"){
            jQuery('.single_add_to_cart_button').prop('disabled',true);
            jQuery('.single_add_to_cart_button').text('Closed');
       }
    }
    //search
    if(jQuery('div').hasClass('geo-location')){
 jQuery('.geo-location').find('input[name="location"]').keyup(function() {
   var address;
   if(jQuery('.geo-location').find('input[name="location"]').val().toLowerCase().indexOf('ga') > 0){
     address = jQuery('.geo-location').find('input[name="location"]').val();
   }
   else{
     address = jQuery('.geo-location').find('input[name="location"]').val() + ' GA';
   }

   jQuery.ajax({
       type: "GET",
       dataType: "json",
       url: "http://maps.googleapis.com/maps/api/geocode/json",
       data: {'address': address,'sensor':false},
       success: function(data){
           if(data.results.length){
               jQuery('.geo-location').find('input[name="location"]').val(jQuery('.geo-location').find('input[name="location"]').val());
               jQuery('.geo-location').find('input[name="latitude"]').val(data.results[0].geometry.location.lat);
               jQuery('.geo-location').find('input[name="longitude"]').val(data.results[0].geometry.location.lng);
            
           } else{
             jQuery('#latitude').val('invalid address');
             jQuery('#longitude').val('invalid address');
             console.log(jQuery('#latitude').val('invalid address'));
             console.log(jQuery('#longitude').val('invalid address'));
          }
       }
   });
 });
}
    if(jQuery('.sidebar-container').length === 3){
                jQuery('.sidebar-container').eq(1).remove();
                }
// end of search


    //move tip container
    /*
    jQuery('.delivery-tip-woocommerce').remove();
jQuery('.delivery-tip-woocommerce1').remove();
jQuery('.woocommerce-billing-fields').append('<div class="delivery-tip-woocommerce1" style=""><form action="" method="post"><div class="tip-ex"><label for="tip-please" class="">Add Tip</label><select onchange="setTipValue(this.value)"><option value="0">Select</option><option value="3.50">10% ($3.50)</option><option value="5.25">15% ($5.25)</option><option value="6.29">18% ($6.29)</option><option value="6.99">20% ($6.99)</option></select><script>function setTipValue(tipsug) {document.getElementById("value_deltip").value = tipsug;}</script></center>Amount: <input style="line-height:1.5em;text-align: center;width: 47%;" type="text" name="value_deltip" class="input-text-deltip" placeholder="Tip Amount" id="value_deltip" value=""><!-- value for entry box (original) --><p class="form-row form-row-last" style="float:left;width: 59%;margin-top: 15px;"><input type="submit" class="button" id="submit_deltip" name="apply_amount" value="Add Tip"></p><div class="clear"></div></form></div>');
   if(jQuery('a').hasClass('empty-wpmenucart-visible')){
    jQuery('.empty-wpmenucart-visible').attr('href','/restaurants/');
}
if(jQuery('a').hasClass('wc-backward')){
    if((jQuery('.wc-backward').text()).indexOf('Return To Shop') >= 0){
       jQuery('.wc-backward').attr('href','/restaurants/');
 }
} */
});
function removeAllVisualStatus(){
    jQuery('input#delivery_address').removeClass("success").removeClass("error");
    removeDeliveryChargeProcessing();
    jQuery("div#message-container").html("");
}
function addDeliveryChargeProcessing(){
    if(!jQuery("input#delivery_address").hasClass("processing")){
        jQuery("input#delivery_address").addClass("processing");
    }
}
function removeDeliveryChargeProcessing(){
        if(jQuery("input#delivery_address").hasClass("processing")){
            jQuery("input#delivery_address").removeClass("processing");
        }
}

function getDistanceMatrix(fromAddress, destAddress){
     
	var service = new google.maps.DistanceMatrixService();
	service.getDistanceMatrix(
	  {
		origins: [fromAddress],
		destinations: [destAddress],
		travelMode: 'DRIVING',
		unitSystem: google.maps.UnitSystem.IMPERIAL,
		avoidHighways: false,
		avoidTolls: false,
	  }, deliveryCostCallback);
}
function deliveryCostCallback(response, status) {
        var messageContainer = jQuery("div#message-container");
	if(status === 'OK') {
		//alert("distance" + response.rows[0].elements[0].distance.text);
		var dist = response.rows[0].elements[0].distance.text;
                if(parseFloat(dist) > 30){
                    messageContainer.html("Sorry, You are out of our delivery zoom. Please select a restaurant that is closer to you");
                }else{
                    var costPerMile = 0.99;
                    var distVal = convertToValue(dist); 
                    var deliveryCost = distVal * costPerMile ;
                    var costAfterFixed =deliveryCost.toFixed(2)
                    messageContainer.html("Distance: " + dist + ", Delivery cost $" + costAfterFixed);
                    // if it isn't out of our zone. we need to add cost to the total
                    jQuery.ajax({
                        url:woocommerce_params.ajax_url,
                        method:"POST",
                        dataType:"json",
                        data:{'delivery_cost':costAfterFixed,'action':'add_delivery_cost'},
                        success:function(data,status){
                            if(status==="success" && data.status===1){
                                // meaning that this is success
                                jQuery("body").trigger("update_checkout");
                                jQuery("#message-container").html(data.message);
                                removeDeliveryChargeProcessing();
                            }else{
                                jQuery("#message-container").html(data.message);
                                removeDeliveryChargeProcessing();
                            }
                        }
                    });
                }
	}else{
            //there is an error
            messageContainer.html("There seems to be an issue calculating your delivery cost");
            removeDeliveryChargeProcessing();
        }
}

function convertToValue(inputDistance) {
	var arr = inputDistance.split(" ");
	var dist = arr[0];
	dist = dist.replace(",","");
	return dist;
}



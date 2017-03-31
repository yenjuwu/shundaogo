;jQuery(document).ready(function(){

    //bind the keypress
    var regex = /(\d+) ((\w+[ ,])+ ){2}([a-zA-Z]){2} (\d){5}/;

    jQuery('input#delivery_address').keypress( _.debounce( function(e){
        var address = e.currentTarget.value;
        if((currentMatch = regex.exec(address)) !==null && currentMatch.length>0 ){
            // find a way to return vendor address
            jQuery(this).removeClass("error").addClass("success");
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
});

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
                        url:ajaxurl,
                        method:"POST",
                        dataType:"json",
                        data:{'delivery_cost':costAfterFixed,'action':'add_delivery_cost'},
                        success:function(data,status){
                            if(status==="success" && data.status===1){
                                // meaning that this is success
                                jQuery("body").trigger("update_checkout");
                                jQuery("#message-container").html(data.message);
                            }else{
                                jQuery("#message-container").html(data.message);
                            }
                        }
                    });
                }
	}else{
            //there is an error
            messageContainer.html("There seems to be an issue calculating your delivery cost");
        }
}

function convertToValue(inputDistance) {
	var arr = inputDistance.split(" ");
	var dist = arr[0];
	dist = dist.replace(",","");
	return dist;
}



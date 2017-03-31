;jQuery(document).ready(function(){

    jQuery('#deliveryCost').html("$money: 4.99");
    //bind the keypress
    var regex = /(\d+) ((\w+[ ,])+ ){2}([a-zA-Z]){2} (\d){5}/;
<<<<<<< HEAD
    jQuery('input#delivery_address').keypress( _.debounce( function(e){        
=======

    jQuery('#billing_postcode_field').keypress(_.debounce(function(e){
      //This is just used to show random value for demo.
      var cost = Number(Math.random() * 10 * .99).toFixed(2);
      getDistanceMatrix();
    },500));

    jQuery('input#delivery_address').keypress( _.debounce( function(e){
>>>>>>> 6f1803bcb32dcd643ff6ed0fc261b4c2ceb6ad28
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
<<<<<<< HEAD
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

=======

   if(!navigator.geolocation){
       //need geolocation to determine location
       console.log("no geolocation");
        return;
   }else{
       navigator.geolocation.getCurrentPosition(shundao_success,shundao_error);
   }

});

function showAlert(msg){
  alert(msg);
}

function getDistanceMatrix(){
  jQuery('#deliveryCost').html("about to get distance");

  var fromAddress = jQuery("#src_address").val();
  var destAddress = jQuery("#billing_address_1_field").val() + ", " + 
    jQuery("#billing_city_field").val() + ", " + jQuery("#billing_state option:selected").text() + ", " + jQuery("#billing_postcode_field"); 
  
  //use this a temp restaurant for now, can change to use the value of the currect restaurant
  var fogoDunwoody = '4671 Ashford Dunwoody Rd NE, Dunwoody, GA 30346';
  var sampleAddress = '820 Ralph McGill Blvd NE, Atlanta, GA 30306';
  
  jQuery('#deliveryCost').html("Dest address " + destAddress);
  //$("#rElement").html("from: " + fromAddress +", to:" + destAddress);
  var service = new google.maps.DistanceMatrixService();
  service.getDistanceMatrix (
  {
    origins: [fogoDunwoody],
    destinations: [destAddress],
    travelMode: 'DRIVING',
    unitSystem: google.maps.UnitSystem.METRIC,
    avoidHighways: false,
    avoidTolls: false,
    }, myCallback);

}


function myCallback(response, status) {
  jQuery("deliveryCost").html("Service return ");
  //jQuery("#pElement").html(response);
  if(status == 'OK') {
    //alert("distance" + response.rows[0].elements[0].distance.text);
    var dist = response.rows[0].elements[0].distance.text;
    var costPerMile = .99;
    var distVal = convertToValue(dist); 
    var deliveryCost = (distVal /1.6) * costPerMile ;
    jQuery("deliveryCost").html("$" + deliveryCost);
    showAlert("get distance success");

  } else {
      jQuery("Invalid address");
      showAlert('Invalid address');
  }

}

function convertToValue(inputDistance) {
  var arr = inputDistance.split(" ");
  var dist = arr[0];
  dist = dist.replace(",","");
  return dist;
}
>>>>>>> 6f1803bcb32dcd643ff6ed0fc261b4c2ceb6ad28


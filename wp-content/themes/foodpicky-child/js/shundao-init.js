;jQuery(document).ready(function(){
    var address_field=jQuery('input#delivery_address');
    if(address_field.length>0){
        initAutocomplete();
        //bind the keypress
        //var regex = /(\d+) ((\w+[ ,])+ ){2}([a-zA-Z]){2} (\d){5}/;
        jQuery('input#delivery_address').focus(geolocate);
    }
    /*
    jQuery('input#delivery_address').keydown( _.debounce( function(e){
        var address = e.currentTarget.value;
        if((currentMatch = regex.exec(address)) !==null && currentMatch.length>0 ){
            // find a way to return vendor address
            jQuery(this).removeClass("error").addClass("success");
            addDeliveryChargeProcessing();
            var vendorAddress=jQuery('input#vendor_address').val();
            getDistanceMatrix(address,vendorAddress);
        }else{
            jQuery("div#message-container").text("* 格式为：number(空格)street(,)(空格)city(,)(空格) GA(空格)zipcode");
            jQuery(this).removeClass("success").addClass("error");
        }
    }, 800 ) );
    */
    jQuery('input#delivery_address').bind("paste",function(e){
        var address = e.originalEvent.clipboardData.getData('text');
        var vendorAddress=jQuery('input#vendor_address').val();
        getDistanceMatrix(address,vendorAddress);
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

//Initilizing Checkout page Accordion under narrow screen size
    (function(){
        var currentDisplayedAccordionPanel; 
        function toggleAccordionPanel(panelElement){
            if(panelElement && panelElement.hasClass("accordion_panel")){
                panelElement.toggleClass('active');
            }
        }
        var accordions = jQuery(".accordion_head").each(function(){
            var self = jQuery(this);
            var nextButton = self.next().find('.accordion_next_button');
            nextButton.click(function(e){ 
                for(var i=0;i<accordions.length;i++){
                    console.log(accordions[i]);
                    console.log(self);
                    if(accordions[i]===self[0]){
                        console.log("found");
                        self.click();
                        accordions[i+1].click();
                    }
                }
                e.preventDefault();
            });
        }).click(function(){
            var self = jQuery(this);
            if(this === currentDisplayedAccordionPanel){
                self.toggleClass('active');
                toggleAccordionPanel(self.next()); 
                currentDisplayedAccordionPanel=null;
            }else{
                if(currentDisplayedAccordionPanel){
                    var previous=jQuery(currentDisplayedAccordionPanel)
                    jQuery(currentDisplayedAccordionPanel).toggleClass('active');
                    toggleAccordionPanel(previous.next());  
                }
                currentDisplayedAccordionPanel=this;
                self.toggleClass('active');
                toggleAccordionPanel(self.next()); 
            }
        }); 
        
    })();

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
        addDeliveryChargeProcessing();
    var service = new google.maps.DistanceMatrixService();
    service.getDistanceMatrix(
      {
        origins: [fromAddress],
        destinations: [destAddress],
        travelMode: 'DRIVING',
        unitSystem: google.maps.UnitSystem.IMPERIAL,
        avoidHighways: false,
        avoidTolls: false
      }, deliveryCostCallback);
}
function deliveryCostCallback(response, status) {
        var messageContainer = jQuery("div#message-container");
    if(status === 'OK') {// this mean request is ok
                var respElements =response.rows[0].elements[0];
                if(respElements.status==="OK"){
                    // meaning we got an ok respose
                    handleGoogleOkStatus(respElements,messageContainer);
                }else{
                    handleGoogleNotOkStatus(messageContainer,respElements.status);
                }
    }else{
            handleGoogleNotOkStatus(messageContainer,respElements,"NOT_OK");
        }
}
function handleGoogleNotOkStatus(messageContainer,status){
    if(status==="NOT_FOUND" || status === "ZERO_RESULTS"){
        messageContainer.html("很抱歉，找不到在下的地址！");
    }else{
        messageContainer.html("很抱歉，无法算出你的外送费用");
    }
    removeDeliveryChargeProcessing();
    // handle the message
}

function handleGoogleOkStatus(response,messageContainer){
    var dist = response.distance.text;
    var costPerMile = 0.99;
    var distVal = convertToValue(dist); 
    var deliveryCost = distVal * costPerMile ;
    var costAfterFixed =deliveryCost.toFixed(2);
    messageContainer.html("距離: " + dist + ", 送外卖费: $" + costAfterFixed);
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

function convertToValue(inputDistance) {
    var arr = inputDistance.split(" ");
    var dist = arr[0];
    dist = dist.replace(",","");
    return dist;
}



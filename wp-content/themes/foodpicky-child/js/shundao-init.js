;jQuery(document).ready(function(){

    jQuery('#deliveryCost').html("$money: 4.99");
    //bind the keypress
    var regex = /(\d+) ((\w+[ ,])+ ){2}([a-zA-Z]){2} (\d){5}/;

    jQuery('#billing_postcode_field').keypress(_.debounce(function(e){
      //This is just used to show random value for demo.
      var cost = Number(Math.random() * 10 * .99).toFixed(2);
      getDistanceMatrix();
    },500));

    jQuery('input#delivery_address').keypress( _.debounce( function(e){
        var address = e.currentTarget.value;
        if((currentMatch = regex.exec(address)) !==null && currentMatch.length>0 ){
            console.log("match");
        }else{
            console.log("not match");
        }
        console.log(address);
    }, 800 ) );

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


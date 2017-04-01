function shundao_success(pos){
    var geocoder = new google.maps.Geocoder();
    var lat = pos.coords.latitude;
    var lon = pos.coords.longitude;
    // we need to find the vendor's address
    jQuery(".address .value").each(function(i,item){
        
        var vendorValue = this;
        var vendorAddress = jQuery(this).text();
        setTimeout(function(){
            geocoder.geocode({'address':vendorAddress},function(results,status){
               if(status===google.maps.GeocoderStatus.OK){
                   var vendorLat =  results[0].geometry.location.lat();
                   var vendorLong = results[0].geometry.location.lng();
                   currentDistance = distance(lat,lon,vendorLat,vendorLong);
                   addDistance(currentDistance,vendorValue);
                }else{
                    console.log(status);
                }
            });
        },650 * i );
    });
}
function shundao_error(){
    console.log("error");
}

function addDistance(currentDistance, vendorValue){
     var entryFooter = jQuery(vendorValue).closest("div.entry-footer");
        var vendorDistance = entryFooter.find("div.vendor-distance");
        vendorDistance.html(currentDistance+" miles ");
}

function distance(lat1, lon1, lat2, lon2) {
  var p = 0.017453292519943295;    // Math.PI / 180
  var c = Math.cos;
  var a = 0.5 - c((lat2 - lat1) * p)/2 + 
          c(lat1 * p) * c(lat2 * p) * 
          (1 - c((lon2 - lon1) * p))/2;

  return floorFigure(12742 * Math.asin(Math.sqrt(a))); // 2 * R; R = 6371 km
}

function floorFigure(figure, decimals){
    if (!decimals) decimals = 2;
    var d = Math.pow(10,decimals);
    return (parseInt(figure*d)/d).toFixed(decimals);
};

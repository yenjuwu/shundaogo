var placeSearch, autocomplete;

 function initAutocomplete() {
   // Create the autocomplete object, restricting the search to geographical
   // location types.
   var address_field = document.getElementById('delivery_address');
   autocomplete = new google.maps.places.Autocomplete(
       /** @type {!HTMLInputElement} */(address_field),
       {types: ['geocode']});

   // When the user selects an address from the dropdown, populate the address
   // fields in the form.
   autocomplete.addListener('place_changed', fillInAddress);
 }

 function fillInAddress() {
   // Get the place details from the autocomplete object.
   var place = autocomplete.getPlace();
   var address = place.formatted_address;
   if(typeof address !=='undefined' && address!==''){
       var vendorAddress=jQuery('input#vendor_address').val();
       getDistanceMatrix(address,vendorAddress);
   }
 }

 // Bias the autocomplete object to the user's geographical location,
 // as supplied by the browser's 'navigator.geolocation' object.
 function geolocate() {
   if (navigator.geolocation) {
     navigator.geolocation.getCurrentPosition(function(position) {
       var geolocation = {
         lat: position.coords.latitude,
         lng: position.coords.longitude
       };
       var circle = new google.maps.Circle({
         center: geolocation,
         radius: position.coords.accuracy
       });
       autocomplete.setBounds(circle.getBounds());
     });
   }
 }
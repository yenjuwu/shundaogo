;jQuery(document).ready(function(){
    //bind the keypress
    var regex = /(\d+) ((\w+[ ,])+ ){2}([a-zA-Z]){2} (\d){5}/;

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


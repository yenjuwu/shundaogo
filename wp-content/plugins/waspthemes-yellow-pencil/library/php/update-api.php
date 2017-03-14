<?php
/*
    Yellow Pencil codecanyon update api.
*/

// General.
define("YP_TIMEOUT",86400); // 1DAY = 86400

// User information
define("YP_USERNAME",get_option('yp_username'));
define("YP_APIKEY",get_option('yp_apikey'));
define("YP_PURCHASE_CODE",get_option('yp_purchase_code'));

// Basic
function yp_get_plugin($plugin){

    $args = array(
        'path' => ABSPATH.'wp-content/plugins/'
    );

    $plugin = $plugin[0];

    yp_plugin_download($plugin['path'], $args['path'].$plugin['name'].'.zip');
    yp_plugin_unpack($args, $args['path'].$plugin['name'].'.zip');
    yp_plugin_activate($plugin['install']);

}

// Response code
function yp_get_http_response_code($url){

    if( ini_get('allow_url_fopen') ) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }else{
        return null;
    }

}

// Getting version
function yp_getting_ver_from_changelog(){

    $version = 0;
    $pluginVersion = YP_PLUGIN_VERSION;

    // Changelog URL
    $url = "http://www.waspthemes.com/yellow-pencil/inc/changelog.txt";

    // If page found.
    if(yp_get_http_response_code($url) == 200){

        // Getting Changelog
        $changelog = wp_remote_get($url, array( 'sslverify' => false ));
                
        if(is_array($changelog)){
            $changelog = $changelog['body'];
        }
        
        // If have data.
        if(!empty($changelog)){

            // Get First line.
            $last_update = substr($changelog, 0, 32);

            // Part of first line.
            $array = explode('(',$last_update);

            // Only version.
            $version = yp_version($array['0']);

            if($version > $pluginVersion){
                            
                // Add to datebase, because have a new update.
                if(get_option('yp_update_status') !== false ){
                    update_option( 'yp_update_status', 'true');
                    update_option( 'yp_last_check_version', $pluginVersion);
                    update_option( 'yp_available_version', $version);
                }else{
                    add_option( 'yp_update_status', 'true');
                    add_option( 'yp_last_check_version', $pluginVersion);
                    add_option( 'yp_available_version', $version);
                }
                
                    return true;
                            
            }else{
                            
                // Update database, because not have a new update.
                if(get_option('yp_update_status') !== false ){
                    update_option( 'yp_update_status', 'false');
                }else{
                    add_option( 'yp_update_status', 'false');
                }
                
                return false;
                
            }
                
        } // If has data.
                
    } // IF URL working.

}

// Check everyday for update
function yp_update_checker(){
    
    $timeStamp = current_time('timestamp', 1 );
    if(get_option('yp_checked_data') !== false ){

        if(($timeStamp-get_option('yp_checked_data')) > YP_TIMEOUT){ // 1day. 86400

            yp_getting_ver_from_changelog();
            
            update_option( 'yp_checked_data', $timeStamp);
            
        }
    
    }else{
        
        // First Check
        yp_getting_ver_from_changelog();
        
        add_option( 'yp_checked_data', $timeStamp);
        
    }
    
    
}

// Getting plugin download uri from envato
function yp_get_plugin_patch($username,$apikey,$purchase_code){

    // if any is empty, stop.
    if(empty($username) == true || empty($apikey) == true || empty($purchase_code) == true){
        return false;
    }

    // Envato download api.
    $download_uri = 'http://marketplace.envato.com/api/edge/'.$username.'/'.$apikey.'/download-purchase:'.$purchase_code.'.json';
    
    // Getting plugin download url
    $data = yp_remote_request($download_uri);
    $data = json_decode($data,true);

    // if there is a error, stop.
    if(!empty($data['error'])){
        return false;
    }

    // if there is a error, stop.
    if(empty($data['download-purchase'])){
        return false;
    }

    // be sure plugin url founded
    if(!isset($data['download-purchase']['wordpress_plugin'])){
        die("<b>Error:</b> Please check purchase code, api key and username. If you didn't purchase this plugin, you can <a target='_blank' href='http://waspthemes.com/buy'>purchase here</a>.");
    }

    // return download url.
    return $data['download-purchase']['wordpress_plugin'];

}

// File download func.
function yp_remote_request( $url ) {
    
      if ( empty( $url ) ) {
        return false;
      }

    $args = array(
        'headers'    => array( 'Accept-Encoding' => '' ), 
        'timeout'    => 300000,
        'user-agent' => 'Yellow Pencil Updater',
    );

    $request = wp_safe_remote_request( $url, $args );

    if (is_wp_error($request)){
        return json_decode($request['body'],true);
    }
      
    if ( $request['response']['code'] == 200 ) {
        return $request['body'];
    }

    return false;

}

// Downloading plugin zip file.
function yp_plugin_download($url, $path){

    if(empty($url)){
        die("<b>Error:</b> Please check purchase code, api key and username. If you didn't purchase this plugin, you can <a target='_blank' href='http://waspthemes.com/buy'>purchase here</a>.");
    }

    $data = yp_remote_request($url);

    if(file_put_contents($path, $data)){
        return true;
    }else{
        return false;
    }

}

// Unpack zip file.
function yp_plugin_unpack($args, $target){

    if($zip = zip_open($target)){

        while($entry = zip_read($zip)){

            $is_file = substr(zip_entry_name($entry), -1) == '/' ? false : true;
            $file_path = $args['path'].zip_entry_name($entry);
            if($is_file){

                if(zip_entry_open($zip,$entry,"r")){
                    $fstream = zip_entry_read($entry, zip_entry_filesize($entry));
                    file_put_contents($file_path, $fstream );
                    chmod($file_path, 0644);
                }

                zip_entry_close($entry);
        
            }else{

                if(zip_entry_name($entry)){
                    if (!file_exists($file_path)){
                        mkdir($file_path, 0755);
                    }
                }

            }

        }

        zip_close($zip);

    }

    // delete zip file.
    unlink($target);

}

// Active new version.
function yp_plugin_activate($installer){

    $current = get_option('active_plugins');
    $plugin = plugin_basename(trim($installer));

    if(!in_array($plugin, $current)){
        $current[] = $plugin;
        sort($current);
        do_action('activate_plugin', trim($plugin));
        update_option('active_plugins', $current);
        do_action('activate_'.trim($plugin));
        do_action('activated_plugin', trim($plugin));
        return true;
    }else{
        return false;
    }

}

// show update message.
function yp_update_message(){

    $lastCheckVer = get_option('yp_last_check_version');
    $isUpdate = get_option('yp_update_status');
    $available = get_option('yp_available_version');

    if($isUpdate != 'true' && current_user_can("update_plugins") == true && YP_USERNAME == '' && YP_APIKEY == '' && YP_PURCHASE_CODE == ''){
        ?>
        <div class="error yp-update-info-bar">
            <p><?php _e("Purchase code is empty or invalid. Please enter your purchase code for Yellow Pencil.","yp"); ?> <a style="box-shadow:none !important;-webkit-box-shadow:none !important;-moz-box-shadow:none !important;" href="<?php echo admin_url('options-general.php?page=yp-update-settings'); ?>"><?php _e("Activate","yp"); ?></a> | <a style="box-shadow:none !important;-webkit-box-shadow:none !important;-moz-box-shadow:none !important;" href="http://waspthemes.com/yellow-pencil/buy"><?php _e("Get it now!","yp"); ?></a></p>
        </div>
    <?php
    }
    
    if($isUpdate == 'true' && $lastCheckVer == YP_PLUGIN_VERSION && $available > YP_PLUGIN_VERSION && current_user_can("update_plugins") == true && YP_USERNAME != '' && YP_APIKEY != '' && YP_PURCHASE_CODE != ''){
        
        ?>
        <div class="updated yp-update-info-bar">
            <p><?php _e("New update available for Yellow Pencil. Please update the plugin for new features and improvements.","yp"); ?> <a style="box-shadow:none !important;-webkit-box-shadow:none !important;-moz-box-shadow:none !important;" href="#" class="yp_update_link"><?php _e("Update Now!","yp"); ?></a></p>
        </div>
        <?php
            
    }elseif($isUpdate == 'true' && $lastCheckVer == YP_PLUGIN_VERSION && $available > YP_PLUGIN_VERSION && current_user_can("update_plugins") == true){

        ?>
        <div class="updated yp-update-info-bar">
            <p><?php _e("A new update is available for Yellow Pencil! Please verify your purchase for automatic updates.","yp"); ?> <a style="box-shadow:none !important;-webkit-box-shadow:none !important;-moz-box-shadow:none !important;" href="<?php echo admin_url('options-general.php?page=yp-update-settings'); ?>"><?php _e("Verify Now!","yp"); ?></a></p>
        </div>
        <?php

    }

}

// Begin to update.
function yp_update_now(){

    $lastCheckVer = get_option('yp_last_check_version');
    $isUpdate = get_option('yp_update_status');
    $available = get_option('yp_available_version');
    
    if($isUpdate == 'true' && $lastCheckVer == YP_PLUGIN_VERSION && $available > YP_PLUGIN_VERSION && current_user_can("update_plugins") == true && YP_USERNAME != '' && YP_APIKEY != '' && YP_PURCHASE_CODE != ''){
        
        // Getting the path.
        $path = yp_get_plugin_patch(YP_USERNAME,YP_APIKEY,YP_PURCHASE_CODE);

        // Update.
        yp_get_plugin(array(
            array('name' => 'yellow_pencil_update_pack', 'path' => $path, 'install' => 'waspthemes-yellow-pencil/yellow-pencil.php'),
        ));
        
    }

    die("Updated.");

}

// Update javascript
function yp_update_javascript() { ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {

        jQuery(".yp_update_link").click(function(){

            // Only one click.
            if(!jQuery(this).hasClass("yp_update_link_disable")){

                // Updating.
                jQuery(this).text("Updating..").css("color","inherit").addClass("yp_update_link_disable");

                jQuery(this).append("<img src='<?php echo esc_url(plugins_url( '/images/wpspin_light.gif' , dirname(dirname(__FILE__)) )); ?>' style='position: relative;left: 7px;top: 2px;width: 12px;height: 12px;' />");

                var data = {
                    'action': 'yp_update_now'
                };

                jQuery.post(ajaxurl,data, function(response) {
                    jQuery(".yp-update-info-bar").html("<p>"+response+"</p>");
                });

            }

        });

    });
    </script><?php
}

// Admin update script
add_action( 'admin_footer', 'yp_update_javascript' );

// Alert update
add_action( 'admin_notices', 'yp_update_message' );

// Ajax action.
add_action( 'wp_ajax_yp_update_now', 'yp_update_now' );

if(is_admin()){
    yp_update_checker();
}




/* -------------------------------------------- */
/* UPDATE ADMIN PAGE                            */
/* -------------------------------------------- */

// Add settings page
add_action('admin_menu', 'yp_create_setting_menu');


// Create submenu
function yp_create_setting_menu() {

    if(defined('WTFV')){

        add_submenu_page( 'options-general.php', 'Yellow Pencil Updater', 'Yellow Pencil Updater', 'edit_theme_options', 'yp-update-settings', 'yp_settings' );

        add_action( 'admin_init', 'yp_register_settings' );

    }

}

//register our settings
function yp_register_settings(){
    register_setting( 'yp-update-settings', 'yp_username' );
    register_setting( 'yp-update-settings', 'yp_apikey' );
    register_setting( 'yp-update-settings', 'yp_purchase_code' );

    if(isset($_POST['yp_username'])){
        delete_option("yp_checked_data"); // Check again for update after fill options.
    }

}

// Settings page
function yp_settings(){ ?>
    <div class="wrap">
    <h2>Yellow Pencil Automatic Update</h2>


    <div class="postbox yp-settings-box">
        <div class="inside">
    <p style="font-weight:600;"><?php _e("Please fill all fields for activating your copy of Yellow Pencil and unlock automatic updates. Don't worry!<br> You will not lose your changes after updates.","yp"); ?> <?php _e('Need to <a target="_blank" href="http://waspthemes.com/yellow-pencil">Help?</a>','yp'); ?></p>

    <style>
    .yp-settings-box p.submit{
        padding-bottom:0;
        margin-bottom:0;
    }
    .yp-settings-box{
        display: inline-block;
        padding: 20px;
    }
    </style>

    <form method="post" action="options.php">
        <?php settings_fields( 'yp-update-settings' ); ?>
        <?php do_settings_sections( 'yp-update-settings' ); ?>
        <table class="form-table">
            <tr valign="top">
            <th scope="row"><?php _e('Codecanyon Username','yp'); ?></th>
            <td><input type="text" name="yp_username" value="<?php echo esc_attr( get_option('yp_username') ); ?>" />
            <p style="font-weight:400;font-style:italic;font-size:90%;">Your <a target="blank" href="http://codecanyon.net">codecanyon</a> username.</p>
            </td>
            </tr>
             
            <tr valign="top">
            <th scope="row"><?php _e('Codecanyon API Key','yp'); ?></th>
            <td><input type="text" name="yp_apikey" value="<?php echo esc_attr( get_option('yp_apikey') ); ?>" />
            <p><a target="_blank" style="font-weight:400;font-style:italic;font-size:90%;text-decoration:none;" href="https://help.market.envato.com/hc/en-us/articles/204498284-API-Keys"><?php _e('Where is my API key?','yp'); ?></a></p>
            </td>
            </tr>
            
            <tr valign="top">
            <th scope="row"><?php _e("Plugin Purchase Key","yp"); ?></th>
            <td><input type="text" name="yp_purchase_code" value="<?php echo esc_attr( get_option('yp_purchase_code') ); ?>" />
            <p><a target="_blank" style="font-weight:400;font-style:italic;font-size:90%;text-decoration:none;" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Can-I-Find-my-Purchase-Code-"><?php _e("Where is purchase key?","yp"); ?></a></p>
            </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
    </div></div></div><?php
} ?>
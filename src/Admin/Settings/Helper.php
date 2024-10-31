<?php
namespace RCFV\Admin\Settings;

class Helper {

    /**
     * Get Firebase APP Credentials
     */
    public function getAPPCredentials( $type = "" ) {
        $settings_data = $app_config = array();
        
        $options = get_option("firebase-chat-settings");

        if(!$options)
        return array();
        if($type == "firebase-app-config" && isset($options["firebase_app_config"]))
        $app_config = $options["firebase_app_config"];

        else if($type === "firebase-service-account" && isset($options["firebase_db_config"]) ) {
            $app_config = $options["firebase_db_config"];
        } else if($type === "firebase-roles" && isset($options["firebase_user_roles"]) ) {
            $app_config = $options["firebase_user_roles"];
        }
         else {
            $app_config = $options;
         }
        return $app_config;
    }
}
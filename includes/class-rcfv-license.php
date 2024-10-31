<?php

/**
 * Register all license functgions for the plugin
 *
 * @link       https://https://raoinformationtechnology.com/
 * @since      1.0.0
 *
 * @package    Raopress_chat
 * @subpackage Raopress_chat/includes
 */

class RCFV_License {

    protected $server_url = "https://licence-manager.raoinfo.tech/wp-json/";

    protected $web_server_url = "https://licence-manager.raoinfo.tech/wp-json/";

    protected $client_id = "ck_fcb0d0db9fac299a04ca58ab1ad9a77ffec36bb5";
    protected $client_secret = "cs_8d2ff8d93c652e977c9476002a1e4bb223a4f3be";
    public function __construct() {

    }

    public function activate_license( $license_key = "" ) {
        $api_hook = "lmfwc/v2/licenses/activate/";
        $endpoint_url = $this->server_url.$api_hook.$license_key;
        $response = $this->make_request($endpoint_url, "GET", array(), 200);
        return $response;
    }

    public function deactivate_license( $license_key = "" ) {
        $api_hook = "lmfwc/v2/licenses/deactivate/";
        $endpoint_url = $this->server_url.$api_hook.$license_key;
        $response = $this->make_request($endpoint_url, "GET", array(), 200);
        return $response;
    }

    public function deregister_license( $license_data, $license_key )  {
        $api_hook = "lmfwcrao/v2/licenses/deregister/".$license_key;
        $endpoint_url = $this->server_url.$api_hook;
        $args["userId"] = $license_data["userId"];
        $response = $this->make_request($endpoint_url, "POST", $args, 200 );
        return $response;
    }

    public function register_license( $license_data, $license_key ) {
        $api_hook = "lmfwcrao/v2/licenses/client-register/".$license_key;
        
        $args["orderId"] = $license_data["orderId"];
        $args["userId"] = $license_data["userId"];
        $args["siteUrl"]   = site_url();
        $args["license_key"] = $license_key;
        $endpoint_url = $this->server_url.$api_hook;
        $response = $this->make_request($endpoint_url, "POST", $args, 200 );
       
        return $response;
        
    }

    public function validate_license( $license_key ) {
        $api_hook = "lmfwcrao/v2/licenses/validate/".$license_key;
        $endpoint_url = $this->server_url.$api_hook;
        $response = $this->make_request($endpoint_url, "GET", array(), 200, false);
        
        return $response;
    }

    public function make_request( $endpoint_url, $method = "GET", $args = array(), $status_code = 200, $auth = true ) {
        $error_message = "";
        if($auth) {
        $send_args["headers"] = array(
            "Authorization" =>  "Basic ". base64_encode($this->client_id.":".$this->client_secret)
        );
        }
        
        $send_args["timeout"] = 45;
        if( $method === "GET" ) {
            $response_data = wp_remote_get( $endpoint_url, $send_args );
        } else {
            $send_args["headers"]['Content-Type'] = 'application/json';
            $send_args["method"] = $method;
            $send_args["body"] = json_encode( $args );
            $response_data = wp_remote_post( $endpoint_url, $send_args );
        }
        $response_code = wp_remote_retrieve_response_code( $response_data );
        $response_message = wp_remote_retrieve_response_message( $response_data );
        if( is_wp_error( $response_data ) ) {
            $message = $response_data->get_error_message();
            return array("error"=>$message);
        } else {
            $response_body = json_decode( wp_remote_retrieve_body( $response_data ), true );
        }
        return $response_body;
    }


}

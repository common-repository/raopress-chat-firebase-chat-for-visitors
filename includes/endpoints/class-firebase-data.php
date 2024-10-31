<?php
class Firebase_Data extends WP_REST_Controller {

    /**
     * @var string
     */
    protected $namespace = 'raopress-chat/v2';

    /**
     * @var string
     */
    protected $rest_base = '/firebase';

    protected $app_config = "";
    protected $db_config = "";

    public function __construct() {

    }

    public function register_routes() {
        register_rest_route(
            $this->namespace, $this->rest_base.'/configobject/(?P<license_key>[\w-]+)', array(
                array(
                    "methods"   =>      WP_REST_SERVER::READABLE,
                    "callback"              =>      array($this, "sendFirebaseObject" ),
                    "permission_callback"   =>      array( $this, "permissionCallback" )
                )
            )
        );

        register_rest_route(
            $this->namespace, $this->rest_base.'/login', array(
                array(
                    "methods"   =>      WP_REST_SERVER::READABLE,
                    "callback"              =>      array($this, "sendAuth" ),
                    "permission_callback"   =>      array( $this, "authPermissionCallback" )
                )
            )
        );
    }

    public function authPermissionCallback( WP_REST_REQUEST $request ) {
        //authenticate username & password
        //check if specific role is allowed for the access
        $body_params = $request->get_params();
       if( !isset($body_params["email"]) || !isset($body_params["password"]))
       {
            return new WP_Error(
                'rcap_rest_fields_required',
                __('Email & Password fields are required!'),
                array(
                    'status' => 401
                )
            );
            
       }
        
       $email = sanitize_email( $body_params["email"] );
       $password = sanitize_text_field( $body_params["password"] );

       if( $email === "" || $password === "" ) {
        return new WP_Error(
            'rcap_rest_fields_empty',
            __('Email or Password cannot be empty!'),
            array(
                'status' => 401
            )
        );
       }

       $user = get_user_by("email", $email);
       if( !$user->ID ) {
        return new WP_Error(
            'rcap_rest_user_not_found',
            sprintf(__('User with email %s not found!'),$email),
            array(
                'status' => 404
            )
        );
       }
       
       $authenticate = wp_authenticate( $email, $password );
       if( is_wp_error( $authenticate ) ) {
        return new WP_Error(
            'rcap_rest_user_invalid',
            __("Invalid email/password"),
            array(
                'status' => 401
            )
        );
       }

       $user_id = $authenticate->ID;
       $user_role = ( array ) $authenticate->roles;
       
       $options = get_option("firebase-chat-settings");
       if(isset( $options["firebase_user_roles"]) && !empty($options["firebase_user_roles"]))
       {
            if( !array_intersect($user_role, $options["firebase_user_roles"] ))
            {
                return new WP_Error(
                    'rcap_rest_user_not_authorized',
                    __("Unauthorized user role"),
                    array(
                        'status' => 401
                    )
                );
            }
       } else {
        
            return new WP_Error(
                'rcap_rest_user_not_authorized',
                __("User role is not saved in Raopress Chat plugin settings"),
                array(
                    'status' => 401
                )
            );
        
       }
       return true;
    }

    public function sendAuth() {
        $license_key = get_option("raopress_chat_admin_pro_license");
        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $license_key
            ),
            200
        );
    }

    public function permissionCallback( WP_REST_REQUEST $request ) {
        $body_params = $request->get_params();
        if( !isset($body_params["license_key"])) {
            return new WP_Error(
                'rcap_rest_fields_required',
                __('License field is required!'),
                array(
                    'status' => 401
                )
            );
        }
        return true;
    }

    public function sendFirebaseObject( WP_REST_REQUEST $request ) {
        //check license key
        $licenseKey = sanitize_text_field($request->get_param('license_key'));
        $options = get_option("firebase-chat-settings");
        $send_data["app_config"] = json_decode($options["firebase_app_config"],true);
        $send_data["db_config"] = json_decode($options["firebase_db_config"],true);
        
        if( $send_data["app_config"] === "" && $send_data["db_config"] === "")
        {
            return new WP_Error(
                'rcap_rest_invalid_firebase_object',
                __("Invalid firebase object"),
                array(
                    'status' => 401
                )
            );
        }

        //return $this->response(true, "hello", 200, "v2/firebase-object/validate'");
        return new WP_REST_Response(
            array(
                'success' => true,
                'data'    => $send_data
            ),
            200
        );

    }
    
}
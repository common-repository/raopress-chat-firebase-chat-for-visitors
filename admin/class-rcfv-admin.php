<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://raoinformationtechnology.com
 * @since      1.0.0
 *
 * @package    RCFV
 * @subpackage RCFV/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    RCFV
 * @subpackage RCFV/admin
 * @author     raoinfotech <admin@raoinformationtechnology.com>
 */
class RCFV_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $firebase_chat    The ID of this plugin.
	 */
	private $firebase_chat;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	private $locale = "rcfv";

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $firebase_chat       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $firebase_chat, $version ) {

		$this->firebase_chat = $firebase_chat;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in RCFV_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The RCFV_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$page = $page_set = false;
		if(isset($_GET["page"]))
		{
			$page_set = true;
			$page = sanitize_text_field($_GET["page"]);
		}
		if( $page_set && $page === "firebase-chat-settings") {
			if(isset($_GET["tab"])) {
			$tab_set = true;
			$tab_slug = sanitize_text_field($_GET["tab"]);
			} else {
				$tab_set = false;
				$tab_slug = "";
			}
			if(!$tab_set || ($tab_set && $tab_slug == "general")) {
			wp_enqueue_style( $this->firebase_chat, RCFV_PLUGIN_URL . 'admin/css/rcfv-admin.css', array(), $this->version, 'all' );
			wp_register_style( 'select2css', RCFV_PLUGIN_URL.'assets/select2.css', false, '2.0', 'all' );
			wp_enqueue_style( 'select2css' );
			}

			if($tab_set && $tab_slug === "frontend_chat_widget_settings") {
				wp_enqueue_style( "firebase-chat-widget", RCFV_PLUGIN_URL . 'admin/css/rcfv-widget.css', array(), $this->version, 'all' );
				wp_enqueue_style( 'wp-color-picker');
			} else if( $tab_set && $tab_slug === "raopress_chat_pro_license") {
				
			}
		}

		if( $page_set && $page === "firebase-chat") { 
			wp_enqueue_style( "firebase-fontawesome", 'https://use.fontawesome.com/releases/v5.6.3/css/all.css', array(), $this->version, 'all' );
			wp_enqueue_style( "firebase-bootstrap", RCFV_PLUGIN_URL . 'admin/css/bootstrap-5.2.3.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( "firebase-chat-style", RCFV_PLUGIN_URL . 'admin/css/style.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in RCFV_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The RCFV_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if( isset($_GET["page"]) && sanitize_text_field($_GET["page"]) === "firebase-chat-settings") { 
			wp_register_script( 'select2', RCFV_PLUGIN_URL.'assets/select2.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'select2' );
			wp_register_script( 'firebase-chat-settings', RCFV_PLUGIN_URL . 'admin/js/rcfv-settings.js', array( 'jquery', 'wp-color-picker' ), $this->version, false );
			wp_enqueue_script( 'firebase-chat-settings' );
		}
		if( isset($_GET["page"]) && sanitize_text_field($_GET["page"]) === "firebase-chat") {
		$settings = new RCFV\Admin\Settings\Helper();
		$app_credentials = $settings->getAPPCredentials();
		if(isset($app_credentials["firebase_app_config"])){
		$firebase_credentials = $app_credentials["firebase_app_config"];
		
		$firebase_credentials = json_decode($firebase_credentials, true);
		} else {
			$firebase_credentials  = false;
		}
		if(!is_array($firebase_credentials)) {
			update_option("rao_firebase_user_credentials", false);
			set_transient("rao_auth_error", "yes");
		}
		else {
			delete_transient("rao_auth_error");
		}
		if(is_array($firebase_credentials)) {
		$get_user_options = get_option("rao_firebase_user_credentials");
		$site_config = array("plugin_url"=>RCFV_PLUGIN_URL,"ajaxurl"=>admin_url('admin-ajax.php'));
		wp_enqueue_script( $this->firebase_chat, plugin_dir_url( __FILE__ ) . 'js/rcfv-admin.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( $this->firebase_chat, "firebaseConfig", $firebase_credentials );	
		wp_localize_script( $this->firebase_chat, "siteConfig", $site_config );	
		wp_enqueue_script( "firebase-app", "https://www.gstatic.com/firebasejs/8.2.10/firebase-app.js", "", "", true );
		wp_enqueue_script( "firebase-auth", "https://www.gstatic.com/firebasejs/8.2.10/firebase-auth.js", "", "", true );
		wp_enqueue_script( "firebase-database", "https://www.gstatic.com/firebasejs/8.2.10/firebase-database.js", "", "", true );
		wp_enqueue_script( "firebase-storage", "https://www.gstatic.com/firebasejs/8.2.10/firebase-storage.js", "", "", true ); 
		wp_enqueue_script( "firebase-init", RCFV_PLUGIN_URL."admin/js/firebase.init.js", array("jquery"), RCFV_VERSION, true );
		if($get_user_options && isset($get_user_options["email"]) && isset($get_user_options["pwd"]) && $get_user_options["email"] !== "" && $get_user_options["pwd"] !== "")
		wp_enqueue_script( "firebase-raopress-chat", RCFV_PLUGIN_URL."admin/js/raoChat.js", array("jquery","moment", "firebase-app", "firebase-auth","firebase-database","firebase-storage"),RCFV_VERSION, true);
		wp_enqueue_script( "firebase-user", RCFV_PLUGIN_URL."admin/js/raoUser.js", array("jquery"),RCFV_VERSION, true);
		}
		}

		if(isset($_GET["tab"])) {
			$tab_set = true;
			$tab_slug = sanitize_text_field($_GET["tab"]);
			} else {
				$tab_set = false;
				$tab_slug = "";
			}
			if(!$tab_set || ($tab_set && $tab_slug == "raopress_chat_pro_license")) {
				$license_config = array("ajaxurl"=>admin_url('admin-ajax.php'));
				wp_enqueue_script( "rcfv-license", RCFV_PLUGIN_URL."admin/js/rcfv-license.js", array("jquery"),RCFV_VERSION, true);
				wp_localize_script( "rcfv-license", "licenseConfig", $license_config );	
			}
	}

	/**
	 * Add firebase Chat Menu
	 * 
	 * @since v1.0.0
 	 */
	 public function add_firebase_chat_menu() {
		//add_menu_page(__('Firebase Chat', $this->locale), __('Firebase Chat', $this->locale), 'manage_options', 'firebase-chat', array( $this,'firebase_chat_template' ) );
		//add_submenu_page( 'firebase-chat', __('Settings', $this->locale), __('Settings', $this->locale), 'manage_options', 'settings', array($this, "firebase_chat_settings_template"));
		}

	 /**
	  * Include Firebase Chat Template
	  */
	public function firebase_chat_template() {
		$path = RCFV_PLUGIN_DIR."admin/partials/new-template.php";
		include_once $path;
	}

	/**
	 * Add WP User to Firebase Chats when new user registers
	 * 
	 * @since v1.0.0
	 */
	public function add_user_to_firebase_chats( $user_id ) {
		if( !$user_id ) {
			return;
		}

		$validate = get_transient("rao_firebase_global_error");
		if("yes" === $validate)
		return;

		$chat_id = get_user_meta($user_id,"chat_id", true);

		//create user on firebase only if user is not created
		if($chat_id == "" || !$chat_id || empty($chat_id)){
		
		require_once RCFV_PLUGIN_DIR.'db/firebase-db.php';
		 

		$users = new Firebase_Users();
		$user_data = get_user_by("id", $user_id);
		$avatar_args = array("class"=>"wp_chatee_rao_user_avatar_default");
		$avatar = get_avatar($user_data->user_email,25,404,"",$avatar_args);
		$send_data["name"] = $user_data->display_name;
		$send_data["email"] = $user_data->user_email;
		$send_data["user_registered"] = $user_data->user_registered;
		$send_data["message"] = "";
		$send_data["photoURL"] = $avatar;
		$chat_key = $users->insert($send_data);

		update_user_meta( $user_id, 'chat_id', $chat_key);
		}
	}

	public function filter_avatar( $avatar, $email, $size, $default, $alt, $args ) {
		$avatar_url = str_replace( 'd=' . $args['default'], 'd=404', $args['url'] );
		// Request the image url
		$response = wp_remote_get( $avatar_url );
		// If there's no avatar, the default will be used, which results in 404 response
		if ( 404 === wp_remote_retrieve_response_code( $response ) ) {
			// Do something
			return false;
		}
		return $avatar;

	}

	public function update_user_profile($user_id, $old_data, $user_data ) {
		require_once RCFV_PLUGIN_DIR.'db/firebase-db.php';
		$user = get_user_by("id",$user_id);
		$chat_key = get_user_meta($user->ID, "chat_id", true);
		if($chat_key !== "") {
			$avatar_args = array("class"=>"wp_chatee_rao_user_avatar_default");
		$avatar = get_avatar($user->user_email,25,404,"",$avatar_args);
		$send_data["name"] = $user->display_name;
		$send_data["email"] = $user->user_email;
		$send_data["photoURL"] = $avatar;
		$updates = [
			'users/'.$chat_key => $send_data
		];
		$users = new Firebase_Users();
		$users->update($updates);
		}

	}

	/**
	 * Add WP User to Firebase Chats for existing user if chat_id is not 
	 * 
	 * @since v1.0.0
	 */
	public function check_and_add_user_to_firebase_chats( $user_login, $user ) {
		if( !$user ) {
			return;
		}

		$this->add_user_to_firebase_chats( $user->ID );
		
	}

	/**
	 * Save Login Status
	 */
	public function save_login_status() {
		$login_options["email"] = sanitize_email($_POST["email"]);
		if(sanitize_text_field($_POST["success"]) === "yes") {
		
		$login_options["pwd"] = $_POST["pwd"];
		update_option("rao_chat_admin_display_name",sanitize_text_field(wp_unslash($_POST["admin_name"])));
		
		} else {
			update_option("rao_chat_admin_display_name",false);
			$login_options["pwd"] = "";
		//update_option("rao_firebase_user_credentials", false);
		}
		update_option("rao_firebase_user_credentials", $login_options);
		wp_send_json_success();
		wp_die();

	}	



	/**
	 * Save Firebase Login Access
	 */
	public function save_rao_firebase_login() {
		if (!check_ajax_referer('rcfv_admin_nonce' )){
			wp_die();
		}
		$login_options["email"] = sanitize_email($_POST["email"]);
		$login_options["pwd"] = sanitize_text_field(wp_unslash($_POST["pwd"]));
		$login_options["name"] = "";
		
		update_option("rao_firebase_user_credentials", $login_options);
		wp_safe_redirect(admin_url()."?page=firebase-chat");
	}

	/**
	 * Remove Firebase Login
	 */
	public function remove_firebase_login() {
		$login_creds = get_option("rao_firebase_user_credentials");
		$login_creds["pwd"] = "";
		update_option("rao_firebase_user_credentials", $login_creds);
		if(isset($_POST["error"]) && sanitize_text_field($_POST["error"]) !== "") {
		set_transient("rao_login_error", "Invalid email/password, please try again!");
		} else {
			delete_transient("rao_login_error");
		}
		wp_send_json_success();
		wp_die();
	}

	/**
	 * Save user and pwd on successfull registration
	 */
	public function register_firebase_user() {
		if (!check_ajax_referer('rcfv_admin_nonce' )){
			wp_die();
		}
		$user_creds["email"] = sanitize_email($_POST["email"]);
		$user_creds["pwd"] = sanitize_text_field(wp_unslash($_POST["pwd"]));
		$user_creds["name"] = sanitize_text_field(wp_unslash($_POST["name"]));
		update_option("rao_firebase_user_credentials", $user_creds);
		$admin_chat_id = sanitize_text_field(wp_unslash($_POST["chat_id"]));
		update_option("rao_chat_admin",$admin_chat_id);
		update_option("rao_chat_admin_display_name",$user_creds["name"]);
		wp_send_json_success();
		wp_die();
	}

	/*Add chat admin id*/
	public function add_chat_admin_id() {
		$admin_chat_id = sanitize_text_field(wp_unslash($_POST["chat_id"]));
		update_option("rao_chat_admin",$admin_chat_id);
		wp_send_json_success();
		wp_die();
	}

	/*Add chat admin name*/
	public function add_chat_admin_name() {
		$display_name = sanitize_text_field(wp_unslash($_POST["display_name"]));
		update_option("rao_chat_admin_display_name",$display_name);
		wp_send_json_success();
		wp_die();
	}

	/**
	 * 
	 */
	public function load_firebase_chat_menu() {
		global $wp_roles;
		if($wp_roles) {
        $all_roles = $wp_roles->roles;
		$helper = new RCFV\Admin\Settings\Helper();
        $user_role_options = $helper->getAPPCredentials("firebase-roles");

		if(!$user_role_options || $user_role_options == "administrator")
		$user_role_options = array();
		
		foreach($all_roles as $role_value => $role_text) {
			$role = get_role($role_value);
			if($role_value == "administrator")
			$role->add_cap('rao_firebase_chat_access');
			else {
			if(in_array($role_value,$user_role_options))
			$role->add_cap('rao_firebase_chat_access');
			else
			$role->remove_cap('rao_firebase_chat_access');
			}
		}
		}
		
		$firebase_chat_settings = new RCFV\Admin\Settings\RCFV_Settings();
		
	}

	public function sort_chat_keys() {
		$timestamp_data = $_POST["timestamp_data"];
		//sanitizing
		if( !empty($timestamp_data) ) {
			foreach( $timestamp_data as $key => $t_data ) {
				$timestamp_data[$key]["chatkey"] = sanitize_text_field(wp_unslash($t_data["chatkey"]));
				$timestamp_data[$key]["chat_date"] = sanitize_text_field(wp_unslash($t_data["chat_date"]));
			}
		}
		
		$timestamp_data = wp_list_pluck($timestamp_data,"chat_date","chatkey");
		arsort($timestamp_data);
		wp_send_json_success($timestamp_data);
		wp_die();
	}

	public function display_notices() {
		$no_error = get_option("rao_chat_frontend_status","yes");
		if($no_error === "no") {
			echo wp_kses('<div class="notice notice-error"><p>');
			_e('Frontend Chat Widget is disabled due to conflict with theme or plugins. Try to deactivate plugins one by one or switching to basic theme to find the root cause.',"rcfv-chat");
			echo wp_kses('</p></div>');
		}
	}

	/**
	 * Activate/Deactivate user license
	 * 
	 * @since v1.0.0
	 */
	public function manage_user_license() {
		if (!check_ajax_referer('manage_user_license' )){
			wp_die();
		}
		$error = false;
		$message = "";

		$license_key = sanitize_text_field( $_POST["license_key"] );
		if( $license_key === "" )
		{
			$error = true;
			$message = __("Please enter valid license key");
		} else {
			//activate/deactivate the license
			update_option( "raopress_chat_admin_pro_license", $license_key );
			$current_action = sanitize_text_field( $_POST["current_action"] );
			$lciense_instance = new RCFV_License();
			//$validate_license = $lciense_instance->validate_license( $license_key );
			if( $current_action === "activate" ) {
				
				$license_response = $lciense_instance->activate_license( $license_key );
				if( isset( $license_response["success"]) ) {
					//save website url
					$expire_date = $license_response["data"]["expiresAt"];
					$register_response = $lciense_instance->register_license( $license_response["data"], $license_key);
					if(isset($register_response["data"]) && (sanitize_text_field($register_response["data"]) === "Activated"))
					{
						$error = false;
						$expire_date = "";
						$message = __("License Activated Successfully");
					} else {
						$message = __("Some issue while activating the license. Please contact support");
						$error = true;
						$expire_date = "";
					}
				} else {
					if( isset( $license_data["error"])) {
						$message = $license_data["error"];
						$error = true;
					}
				}
			} else {
				$license_response = $lciense_instance->deactivate_license( $license_key );
				
				if( isset( $license_response["success"]) ) {
					$deregister_response = $lciense_instance->deregister_license( $license_response["data"], $license_key);
					if(isset($deregister_response["data"])) {
						$error = false;
						$expire_date = "";
						$message = __("License Deactivated Successfully");
					}
				}
			}
		}
		if( $error ) {
			update_option("raopress_chat_admin_pro_status", false);
			wp_send_json_error( $message );
		} else {
			update_option("raopress_chat_admin_pro_status", $expire_date);
			wp_send_json_success( $message );
		}

		wp_die();

	}

	/**
	 * Add pro notice sitewide
	 */
	public function add_pro_notice() {
		$page = false;
		if(isset($_GET["page"]))
		$page = sanitize_text_field( $_GET["page"] );
		if( !$page || ($page && $page !== "firebase-chat") ) {
			$license_status = get_transient("raopress_chat_pro_notice_display");
			if($license_status === "yes" || !$license_status ) {
				echo '<div class="notice notice-info raopress-chat-pro-notice is-dismissible" style="border-left-color: #2f4686;">
				<p style="font-size:18px; margin-bottom:30px;">Access RaoPress Chat Inbox from your mobile APP to provide realtime support without accessing the admin dashboard everytime.<a href="https://raoinformationtechnology.com/free-chat-support-plugin-for-wordpress/" target="_blank">Learn more.</a></p>
				<p style="margin-bottom:20px;"><a href="https://licence-manager.raoinfo.tech/product/raopress-chat-mobile-app-admin/" target="_blank" style="text-decoration: none;background: #fff;font-size: 20px;font-weight: bold;color: #2f4686;border: 2px solid #2f4686;padding: 10px;border-radius: 10px;">Upgrade</a></p>
				</div>';
			}
		} 
	}
	

}
<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://raoinformationtechnology.com
 * @since      1.0.0
 *
 * @package    RCFV
 * @subpackage RCFV/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    RCFV
 * @subpackage RCFV/public
 * @author     raoinfotech <admin@raoinformationtechnology.com>
 */
class RCFV_Public {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $firebase_chat       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $firebase_chat, $version ) {

		$this->firebase_chat = $firebase_chat;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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
		wp_enqueue_style( 'rcfv-widget', RCFV_PLUGIN_URL . 'public/css/rcfv-widget.css', array(), '1.1.0', 'all' );
		wp_enqueue_style( 'rcfv-public', RCFV_PLUGIN_URL . 'public/css/rcfv-public.css', array(), '1.1.0', 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		global $post;
		$user = wp_get_current_user();
		$user_id = $user->ID;
		$chat_id = get_user_meta($user_id,"chat_id", false);
		$email = $user->user_email;
		wp_enqueue_script( "rcfv-widget", RCFV_PLUGIN_URL."public/js/rcfv-widget.js", array( 'jquery' ), $this->version, true );
		wp_localize_script("rcfv-widget","firechatee",array("plugin_url",RCFV_PLUGIN_URL));
		//if(isset($post) && $post->ID == 173) {
			//Load scripts and initialize firebase'
			wp_enqueue_script( "rao-cookie", RCFV_PLUGIN_URL."public/js/rao-cookie.min.js",array( 'jquery' ), $this->version, true );
			wp_enqueue_script( "firebase-app", "https://www.gstatic.com/firebasejs/8.2.10/firebase-app.js","","",true );
			wp_enqueue_script( "firebase-auth", "https://www.gstatic.com/firebasejs/8.2.10/firebase-auth.js","", "", true );
			wp_enqueue_script( "firebase-database", "https://www.gstatic.com/firebasejs/8.2.10/firebase-database.js", "", "", true );
			wp_enqueue_script( "firebase-storage", "https://www.gstatic.com/firebasejs/8.2.10/firebase-storage.js", "", "", true ); 
			


			$settings = new RCFV\Admin\Settings\Helper();
		$app_credentials = $settings->getAPPCredentials();
		$firebase_credentials = $app_credentials["firebase_app_config"];
		
		$firebase_credentials = json_decode($firebase_credentials, true);
		if(!is_array($firebase_credentials)) {
			update_option("rao_firebase_user_credentials", false);
			set_transient("rao_auth_error", "yes");
		}
		else {
			delete_transient("rao_auth_error");
		}
		$options = get_option("firebase-chat-settings");
		$error_display = get_option("rao_chat_frontend_status","");
		$welcome_message = $options["welcome_message"];
		$prompt = $options["enable_disable_prompt"];
		if($welcome_message == "")
		$welcome_message = __("How can I help you?");
		$site_config = array(
			"plugin_url"=>RCFV_PLUGIN_URL,
			"ajaxurl"=>admin_url('admin-ajax.php'),  
			"chat_id" => $chat_id, 
			"email" => $email, 
			"welcome_message"=>$welcome_message, 
			"enable_prompt"=> $prompt,
			"error_status" => $error_display);
		wp_localize_script( $this->firebase_chat, "firebaseConfig", $firebase_credentials );	
		wp_localize_script( $this->firebase_chat, "siteConfig", $site_config );	
		wp_enqueue_script( "firebase-public", RCFV_PLUGIN_URL."public/js/rcfv-public.js", array( 'jquery','moment','rao-cookie' ), RCFV_VERSION, true );
		wp_localize_script( "firebase-public", "firebaseConfig", $firebase_credentials );	
		wp_localize_script( "firebase-public", "siteConfig", $site_config );	

		//}
		

	}

	/**
	 * Embed RAO CHAT
	 * 
	 * #since 1.0.0
	 */
	public function embed_rao_chat() {
		$chat_admin_id = get_option("rao_chat_admin",false);
		
		$options = get_option("firebase-chat-settings");
		$enabled = false;
		if(isset($options["enable_disable_chat_widget"]))
		$enabled = $options["enable_disable_chat_widget"];
		$no_error = get_option("rao_chat_frontend_status","yes");

		if( $enabled && $chat_admin_id && ($no_error === "yes") ) {
		
			$attach_enabled = $options["enable_disable_attach"];
			$theme_color = $options["widget_theme_color"];
			
			if($theme_color == "")
			$theme_color = "#42A5F5";
			if(!$chat_admin_id) {
				require_once RCFV_PLUGIN_DIR.'db/firebase-db.php';
				$users = new Firebase_Users();
				$node_data = $users->getLists();
			}
			$path = RCFV_PLUGIN_DIR."public/partials/rcfv-public-template.php";
			include_once $path;
		}
	}

	/**
	 * Create Anonymus User in Firebase from Frontend Chat Widget
	 */
	public function create_anonymus_user() {
		
		if (!check_ajax_referer('anonymus_login_nonce' )){
			wp_die();
		}
		$name = sanitize_text_field(wp_unslash($_POST["name"]));
		$email = sanitize_email($_POST["email"]);
		require_once RCFV_PLUGIN_DIR.'db/firebase-db.php';
		
		$users = new Firebase_Users();
		$send_data["name"] = $name;
		$send_data["email"] = $email;
		$send_data["user_registered"] = current_time("mysql");
		$send_data["message"] = "";

		$chat_key = $users->insert($send_data);
		wp_send_json_success($chat_key);
		wp_die();

	}

	public function update_error_display() {
		$error_display = sanitize_text_field(wp_unslash($_POST["error_display"]));
		update_option("rao_chat_frontend_status",$error_display);
		wp_send_json_success();
	}

	public function check_current_user() {
		$user = wp_get_current_user();
		$return_data = array();
		if( $user->ID ) {	
			$current_user_chat_key = get_user_meta($user->ID, "chat_id", true);
			if($current_user_chat_key === "") {
				//user is logged in before this plugin was installed/configured
				$current_user_chat_key = add_user_to_firebase( $user->ID,$user);
			}

		} else if(isset($_COOKIE["rao_anonymmus_friend_key"])) {
			$current_user_chat_key = sanitize_text_field(wp_unslash($_COOKIE["rao_anonymmus_friend_key"]));
		} else {
			//User is Annonymus
			$current_user_chat_key = "";
		}

		$return_data["id"] = $user->ID;
		$return_data["key"] = $current_user_chat_key;
		$return_data = json_encode( $return_data );
		wp_send_json_success($return_data);
		wp_die();
	}

	public function register_routes() {
		$firebase_instance = new Firebase_Data();
		$firebase_instance->register_routes();
	}

	/**
	 * Add protocols
	 */
	public function add_protocols( $protocols ) {
		if(!is_array($protocols))
		$protocols = [];
		if(!in_array( $protocols["ionic"], $protocols))
		$protocols[] = 'ionic';
		
		if(!in_array( $protocols["capacitor"], $protocols))
		$protocols[] = 'capacitor';
		
		return $protocols;
	}

}
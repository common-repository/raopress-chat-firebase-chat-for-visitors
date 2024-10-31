<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://raoinformationtechnology.com
 * @since      1.0.0
 *
 * @package    RCFV
 * @subpackage RCFV/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    RCFV
 * @subpackage RCFV/includes
 * @author     raoinfotech <admin@raoinformationtechnology.com>
 */
class RCFV {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      RCFV_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $firebase_chat    The string used to uniquely identify this plugin.
	 */
	protected $firebase_chat;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RCFV_VERSION' ) ) {
			$this->version = RCFV_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->firebase_chat = 'firebase-chat';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - RCFV_Loader. Orchestrates the hooks of the plugin.
	 * - RCFV_i18n. Defines internationalization functionality.
	 * - RCFV_Admin. Defines all hooks for the admin area.
	 * - RCFV_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rcfv-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rcfv-license.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/endpoints/class-firebase-data.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rcfv-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rcfv-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rcfv-public.php';
		
		
		$this->loader = new RCFV_Loader();
		

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the RCFV_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new RCFV_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new RCFV_Admin( $this->get_firebase_chat(), $this->get_version() );
		$this->loader->add_action( 'admin_notices', $plugin_admin, "add_pro_notice" );
		//$this->loader->add_action( 'admin_notices', $plugin_admin, "add_license_status" );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'load_firebase_chat_menu');
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'user_register', $plugin_admin, "add_user_to_firebase_chats" );
		$this->loader->add_action( 'wp_login', $plugin_admin, "check_and_add_user_to_firebase_chats", 10, 2 );
		//$this->loader->add_action( 'admin_post_rao_firebase_login', $plugin_admin, "save_rao_firebase_login");
		$this->loader->add_action( 'wp_ajax_remove_firebase_login', $plugin_admin, "remove_firebase_login");
		$this->loader->add_action( 'wp_ajax_register_firebase_user', $plugin_admin, "register_firebase_user");
		$this->loader->add_action( 'wp_ajax_add_chat_admin_id', $plugin_admin, "add_chat_admin_id");
		$this->loader->add_action( 'wp_ajax_add_chat_admin_name', $plugin_admin, "add_chat_admin_name");
		$this->loader->add_action( 'wp_ajax_sort_chat_keys', $plugin_admin, "sort_chat_keys");
		$this->loader->add_action( 'admin_notices', $plugin_admin, "display_notices");
		$this->loader->add_action( 'wp_ajax_save_login_status', $plugin_admin, "save_login_status");
		$this->loader->add_action( 'profile_update', $plugin_admin, "update_user_profile", 10, 3 );
		$this->loader->add_filter( 'get_avatar', $plugin_admin, "filter_avatar", 10, 6);

		$this->loader->add_action( 'wp_ajax_manage_user_license', $plugin_admin, "manage_user_license");

		//$this->loader->add_action( 'admin_menu', $plugin_admin, "add_upgrade_feature",9999999);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new RCFV_Public( $this->get_firebase_chat(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_footer', $plugin_public, "embed_rao_chat");
		$this->loader->add_action( 'wp_ajax_create_anonymus_user', $plugin_public, "create_anonymus_user" );
		$this->loader->add_action( 'wp_ajax_nopriv_create_anonymus_user', $plugin_public, "create_anonymus_user" );
		$this->loader->add_action( 'wp_ajax_update_error_display', $plugin_public, "update_error_display" );
		$this->loader->add_action( 'wp_ajax_nopriv_update_error_display', $plugin_public, "update_error_display" );
		$this->loader->add_action( 'wp_ajax_check_current_user', $plugin_public, "check_current_user" );
		$this->loader->add_action( 'wp_ajax_nopriv_check_current_user', $plugin_public, "check_current_user" );
		//register routes
		$this->loader->add_action( "rest_api_init", $plugin_public, "register_routes");
		$this->loader->add_filter('kses_allowed_protocols', $plugin_public,  "add_protocols");

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_firebase_chat() {
		return $this->firebase_chat;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    RCFV_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

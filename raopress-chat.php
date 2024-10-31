<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://raoinformationtechnology.com
 * @since             1.0.0
 * @package           Raopress_Chat
 *
 * @wordpress-plugin
 * Plugin Name:       Raopress Chat - Firebase Chat for Visitors
 * Plugin URI:        
 * Description:       Frontend Chat Widget and Interact with your users in realtime from Admin Panel
 * Version:           1.3
 * Author:            RAO Information Technology
 * Author URI:        https://raoinformationtechnology.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rcfv-chat
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RCFV_VERSION', time() );
define('RCFV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RCFV_PLUGIN_URL', plugin_dir_url(__FILE__));

if ( !function_exists('write_log')) {
	function write_log ( $log )  {
	   if ( is_array( $log ) || is_object( $log ) ) {
		  error_log( print_r( $log, true ) );
	   } else {
		  error_log( $log );
	   }
	}
 }

/**
 * Autoload classes to avoid adding require_once/include_once
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rcfv-activator.php
 */
function activate_firebase_chat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rcfv-activator.php';
	RCFV_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rcfv-deactivator.php
 */
function deactivate_firebase_chat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rcfv-deactivator.php';
	RCFV_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_firebase_chat' );
register_deactivation_hook( __FILE__, 'deactivate_firebase_chat' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rcfv.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_firebase_chat() {

	$plugin = new RCFV();
	$plugin->run();

}
run_firebase_chat();

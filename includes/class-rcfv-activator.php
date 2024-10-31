<?php

/**
 * Fired during plugin activation
 *
 * @link       https://raoinformationtechnology.com
 * @since      1.0.0
 *
 * @package    RCFV
 * @subpackage RCFV/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    RCFV
 * @subpackage RCFV/includes
 * @author     raoinfotech <admin@raoinformationtechnology.com>
 */
class RCFV_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		//$this->add_firebase_chat_user_role();
	}

	/**
	 * Add Firebase Chat User Role that will have an access to Manage Chats
	 */
	public function add_firebase_chat_user_role() {
		//add_role( 'support_firebase_chats', "Firebase Chat Support", array( 'read' => true,'rao_firebase_chat'=> true ) );
	}

}

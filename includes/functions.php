<?php
function add_user_to_firebase( $user_id, $user) {
    require_once RCFV_PLUGIN_DIR.'db/firebase-db.php';
		$users = new Firebase_Users();
		$user_data = $user;
		$avatar_args = array("class"=>"wp_chatee_rao_user_avatar_default");
		$avatar = get_avatar($user_data->user_email,25,404,"",$avatar_args);
		$send_data["name"] = $user_data->display_name;
		$send_data["email"] = $user_data->user_email;
		$send_data["user_registered"] = $user_data->user_registered;
		$send_data["message"] = "";
		$send_data["photoURL"] = $avatar;
		$chat_key = $users->insert($send_data);

		update_user_meta( $user_id, 'chat_id', $chat_key);
        return $chat_key;
}

function validate_license_key( $license_key ) {
	$lciense_instance = new RCFV_License();
	$validate_response = $lciense_instance->validate_license( $license_key);
	$status = false;
	$message = "";
	if( is_wp_error( $validate_response ) ) {
		$status = fasle;
		$message = $validate_response->get_error_message();
	} else if(isset($validate_response["success"]) && !empty($validate_response["data"])) {
		if(isset($validate_response["data"])) {
			if(isset($validate_response["data"]["url"])) {
				$site_url = $validate_response["data"]["url"]; {
					if(site_url() === $site_url) {
						$status = true;
					} else {
						$status = "multiple";
						$message = "Currently license is activated on ".$site_url." It will be deactivated if you activate the license key on this domain";
					}
				}
			} else {
				$message = "Invalid License Key";
			}
		}
	} else {
		$message = "Invalid License Key";
	}
	$ret_data["status"] = $status;
	$ret_data["message"] = $message;
	return $ret_data;
}
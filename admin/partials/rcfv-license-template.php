<?php
$license_key = get_option("raopress_chat_admin_pro_license");
		 
$status = "";
if($license_key === "") {
    $button_text = __("Activate");
    $current_action = "activate";
    $button_display = true;
    $style="";
    $status = "";
    $display_notice = 'style="display:none"';
} else {
   $display_notice = "";
    $validate_license_key = validate_license_key($license_key);

    $button_display = true;
    $license_status = $validate_license_key["status"];
    if($license_status ) 
    set_transient("raopress_chat_pro_notice_display","no");
    else
    set_transient("raopress_chat_pro_notice_display","yes");
    if( $license_status === "multiple"){ 
        $button_display = false;
        $current_action = "activate";
        $status = $validate_license_key["message"];
        $button_text = __("Activate");
        $class = "notice-error";
        $style = 'style=color:red';
    } else if($license_status) {
        $button_text = __("Deactivate");
        $current_action = "deactivate";
        $status = __("Valid License");
        $class = "notice-success";
        $style = 'style=color:green';
    } else {
        $class = "notice-error";
        $button_text = __("Activate");
        $current_action = "activate";
        $status = $validate_license_key["message"];
        $style = 'style=color:red';
    }
}
?>
<div class="wrap rcfv-license">
    <div class="rcfv-notice notice <?php echo esc_attr($class);?>">
      <p><?php echo esc_attr($status);?></p>
    </div>
    <form method="post" id="manage_license">
        <input type="hidden" name="action" value="manage_user_license" />
        <input type="hidden" name="current_action" id="current_action" value="<?php echo esc_attr($current_action); ?>" />
        <?php wp_nonce_field( "manage_user_license" );?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php _e("RaoPress Chat License Key");?></th>
                    <td>
                        <input type="text" id="raopress_chat_license_key" name="license_key" value="<?php echo esc_attr($license_key);?>"/>
                    </td>
                </tr>
                <tr>
                    
                    <td colspan="2"><input type="submit" name="submit" id="raopress-license-submit" class="button button-primary" value="<?php echo esc_attr($button_text);?>"></td>
                    
                </tr>
            </tbody>
        </table>
    </form>			
</div>
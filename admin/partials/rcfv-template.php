<?php
$get_user_options = get_option("rao_firebase_user_credentials");

$acronym = "";
$is_error = get_transient('rao_login_error');
$current_user = wp_get_current_user();
if($get_user_options) {
$email = $get_user_options["email"];
$pwd = $get_user_options["pwd"];
$admin_name = get_option("rao_chat_admin_display_name");
$avatar_args = array("class"=>"wp_chatee_rao_user_avatar_default");
$acronym = get_avatar($email,25,404,"",$avatar_args);
if(!$acronym){
$acronym = "";
if($admin_name !== "") {
$words = explode(" ", $admin_name);
foreach ($words as $w) {
    $acronym .= mb_substr($w, 0, 1);
  }
} else {
    $acronym = mb_substr($email,0,1);
} 
$acronym = "<div class='wp_chatee_rao_user_avatar_default'>".$acronym."</div>";
}
} else {
    $email = $pwd = "";
}
?><input type="hidden" value="<?php echo wp_kses_post($acronym);?>" id="admin_acronym"/>
<?php
$auth_error = get_transient('rao_auth_error');
if($auth_error === "yes"){
    $settings_page = admin_url('admin.php').'?page=firebase-chat-settings';
    $auth_error_message = sprintf('Invalid Firebase Auentication! Please configure your settings <a href="%s">here</a> again',$settings_page);
    echo wp_kses_post( '<div class="notice notice-error">
                    <p>'.$auth_error_message.'</p>
                </div>' );
} else {
    $license_key = get_option("raopress_chat_admin_pro_license");
    $license_status = false;
    if($license_key) {
        $validate_license_key = validate_license_key($license_key);
        $license_status = $validate_license_key["status"]; 
    }
    if( !$license_status || $license_status === "multiple" ) {
		//$notice = add_license_status();
        ?>
      <div class="raopress-chat-upgrade-notice notice is-dismissible">
            <div class="description">
                Interact with your Customers or Subscribers in realtime using Mobile App
                <div class="raopress-upgrade-button">
                    <a href="">Upgrade</a>
                </div>  				
            </div>
        </div>
        <?php
    }   
?>
<div class="wp_chatee_rao_main_container">
    <?php
    if($email == "") {
        $admin_url = admin_url("admin-post.php");
        ?>
        <div class="wp_chatee_rao_login_register_form_screen">
            <div class="wp_chatee_rao_icon_image_area">
                <img src="<?php echo esc_url(RCFV_PLUGIN_URL); ?>images/bubble-speak.png)"/>
            </div>
            <div class="wp_chatee_rao_tab_content" id="wp_chatee_rao_register">
                <div class="wp_chatee_rao_firebase_register">
                    <div>
                        <h4><?php _e('Register Firebase Admin Account','rcfv-chat');?></h4>
                        <span class="wp_chatee_rao_register_description"><i><?php _e('Register the user under <b>Firebase Project Console / Authentication / Users','rcfv-chat');?></b></i></span>
                    </div>
                    <div class="wp_chatee_rao_register_form_container">
                        <div class="wp_chatee_rao_error_notice reg-error" style="display:none;">
                            <p><?php _e('Issue while creating the user','rcfv-chat');?></p>
                        </div>
                        <div class="wp_chatee_rao_success_notice" style="display:none;">
                            <p><?php _e("User created successfully","rcfv-chat");?></p>
                        </div>
                        <form id="register-form" action="<?php echo  esc_url($admin_url); ?>" method="POST">
                            <input type="hidden" name="action" value="rao_firebase_register">
                            <div class="wp_chatee_rao_form_group_input">
                                <label for="name"><?php _e('Name:','rcfv-chat');?></label>
                                <input type="text" class="wp_chatee_rao_form_input" name="name" id="user-name">
                            </div>
                            <div class="wp_chatee_rao_form_group_input">
                                <label for="email"><?php _e('Email address:','rcfv-chat');?></label>
                                <input type="email" class="wp_chatee_rao_form_input" name="email" id="user-email">
                            </div>
                            <div class="wp_chatee_rao_form_group_input">
                                <label for="pwd"><?php _e('Password:','rcfv-chat');?></label>
                                <input type="password" name="pwd" class="wp_chatee_rao_form_input" id="user-pwd">
                            </div>
                            <button type="submit" id="register-user" class="wp_chatee_rao_submit_button">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php
    wp_nonce_field( 'rcfv_admin_nonce' ); 
    } else if( $is_error || $pwd === "" ) {
        delete_transient("rao_login_error");
        ?>
        <div class="wp_chatee_rao_login_register_form_screen">
            <div class="wp_chatee_rao_icon_image_area">
                <img src="<?php echo esc_url(RCFV_PLUGIN_URL); ?>images/bubble-speak.png)"/>
            </div>
            <div class="wp_chatee_rao_form_login_register_area">
                <div class="wp_chatee_rao_tabs">
                    <!--<div class="wp_chatee_rao_tabs_titles">
                        <ul>
                            <li>
                                <a href="javascript:void(0);" data-tag="wp_chatee_rao_login" class="wp_chatee_rao_tab_link_active">
                                    <?php _e('Login','rcfv-chat');?>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" data-tag="wp_chatee_rao_register">
                                    <?php _e('Register','rcfv-chat');?>
                                </a>
                            </li>
                        </ul>
                    </div>-->
                    <div class="wp_chatee_rao_form_list_tab_content">
                        <div class="wp_chatee_rao_tab_content wp_chatee_rao_tab_active" id="wp_chatee_rao_login">
                            <div class="wp_chatee_rao_firebase_login">
                                <div>
                                    <h4><?php _e('Login with your Firebase Account','rcfv-chat');?></h4>
                                </div>
                                <div class="wp_chatee_rao_login_form_container">
                                    <?php //if($is_error): ?>
                                        <div class="wp_chatee_rao_error_notice login-error" style="display:none;">
                                            <p class="login-error-message"></p>
                                        </div>
                                    <?php //endif; ?>
                                    <form id="login-form">
                                        <div class="wp_chatee_rao_form_group_input">
                                            <label for="email"><?php _e('Email address:','rcfv-chat');?></label>
                                            <?php if($email === ""): ?>
                                                <input type="email" class="wp_chatee_rao_form_input" name="email" id="email">
                                            <?php else: ?>
                                            <input type="email" class="wp_chatee_rao_form_input" name="email" disabled="disabled" id="email" value="<?php echo esc_attr($email); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="wp_chatee_rao_form_group_input">
                                            <label for="pwd"><?php _e('Password:','rcfv-chat');?></label>
                                            <input type="password" name="pwd" class="wp_chatee_rao_form_input" id="pwd">
                                        </div>
                                        <div class="login-reset-actions">
                                        <button type="submit" class="wp_chatee_rao_submit_button wp_chatee_rao_login"><?php _e('Login','rcfv-chat');?></button>
                                        <a href=""  id="reset_firebase_password"><?php _e("Reset Password",'rcfv-chat') ?></a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                <script>
                    jQuery(document).ready(function(){
                        jQuery('.wp_chatee_rao_tabs_titles ul li a').click(function(){
                            jQuery('.wp_chatee_rao_tabs_titles ul li a').removeClass('wp_chatee_rao_tab_link_active');
                            jQuery(this).addClass('wp_chatee_rao_tab_link_active');
                            var tagid = jQuery(this).data('tag');
                            jQuery('.wp_chatee_rao_form_list_tab_content .wp_chatee_rao_tab_content').removeClass('wp_chatee_rao_tab_active').hide();
                            jQuery('#'+tagid).addClass('wp_chatee_rao_tab_active').fadeIn();
                        });
                    });
                </script>
            </div>
        </div>  
        <?php wp_nonce_field( 'rcfv_admin_nonce' ); ?>
    <?php
    } else {
    ?>  
    <div class="wp_chatee_rao_chat_container">
        <div class="wp_chatee_rao_left_side_user_list wp_chatee_rao_show" id="side-1">
            <div class="wp_chatee_rao_left_side_user_list_header">
                <span class="wp_chatee_rao_hide_user_list_mobile" onclick="hideShowChatList()">
                    <i class="fas fa-arrow-left"></i>
                </span>
                
                <?php
                    
                    $user_email = $email;
                    $userID = $current_user->ID;
                    $name = $current_user->display_name;
                    $current_user_chat_key = get_user_meta( $current_user->ID, 'chat_id' , true );
                    if(is_array($current_user_chat_key)) {
                        $current_user_chat_key = $current_user_chat_key[0];
                    }                   
                ?>
                <div class="wp_chatee_rao_now_logedin">
                    <?php echo wp_kses_post($acronym); ?>
                    <p><?php echo esc_html($name).'<span>'.esc_html($user_email).'</span>';?></p>
                </div>
                <!-- <a href="#" data-toggle="modal" data-target="#modalNotificationList" onclick="PopulateNotifications()">
                    <i class="fas fa-bell icon"></i><span id="notification">0</span>
                </a> -->
                <!-- <div class="modal fade" id="modalNotificationList">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="card">
                                <div class="card-header">
                                    <?php _e('All Users Requests','rcfv-chat');?>
                                    <span class="close" data-dismiss="modal" style="cursor:pointer;">&times;</span>
                                </div>
                                <ul class="list-group list-group-flush" id="lstNotification"></ul>
                            </div>
                        </div>
                    </div>
                </div> -->
                <!-- search bara 
                <div  class="wp_chatee_rao_message_search" id="wp_chatee_rao_message_search">                 
                    <div class="card">
                        <div class="mt-3 inputs">
                            <i class="fa fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search Chat...">                                    
                        </div>
                    </div>                     
                </div> -->
                <span class="wp_chatee_rao_search_icon">
                    <i class="fas fa-search"></i>
                </span>
                
                <span class="wp_chatee_rao_dropdown_menu_options">
                    <i class="fas fa-ellipsis-v"></i>
                </span>
                <div class="wp_chatee_rao_dropdown_menu_toggle" style="display: none;">
                   <!-- <a href="javascript:void(0);" id="lnkNewChat" onclick="PopulateFriendList()">New Chat</a> -->
                    <a href="javascript:void(0);" id="lnkSignOut" onclick="signOut()">Logout</a>
                </div>
            </div>
            <div class="wp_chatee_rao_search_toggle" style="display: none;">
                    <div class="card">
                        <div class="mt-3 inputs">
                            <i class="fa fa-search"></i>
                            <input type="text" id="wp_chatee_rao_search" class="form-control" placeholder="Search Users...">                                    
                        </div>
                    </div>    
                </div>
                
            <ul class="wp_chatee_rao_chat_history" id="wp_chatee_rao_chat_list"></ul>
        </div>
        <div class="wp_chatee_rao_right_side_user_list wp_chatee_rao_hide" id="side-2">
            <div id="wp_chatee_rao_chat_panel" style="display: none;">
                <div id="chatPanel" class="wp_chatee_rao_right_side_user_header">
                    <span class="wp_chatee_rao_show_user_list_mobile" onclick="hideShowChatList()">
                        <i class="fas fa-list"></i>
                    </span>
                    
                    <div class="wp_chatee_rao_open_chat_details">
                        <div id="wp_chatee_rao_chat_with_user_avatar"></div>
                        <p id="wp_chatee_rao_chat_with_user"></p>
                    </div>
                    <span class="wp_chatee_rao_attach_files">
                        <i class="fas fa-paperclip"></i>
                    </span>
                    <div class="wp_chatee_rao_dropdown_attach_toggle" style="display: none;">
                        <a href="javascript:void(0);" onclick="ChooseImage()">
                            <?php _e('Image','rcfv-chat');?>
                            <input type="file" accept="image/jpeg,image/gif,image/png,image/x-eps" id="imageFile" onchange="SendImage(this);" accept="image/*" style="display:none;" />
                        </a>
                        <a href="javascript:void(0);" onclick="document.getElementById('photo').click()">
                            <?php _e('Attach File','rcfv-chat');?>
                            <input type="file" accept="application/pdf" onchange="uploadFile()" id="photo" name="files[]" style="visibility:hidden;width:0px;font-size: 1px;overflow: hidden;"/>
                        </a>
                    </div>
                    
                </div>
                <!-- search bara 
                <div  class="wp_chatee_rao_message_search" id="wp_chatee_rao_message_search">                 
                    <div class="card">
                        <div class="mt-3 inputs">
                            <i class="fa fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search Chat...">                                    
                        </div>
                    </div>                     
                </div> -->

                <div class="wp_chatee_rao_message_history" id="wp_chatee_rao_message_list">
                </div>
                <div class="wp_chatee_rao_chat_footer">
                    <div class="wp_chatee_rao_chat_footer_container" style="position:relative;">
                        <div id="wp_chatee_rao_send_emoji_panel" style="display:none;">
                            <div class="wp_chatee_rao_send_emoji_tabs_titles">
                                <ul>
                                    <li>
                                        <a href="javascript:void(0);" data-tag="wp_chatee_rao_smiley" class="wp_chatee_rao_tab_link_active">
                                            Smiley
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="wp_chatee_rao_emoji_list_tab_content">
                                <div class="wp_chatee_rao_tab_content wp_chatee_rao_tab_active" id="wp_chatee_rao_smiley">
                                </div>
                            </div>
                        </div>
                        <script>
                            jQuery(document).ready(function(){
                                jQuery('.wp_chatee_rao_send_emoji_tabs_titles ul li a').click(function(){
                                    jQuery('.wp_chatee_rao_send_emoji_tabs_titles ul li a').removeClass('wp_chatee_rao_tab_link_active');
                                    jQuery(this).addClass('wp_chatee_rao_tab_link_active');
                                    var tagid = jQuery(this).data('tag');
                                    jQuery('.wp_chatee_rao_emoji_list_tab_content .wp_chatee_rao_tab_content').removeClass('wp_chatee_rao_tab_active').hide();
                                    jQuery('#'+tagid).addClass('wp_chatee_rao_tab_active').fadeIn();
                                });
                            });
                        </script>
                        <div class="wp_chatee_rao_chat_footer_send">
                            <span onclick="showEmojiPanel()">
                                <i class="far fa-grin"></i>
                            </span>
                            <div class="wp_chatee_rao_send_message_box">
                                <!--<input id="txtMessage" onkeyup="ChangeSendIcon(this)" type="text" onfocus="hideEmojiPanel()" placeholder="Type here" class="wp_chatee_rao_form_input">-->
                                <!--Adjusted by himani: commented above line and removed onkeyup event -->
                                <input id="txtMessage" class="wp_chatee_rao_form_input" type="text" onfocus="hideEmojiPanel()">
                                <!--<div id="txtMessage" contenteditable="true" placeholder="Type here" onfocus="hideEmojiPanel()" ></div>-->

                            </div>
                            <!--Adjusted by Himani
                                <span id="wp_chatee_rao_audio" onclick="record(this)">
                                <i class="fas fa-microphone"></i>
                            </span>-->
                            <span id="wp_chatee_rao_send">
                                <i class="fa fa-paper-plane"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div id="wp_chatee_rao_start_chat_screen">
                <img src="<?php echo esc_url(RCFV_PLUGIN_URL."images/bubble-speak.png"); ?>">
                <div id="login_div" class="main-div" style="display: none;">
                    <table class="table">
                        <tr><td>

                        </td><td>
                            <div class="col-md-12 text-center">
                                <?php
                                $user_email = $email;
                                $userID = $current_user->ID;
                                $name = $current_user->display_name;
                                $current_user_chat_key = get_user_meta( $current_user->ID, 'chat_id' , true );
                                if(is_array($current_user_chat_key)) {
                                    $current_user_chat_key = $current_user_chat_key[0];
                                }
                                ?>
                                <input type="hidden" placeholder="Role" id="current_role" class="tbxset"  value="admin" />
                                <input type="hidden" placeholder="Role" id="current_role_id" class="tbxset"  value="<?php echo  esc_attr($current_user_chat_key); ?>" />
                                <input type="hidden" placeholder="UserId" id="current_id" class="tbxset"  value="1" />
                                <input type="text" placeholder="Enter your email" id="email_field" class="tbxset"  value="<?php echo esc_attr($user_email);//echo  $current_user->user_email;  ?>" />
                                <input type="hidden" placeholder="Enter your email" id="name_field" class="tbxset"  value="<?php echo esc_attr($name);//echo  $current_user->user_email;  ?>" />
                                <input type="hidden" placeholder="Enter your password" id="password_field" class="tbxset" value="<?php echo esc_attr($pwd); //echo  $current_user->user_email; ?>" />

                                <?php  ?>
                            </div>
                        </td></tr>
                    </table>    
                </div>
                <div id="user_div" class="loggedin-div text-center" style="display: none;">
                    <p id="user_para"></p>
                    <button onclick="signOut('')" class="logoutbtn"><?php _e('Logout','rcfv-chat');?></button>
                </div>
            </div>
        </div>
    </div>
</div>

	<?php
    
}
} ?>
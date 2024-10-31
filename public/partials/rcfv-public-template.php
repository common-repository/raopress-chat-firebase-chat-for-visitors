<?php
$user = wp_get_current_user();

$rao_admin_name = get_option('rao_chat_admin_display_name');
?>
<div class="fabs" style="display:none;">
    <div class="chat">
        <div class="chat_header" style="background:<?php echo esc_attr($theme_color);?>">
            <div class="chat_option">
                <div class="header_image">
                    <span id="chat_head">
                        <?php 
                            if(!$rao_admin_name) {
                                echo __("Hey, there!","rcfv-chat");
                            } else {
                                echo wp_kses_post(sprintf(__("Hey, %s here","rcfv-chat"), $rao_admin_name));
                            }
                        ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="chat_body chat_login">
            <div class="formContainer">
                <span class="login-heading"><?php _e('Enter details to Initialize Chat','rcfv-chat');?></span>
                <form id="annonymus_chat">
                    <input type="text" class="chat_input" id="user_name" name="user_name" placeholder="Your Name" onfocus="this.placeholder=''" onblur="this.placeholder='Your Name'"/>
                    <input type="text" class="chat_input" id="user_email" name="user_email" placeholder="Your Email" onfocus="this.placeholder=''" onblur="this.placeholder='Your Email'"/>
                    <?php wp_nonce_field( 'anonymus_login_nonce' ); ?>
                    <div class="annonymus_button_section">
                    <span><input type="submit" id="anonymus_submit" value="Start Chat" style="background:<?php echo esc_attr($theme_color)?>"/></span>
                    </div>
                </form>
            </div>
			
			
           
        </div>
		<div id="pageloader" class="chat_body">
            <img src="<?php echo RCFV_PLUGIN_URL."images/anonymloader.gif";?>" alt="processing..." />
        </div>
        <div id="chat_converse" style="max-height:300px;" class="chat_body chat_conversion chat_converse">
        </div>

        <div class="fab_field chat_bottom">
            <?php if($attach_enabled):?>
            <span class="rao-attachment"><a id="fab_attachment" class="fab is-visible" style="background:<?php echo esc_attr($theme_color);?>"><i class="zmdi"><img width="24px" height="24px" src="<?php echo esc_url(RCFV_PLUGIN_URL."images/attach.png");?>"/></i></a></span>
            <?php endif; ?>
			<span class="rao-send-input"><textarea type="text" id="chatSend" name="chat_message" placeholder="Send a message" class="chat_field chat_message"></textarea></span>
            <span class="rao-send-icon"><a id="fab_send" class="fab is-visible" style="background:<?php echo  esc_attr($theme_color);?>"><i class="zmdi"><img width="24px" height="24px" src="<?php echo esc_url(RCFV_PLUGIN_URL."images/send.png");?>"/></i></a></span>
        </div>
    </div>
    <a id="prime" class="rao-firechatee fab" style="background:<?php echo esc_attr($theme_color);?>">
        <i class="prime zmdi zmdi-chat-open"><img width="24px" height="24px" src="<?php echo esc_url(RCFV_PLUGIN_URL."images/chat-icon.png");?>"/></i>
    </a>
</div>
<input type="file" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps" id="imageFile" onchange="SendImage(this);" style="display:none;" />
<input type="hidden" placeholder="Role" id="current_role" class="tbxset"  value="<?php echo  'admin'; ?>" />
     <input type="hidden" placeholder="Role" id="current_role_id" class="tbxset"  value="" />
	<input type="hidden" placeholder="UserId" id="current_id" class="tbxset"  value="" />
    <input type="hidden" placeholder="UserId" id="current_admin_id" class="tbxset"  value="<?php echo  esc_attr($chat_admin_id); ?>" />
<style>
    .chat .chat_converse .chat_msg_item.chat_msg_item_user {
        background: <?php echo esc_attr($theme_color); ?>
    }
</style>
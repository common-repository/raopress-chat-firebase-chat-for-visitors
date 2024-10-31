<?php
namespace RCFV\Admin\Settings;

class RCFV_Settings {
    
    private $locale = "rcfv-chat";

    public function __construct() {
        add_action('init', array($this, "load_menu"));
    }

    public function load_menu() {
        
        
        $customWPMenu = new RCFV_Menu( array(
            'slug' => 'firebase-chat',
            'title' => __( 'Raopress Chat', $this->locale ),
            'desc' => __( 'Raopress Chat Dashboard', $this->locale ),
            'icon' => 'dashicons-welcome-widgets-menus',
            'function'  => 'render_chatboat_page',
            'capability' => 'rao_firebase_chat_access',
			'position' => 99,

        ) );

        $settingsMenu = new RCFV_SubMenu(
            array(
                'slug' => 'firebase-chat-settings',
                'title'=>   __('Settings', $this->locale),
                'desc'  =>  __('Firebase Chat Settings', $this->locale),
                
            ),
            $customWPMenu
        );

        $settingsMenu->add_field(
            array(
                'name'  =>  'firebase_section',
                'title' =>  '<h2>'.__('Configure Firebase',$this->locale).'</h2>',
                'desc'  =>  '<p></p>',
                'type'  =>  'heading'
            )
        );
        //__('Property keys & values must be doublequotted.<br/><b>For eg:</b> "apiKey": "AIzaSyB8wpcocF9wo0Sy5PhcgbIVgNMRrMHyDZc"', $this->locale)
        $settingsMenu->add_field(array(
            'name' => 'firebase_app_config',
            'title' => __('Firebase APP Configuration', $this->locale),
            'desc' => 'You can find the Firebase Aoo Config under Project Overview->Project Settings->SDK setup and configuration->Config',
            'type' => "textarea"
        ));
        $firebase_db = "Follow the steps mentioned ".'<a href="https://cloud.google.com/iam/docs/creating-managing-service-account-keys" tatget="_blank">here</a> to retrieve DB objext.';
        $settingsMenu->add_field(array(
            'name' => 'firebase_db_config',
            'title' => __('Firebase DB Configuration', $this->locale),
            'desc' => $firebase_db,
            'type' => "textarea"
        ));


        $settingsMenu->add_field(
            array(
                'name'  =>  'firebase_user_section',
                'title' =>  '<h2>'.__('Privacy',$this->locale).'</h2>',
                'desc'  =>  '<p></p>',
                'type'  =>  'heading'
            )
        );

        
        global $wp_roles;

        $all_roles = $wp_roles->roles;
        $roles_array = array();
        $discard_roles = array("subscriber","customer","contributor");
        foreach($all_roles as $role_id => $role_data) {
            if(in_array($role_id,$discard_roles))
            continue;
            $roles_array[$role_id] = $role_data['name'];
        }
        $settingsMenu->add_field(array(
            'name' => 'firebase_user_roles',
            'title' => __('Provide Firebase Backend Chat Access to Specific Roles Only', $this->locale),
            'type' => "select",
            'attr' => "multiple",
            'options' => $roles_array
        ));

        //Load Frontend Settings
        $this->render_frontend_settings_tab( $settingsMenu );
        $this->render_frontend_license_tab( $settingsMenu );


        
    }

    public function render_frontend_license_tab( $settingsMenu ) {
        $customTab = new RCFV_Tab(
            array(
                'slug'  =>  "raopress_chat_pro_license",
                "title" => __("Pro License", $this->locale)
            ),
            $settingsMenu

        );
    }

    public function render_frontend_settings_tab( $settingsMenu ) {
        $customTab = new RCFV_Tab(
            array(
                'slug'  =>  "frontend_chat_widget_settings",
                "title" => __("Frentend Chat Widget Settings", $this->locale)
            ),
            $settingsMenu

        );

        $customTab->add_field(
            array(
                'name'  =>  'enable_disable_chat_widget',
                "title" => __("Enable/Disable Chat Widget", $this->locale),
                "desc"  =>  __("On checked, chat widget would appear on the frontend of your website"),
                'type'  =>  "checkbox",
            )
        );

        $customTab->add_field(
            array(
                "name"  =>  "welcome_message",
                "title" =>  __("Welcome Message", $this->locale),
                "desc"  => __("Configure a welcome message for your website visitors. This message will appear once chat widget is opened.", $this->locale),
                "type"      => "textarea",
                "default"   =>  __("How can I help you?", $this->locale) 
            )
        );

        $customTab->add_field(
            array(
                'name'  =>  'widget_theme_color',
                'type'  =>  "text",
                "default" => "#42a5f5",
                'title' =>  __("Choose Chat widget theme color", $this->locale),
            )
        );

        $customTab->add_field(
            array(
                'name'  =>  'enable_disable_attach',
                "title" => __("Allow Attachment from User", $this->locale),
                "desc"  =>  __("On checked, User will be able to send attachments", $this->locale),
                'type'  =>  "checkbox"
            )
        );

        $customTab->add_field(
            array(
                'name'  =>  'enable_disable_prompt',
                "title" => __("Display Prompt on First Visit", $this->locale),
                "desc"  =>  __("On checked, a chat widget will be opened automatically", $this->locale),
                'type'  =>  "checkbox"
            )
        );
    }

}
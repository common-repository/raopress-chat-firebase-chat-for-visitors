<?php
namespace RCFV\Admin\Settings;
abstract class WordPressSettings {

    /**
     * ID of the Settings
     * @var string
     */
    public $settings_id = "";

    /**
	 * Tabs for the settings page
	 * @var array
	 */
	public $tabs = array( 
		'general' => 'General' );

	/**
	 * Settings from database
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Array of fields for the general tab
	 * array(
	 * 	'tab_slug' => array(
	 * 		'field_name' => array(),
	 * 		),
	 * 	)
	 * @var array
	 */
	protected $fields = array();

	/** 
	 * Data gotten from POST
	 * @var array
	 */
	protected $posted_data = array();

     /**
	 * Get the settings from the database
	 * @return void 
	 */
	public function init_settings() {

		$this->settings = (array) get_option( $this->settings_id );

		foreach ( $this->fields as $tab_key => $tab ) {
			
			foreach ( $tab as $name => $field ) {
				
				if( isset( $this->settings[ $name ] ) ) {
					$this->fields[ $tab_key ][ $name ]['default'] = $this->settings[ $name ];
				}	
			
			}

		}

	}

	/**
	 * Save settings from POST
	 * @return [type] [description]
	 */
	public function save_settings(){
		
	 	$this->posted_data = $_POST;

		$current_tab = isset($_GET["tab"]) ? sanitize_text_field(wp_unslash($_GET["tab"])) : "general";

	 	if( empty( $this->settings ) ) {

	 		$this->init_settings();

	 	}
		
	 	foreach ($this->fields as $tab => $tab_data ) {
			if( $tab !== $current_tab )
			continue;
	 		foreach ($tab_data as $name => $field) {
	 			if($field['type'] === "heading")
                continue;

	 			$this->settings[ $name ] = $this->{ 'validate_' . $field['type'] }( $name );
	 	
	 		}

	 	}
	 	update_option( $this->settings_id, $this->settings );	

	}

	/**
	 * Gets and option from the settings API, using defaults if necessary to prevent undefined notices.
	 *
	 * @param  string $key
	 * @param  mixed  $empty_value
	 * @return mixed  The value specified for the option or a default value for the option.
	 */
	public function get_option( $key, $empty_value = null ) {

		if ( empty( $this->settings ) ) {
			$this->init_settings();
		}

		// Get option default if unset.
		if ( ! isset( $this->settings[ $key ] ) ) {

			$form_fields = $this->fields;

			foreach ( $this->tabs as $tab_key => $tab_title ) {

				if( isset( $form_fields[ $tab_key ][ $key ] ) ) {

					$this->settings[ $key ] = isset( $form_fields[ $tab_key ][ $key ]['default'] ) ? $form_fields[ $tab_key ][ $key ]['default'] : '';
				
				}

			}
			
		}

		if ( ! is_null( $empty_value ) && empty( $this->settings[ $key ] ) && '' === $this->settings[ $key ] ) {
			$this->settings[ $key ] = $empty_value;
		}

		return $this->settings[ $key ];
	}

    /**
	 * Validate text field
	 * @param  string $key name of the field
	 * @return string     
	 */
	public function validate_text( $key ){
		$text  = $this->get_option( $key );
		
		if ( isset( $this->posted_data[ $key ] ) ) {
			$text = wp_kses_post( trim( stripslashes( $this->posted_data[ $key ] ) ) );
		}

		return $text;
	}

	/**
	 * Validate textarea field
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_textarea( $key ){
		$text  = $this->get_option( $key );
		
		if ( isset( $this->posted_data[ $key ] ) ) {

			$text = wp_kses( trim( stripslashes( $this->posted_data[ $key ] ) ),
				array_merge(
					array(
						'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
					),
					wp_kses_allowed_html( 'post' )
				)
			);
		}

		if( $key === "firebase_app_config" || $key === "firebase_db_config" ) {

			$firebase_credentials = json_decode($text, true);

			if(!$firebase_credentials) {
				$text = "";
				if($key === "firebase_app_config")
				set_transient("rao_firebase_app_config_error", "yes" );
				if($key === "firebase_db_config")
				set_transient("rao_firebase_db_config_error", "yes" );
				return $text;
			}
			$error_present_app = get_transient("rao_firebase_app_config_error");
			$error_present_db = get_transient("rao_firebase_app_config_error");
			if($error_present_app !== "yes" && $error_present_db !== "yes") {
			
			$app_object = $this->posted_data["firebase_app_config"];
			$app_object = wp_kses( trim( stripslashes( $app_object ) ),
				array_merge(
					array(
						'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
					),
					wp_kses_allowed_html( 'post' )
				)
			);
			
			$app_object1 = json_decode($app_object, true);

			$db_object = $this->posted_data["firebase_db_config"];
			$db_object = wp_kses( trim( stripslashes( $db_object ) ),
				array_merge(
					array(
						'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
					),
					wp_kses_allowed_html( 'post' )
				)
			);
			$db_object1 = json_decode($db_object, true);

			if(is_array($app_object1) && is_array($db_object1)) {
				if(!isset($app_object1["projectId"]) || !isset($db_object1["project_id"]))
				set_transient("rao_firebase_global_error","yes");
				else if($app_object1["projectId"] !== $db_object1["project_id"])
				set_transient("rao_firebase_global_error","yes");
				else
				$delete = delete_transient("rao_firebase_global_error");
				//$text = "";
				//$text = "";
			}
			}
		}

		return $text;
	}

	/**
	 * Validate WPEditor field
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_wpeditor( $key ){
		$text  = $this->get_option( $key );
		 
		if ( isset( $this->posted_data[ $key ] ) ) {

			$text = wp_kses( trim( stripslashes( $this->posted_data[ $key ] ) ),
				array_merge(
					array(
						'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
					),
					wp_kses_allowed_html( 'post' )
				)
			);
		}

		return $text;
	}

	/**
	 * Validate select field
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_select( $key ) {

		$value = $this->get_option( $key );
		if ( isset( $this->posted_data[ $key ] ) ) {
			if(is_array($this->posted_data[ $key ]))
			$value = $this->posted_data[ $key ];
			else
			$value = stripslashes( $this->posted_data[ $key ] );
		}

		return $value;
	}

	/**
	 * Validate radio
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_radio( $key ) {

		$value = $this->get_option( $key );

		if ( isset( $this->posted_data[ $key ] ) ) {
			$value = stripslashes( $this->posted_data[ $key ] );
		}

		return $value;
	}

	/**
	 * Validate checkbox field
	 * @param  string $key name of the field
	 * @return string      
	 */
	public function validate_checkbox( $key ) {

		$status = '';
		if ( isset( $this->posted_data[ $key ] ) && ( 1 == $this->posted_data[ $key ] ) ) {
			$status = '1';
		}

		return $status;
	}

    /**
	 * Adding fields 
	 * @param array $array options for the field to add
	 * @param string $tab tab for which the field is
	 */
	public function add_field( $array, $tab = 'general' ) {
		$allowed_field_types = array(
            'heading',
			'text',
			'textarea',
			'wpeditor',
			'select',
			'radio',
			'checkbox' );

		// If a type is set that is now allowed, don't add the field
		if( isset( $array['type'] ) &&$array['type'] != '' && ! in_array( $array['type'], $allowed_field_types ) ){
			return;
		}

		$defaults = array(
			'name' => '',
			'title' => '',
			'default' => '',
			'placeholder' => '',
			'type' => 'text',
			'options' => array(),
			'desc' => '',
			);

		$array = array_merge( $defaults, $array );

		if( $array['name'] == '' ) {
			return;
		}

		foreach ( $this->fields as $tabs ) {
			if( isset( $tabs[ $array['name'] ] ) ) {
				trigger_error( 'There is alreay a field with name ' . $array['name'] );
				return;
			}
		}

		// If there are options set, then use the first option as a default value
		if( ! empty( $array['options'] ) && $array['default'] == '' ) {
			$array_keys = array_keys( $array['options'] );
			$array['default'] = $array_keys[0];
		}

		if( ! isset( $this->fields[ $tab ] ) ) {
			$this->fields[ $tab ] = array();
		}

		$this->fields[ $tab ][ $array['name'] ] = $array;

	}
	
	/**
	 * Adding tab
	 * @param array $array options
	 */
	public function add_tab( $array ) {

		$defaults = array(
			'slug' => '',
			'title' => '' );

		$array = array_merge( $defaults, $array );

		if( $array['slug'] == '' || $array['title'] == '' ){
			return;
		}

		$this->tabs[ $array['slug'] ] = $array['title'];
	}

     /**
	 * Rendering fields 
	 * @param  string $tab slug of tab
	 * @return void  
	 */
	public function render_fields( $tab ) {
		if($tab !== "raopress_chat_pro_license") {
		if( ! isset( $this->fields[ $tab ] ) ) {

			echo wp_kses_post('<p>' . __( 'There are no settings on these page.', 'rcfv-chat' ) . '</p>');
			return;
		}

		//check for error
		$app_config_error = get_transient("rao_firebase_app_config_error");
		$db_config_error = get_transient("rao_firebase_db_config_error");
		$error_message = $success_message = "";
		if($app_config_error === "yes" && $db_config_error === "yes") {
			$error_message = __("Please save the config fields with a valid JSON format","rcfv");
		} else if($app_config_error === "yes") {
			$error_message = __("Please save the Firebase App config field with a valid JSON format","rcfv");
		} else if($db_config_error === "yes") {
			$error_message = __("Please save the Firebase DB config field with a valid JSON format","rcfv");
		} else if(isset($_POST["firebase-chat-settings_save"])) {
			$success_message = __("Settings Saved Successfully","rcfv");
		}

		$global_error = get_transient("rao_firebase_global_error");
		if("yes" ===$global_error)
		$error_message = __("Firebase DB project ID does not comply with APP config object","rcfv");

		$delete = delete_transient("rao_firebase_app_config_error");
		$delete = delete_transient("rao_firebase_db_config_error");
		//$delete = delete_transient("rao_firebase_global_error");

		if($error_message !== "") 
		{
			echo wp_kses_post('<div class="notice notice-error"><p>'.esc_html($error_message).'</p></div>');
		}

		if($success_message !== "") 
		{
			echo wp_kses_post('<div class="notice notice-success"><p>'.esc_html($success_message).'</p></div>');
		}

		foreach ( $this->fields[ $tab ] as $name => $field ) {
			
			$this->{ 'render_' . $field['type'] }( $field );

		}
		} else {
			echo wp_kses_post('<div class="license-template">');
			$path = RCFV_PLUGIN_DIR."admin/partials/rcfv-license-template.php";
			include_once $path;
			echo '</div>';
		}
	}

    /**
	 * Render heading field
	 * @param  string $field options
	 * @return void     
	 */
	public function render_heading( $field ){

		extract( $field );
		?>

		<tr>
			<th colspan="2">
				<?php echo wp_kses_post($title); ?>
                <?php if( $desc != '' ) {
					echo  wp_kses_post( sprintf(__('<p class="description">%s</p>',"rcfv-chat"), $desc));
				}?>
			</th>
		</tr>

		<?php
	}

    /**
	 * Render text field
	 * @param  string $field options
	 * @return void     
	 */
	public function render_text( $field ){

		extract( $field );
		if($field["name"] === "widget_theme_color") {
			if($field["default"] == "")
			$default = "#42a5f5";
		}
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo wp_kses_post($title); ?></label>
			</th>
			<td>
				<input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($default); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" />	
				<?php if( $desc != '' ) {
					echo wp_kses_post(sprintf(__('<p class="description">%s</p>','rcfv-chat'),$desc));
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render textarea field
	 * @param  string $field options
	 * @return void      
	 */
	public function render_textarea( $field ){

		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<textarea name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" ><?php echo esc_attr($default); ?></textarea>	
				<?php if( $desc != '' ) {
					echo wp_kses_post(sprintf(__('<p class="description">%s</p>','rcfv-chat'),$desc));
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render WPEditor field
	 * @param  string $field  options
	 * @return void      
	 */
	public function render_wpeditor( $field ){
		
		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<?php wp_editor( $default, $name, array('wpautop' => false) ); ?>
				<?php if( $desc != '' ) {
					echo wp_kses_post('<p class="description">' . esc_attr( $desc ) . '</p>');
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render select field
	 * @param  string $field options
	 * @return void      
	 */
	public function render_select( $field ) {

		extract( $field );
		$attr_text = "";
		if($attr === "multiple") {
			$attr_text = 'multiple="multiple"';
		} 
		
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<select name="<?php echo esc_attr($name); ?>[]" <?php echo esc_attr($attr_text);?> id="<?php echo esc_attr($name); ?>" >
					<?php 
					if(!$default)
					$default  = array();
					if($default === "administrator")
					$default = array("administrator");
						foreach ($options as $value => $text) {
							if(in_array($value, $default))
							$selected = 'selected="selected"';
							else
							$selected = "";
							echo '<option ' . esc_attr($selected) . ' value="' . esc_attr($value) . '">' . esc_attr($text) . '</option>';
						}
					?>
				</select>
				<?php if( $desc != '' ) {
					sprintf(__('<p class="description">%s</p>'),$desc);
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render radio
	 * @param  string $field options
	 * @return void      
	 */
	public function render_radio( $field ) {

		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<?php 
					foreach ($options as $value => $text) {
						echo '<input name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" type="'.  esc_attr( $type ) . '" ' . checked( $default, $value, false ) . ' value="' . esc_attr( $value ) . '">' . esc_attr( $text ) . '</option><br/>';
					}
				?>
				<?php if( $desc != '' ) {
					echo wp_kses_post(sprintf( __('<p class="description">%s</p>','rcfv-chat'), $desc ));
				}?>
			</td>
		</tr>

		<?php
	}

	/**
	 * Render checkbox field
	 * @param  string $field options
	 * @return void      
	 */
	public function render_checkbox( $field ) {

		extract( $field );
		?>

		<tr>
			<th>
				<label for="<?php echo esc_attr($name); ?>"><?php echo esc_attr($title); ?></label>
			</th>
			<td>
				<input <?php checked( $default, '1', true ); ?> type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="1" placeholder="<?php echo esc_attr($placeholder); ?>" />
				<?php echo esc_attr($desc); ?>
			</td>
		</tr>

		<?php
	}
}
<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class CCP_Mentor_Plugin_Settings {

	/**
	 * The single instance of CCP_Mentor_Plugin_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public $customers_obj;

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'mentor_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		
		$page = add_options_page( __( 'CCP Plugin Settings', 'ccp-mentor-plugin' ) , __( 'Mentor Program', 'ccp-mentor-plugin' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		// add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );

		// new customers stuff
		 // $this->customers_obj = new Customers_List();

		$hook = add_menu_page(
			'Mentees List - Approved',
			'Mentees List - Approved',
			'manage_options',
			'wp_list_table_class',
			[ $this, 'plugin_settings_page' ]
		);

		$hook2 = add_menu_page(
			'Mentees List - Pending',
			'Mentees List - Pending',
			'manage_options',
			'wp_list_table_class2',
			[ $this, 'plugin_settings_page' ]
		);
		
		add_action( "load-$hook", [ $this, 'screen_option' ] );
		add_action( "load-$hook2", [ $this, 'screen_option' ] );
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );

		
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}
/** FROM PS **/
	public function screen_option() {
		$option = 'per_page';
		$args   = [
			'label'   => 'Customers',
			'default' => 5,
			'option'  => 'customers_per_page'
		];
		add_screen_option( $option, $args );
		$this->customers_obj = new Customers_List();
	}

/** FROM PS **/
	public function plugin_settings_page() {
		$ret = '<div class="wrap"><h2>Mentees</h2><div id="poststuff"><div id="post-body" class="metabox-holder columns-2"><div id="post-body-content">' 
		     . '<div class="meta-box-sortables ui-sortable"><form method="post">' . "\n"
			 . $this->customers_obj->prepare_items() . "\n" 
			 . $this->customers_obj->display() 
			 . '</form></div></div></div><br class="clear"></div></div>';
	    return $ret;
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'ccp-mentor-plugin' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['confi'] = array(
			'title'					=> __( 'Config', 'ccp-mentor-plugin' ),
			'description'			=> __( 'These are fairly standard form input fields.', 'ccp-mentor-plugin' ),
			'fields'				=> array(
				array(
					'id' 			=> 'program_name',
					'label'			=> __( 'Mentoring Program Name' , 'ccp-mentor-plugin' ),
					'description'	=> __( 'Used in emails etc..', 'ccp-mentor-plugin' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'ccp-mentor-plugin' )
				),
				array(
					'id' 			=> 'password_field',
					'label'			=> __( 'A Password' , 'ccp-mentor-plugin' ),
					'description'	=> __( 'This is a standard password field.', 'ccp-mentor-plugin' ),
					'type'			=> 'password',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'ccp-mentor-plugin' )
				),
				array(
					'id' 			=> 'secret_text_field',
					'label'			=> __( 'Some Secret Text' , 'ccp-mentor-plugin' ),
					'description'	=> __( 'This is a secret text field - any data saved here will not be displayed after the page has reloaded, but it will be saved.', 'ccp-mentor-plugin' ),
					'type'			=> 'text_secret',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text', 'ccp-mentor-plugin' )
				),
				array(
					'id' 			=> 'text_block',
					'label'			=> __( 'A Text Block' , 'ccp-mentor-plugin' ),
					'description'	=> __( 'This is a standard text area.', 'ccp-mentor-plugin' ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( 'Placeholder text for this textarea', 'ccp-mentor-plugin' )
				),
				array(
					'id' 			=> 'abn_check_auto',
					'label'			=> __( 'Auto ABN Validation', 'ccp-mentor-plugin' ),
					'description'	=> __( 'Automatically validate new form submission ABN details.', 'ccp-mentor-plugin' ),
					'type'			=> 'checkbox',
					'default'		=> ''
				),
				array(
					'id' 			=> 'select_box',
					'label'			=> __( 'Form Handler', 'ccp-mentor-plugin' ),
					'description'	=> __( 'Component used to render and capture form submissions.', 'ccp-mentor-plugin' ),
					'type'			=> 'select',
					'options'		=> array( 'wp-native' => 'Wordpress', 'formio' => 'Form.IO', 'external' => 'External CGI or API Integration' ),
					'default'		=> 'wordpress'
				),
				array(
					'id' 			=> 'radio_buttons',
					'label'			=> __( 'Some Options', 'ccp-mentor-plugin' ),
					'description'	=> __( 'A standard set of radio buttons.', 'ccp-mentor-plugin' ),
					'type'			=> 'radio',
					'options'		=> array( 'superman' => 'Superman', 'batman' => 'Batman', 'ironman' => 'Iron Man' ),
					'default'		=> 'batman'
				),
				array(
					'id' 			=> 'multiple_checkboxes',
					'label'			=> __( 'Some Items', 'ccp-mentor-plugin' ),
					'description'	=> __( 'You can select multiple items and they will be stored as an array.', 'ccp-mentor-plugin' ),
					'type'			=> 'checkbox_multi',
					'options'		=> array( 'square' => 'Square', 'circle' => 'Circle', 'rectangle' => 'Rectangle', 'triangle' => 'Triangle' ),
					'default'		=> array( 'circle', 'triangle' )
				)
			)
		);


		$settings['mentor'] = array(
			'title'					=> __( 'Mentors', 'ccp-mentor-plugin' ),
			'description'			=> __( 'Here are the registered Mentors for our program.', 'ccp-mentor-plugin' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'ccp-mentor-plugin' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'ccp-mentor-plugin' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'ccp-mentor-plugin' )
				)
			)
		);
		$settings['mentee'] = array(
			'title'					=> __( 'Mentees', 'ccp-mentor-plugin' ),
			'description'			=> __( 'These are some extra input fields that maybe aren\'t as common as the others.', 'ccp-mentor-plugin' ),
			'fields'				=> array(
				array(
					'id' 			=> 'number_field',
					'label'			=> __( 'A Number' , 'ccp-mentor-plugin' ),
					'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', 'ccp-mentor-plugin' ),
					'type'			=> 'number',
					'default'		=> '',
					'placeholder'	=> __( '42', 'ccp-mentor-plugin' )
				),
				array(
					'id' 			=> 'colour_picker',
					'label'			=> __( 'Pick a colour', 'ccp-mentor-plugin' ),
					'description'	=> __( 'This uses WordPress\' built-in colour picker - the option is stored as the colour\'s hex code.', 'ccp-mentor-plugin' ),
					'type'			=> 'color',
					'default'		=> '#21759B'
				),
				array(
					'id' 			=> 'an_image',
					'label'			=> __( 'An Image' , 'ccp-mentor-plugin' ),
					'description'	=> __( 'This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an imge the thumbnail will display above these buttons.', 'ccp-mentor-plugin' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'multi_select_box',
					'label'			=> __( 'A Multi-Select Box', 'ccp-mentor-plugin' ),
					'description'	=> __( 'A standard multi-select box - the saved data is stored as an array.', 'ccp-mentor-plugin' ),
					'type'			=> 'select_multi',
					'options'		=> array( 'linux' => 'Linux', 'mac' => 'Mac', 'windows' => 'Windows' ),
					'default'		=> array( 'linux' )
				)
			)
		);


		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Plugin Settings' , 'ccp-mentor-plugin' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'ccp-mentor-plugin' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main CCP_Mentor_Plugin_Settings Instance
	 *
	 * Ensures only one instance of CCP_Mentor_Plugin_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see CCP_Mentor_Plugin()
	 * @return Main CCP_Mentor_Plugin_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
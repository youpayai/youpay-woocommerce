<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://youpay.ai
 * @since      1.0.0
 *
 * @package    YouPay_WooCommerce
 * @subpackage YouPay_WooCommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    YouPay_WooCommerce
 * @subpackage YouPay_WooCommerce/admin
 * @author     Your Name <email@example.com>
 */
class YouPay_WooCommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $youpay_woocommerce    The ID of this plugin.
	 */
	private $youpay_woocommerce;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $youpay_woocommerce       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $youpay_woocommerce, $version ) {

		$this->youpay_woocommerce = $youpay_woocommerce;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in YouPay_WooCommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The YouPay_WooCommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->youpay_woocommerce, plugin_dir_url( __FILE__ ) . 'css/youpay-woocommerce-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in YouPay_WooCommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The YouPay_WooCommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->youpay_woocommerce, plugin_dir_url( __FILE__ ) . 'js/youpay-woocommerce-admin.js', array( 'jquery' ), $this->version, false );

	}	


	public function youpay_settings_init() {
		// Register a new setting for "youpay" page.
		register_setting( 'youpay', 'youpay_options' );
	 
		// Register a new section in the "youpay" page.
		add_settings_section(
			'youpay_section_developers',
			__( 'YouPay settings page.', 'youpay' ), array($this,'youpay_section_developers_callback'),
			'youpay'
		);

		add_settings_field( 
			'youpay_store_id',
			'YouPay Store ID',
			array($this,'youpay_text_input'),
			'youpay',
			'youpay_section_developers',
			array(
				'label_for'         => 'youpay_store_id',
				'class'             => 'youpay_row',
				'youpay_custom_data' => 'custom',
			)
		);

		add_settings_field( 
			'youpay_access_token',
			'YouPay Access Token',
			array($this,'youpay_text_input'),
			'youpay',
			'youpay_section_developers',
			array(
				'label_for'         => 'youpay_access_token',
				'class'             => 'youpay_row',
				'youpay_custom_data' => 'custom',
			)
		);

		add_settings_field( 
			'youpay_username',
			'YouPay Username',
			array($this,'youpay_text_input'),
			'youpay',
			'youpay_section_developers',
			array(
				'label_for'         => 'youpay_username',
				'class'             => 'youpay_row',
				'youpay_custom_data' => 'custom',
			)
		);

		add_settings_field( 
			'youpay_password',
			'YouPay Password',
			array($this,'youpay_password_input'),
			'youpay',
			'youpay_section_developers',
			array(
				'label_for'         => 'youpay_password',
				'class'             => 'youpay_row',
				'youpay_custom_data' => 'custom',
			)
		);		

		// // Register a new field in the "youpay_section_developers" section, inside the "youpay" page.
		// add_settings_field(
		// 	'youpay_field_pill', // As of WP 4.6 this value is used only internally.
		// 							// Use $args' label_for to populate the id inside the callback.
		// 		__( 'Pill', 'youpay' ),
		// 	array($this,'youpay_field_pill_cb'),
		// 	'youpay',
		// 	'youpay_section_developers',
		// 	array(
		// 		'label_for'         => 'youpay_field_pill',
		// 		'class'             => 'youpay_row',
		// 		'youpay_custom_data' => 'custom',
		// 	)
		// );
	}
	 	 
	/**
	 * Custom option and settings:
	 *  - callback functions
	 */
	
	
	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	public function youpay_section_developers_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'youpay' ); ?></p>
		<?php
	}
	
	/**
	 * Text field callback function.
	 *
	 * WordPress has magic interaction with the following keys: label_for, class.
	 * - the "label_for" key value is used for the "for" attribute of the <label>.
	 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
	 * Note: you can add custom key value pairs to be used inside your callbacks.
	 *
	 * @param array $args
	 */
	public function youpay_text_input( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'youpay_options' );
		?>
		<input type="text" id="<?php echo $args['label_for']; ?>"  name="youpay_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $options[ $args['label_for']]; ?>">

		<?php
	}

	/**
	 * Password field callback function.
	 *
	 * WordPress has magic interaction with the following keys: label_for, class.
	 * - the "label_for" key value is used for the "for" attribute of the <label>.
	 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
	 * Note: you can add custom key value pairs to be used inside your callbacks.
	 *
	 * @param array $args
	 */
	public function youpay_password_input( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'youpay_options' );
		?>
		<input type="password" id="<?php echo $args['label_for']; ?>"  name="youpay_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo $options[ $args['label_for']]; ?>">

		<?php
	}	

	/**
	 * Pill field callback function.
	 *
	 * WordPress has magic interaction with the following keys: label_for, class.
	 * - the "label_for" key value is used for the "for" attribute of the <label>.
	 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
	 * Note: you can add custom key value pairs to be used inside your callbacks.
	 *
	 * @param array $args
	 */
	public function youpay_field_pill_cb( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$options = get_option( 'youpay_options' );
		?>
		<select
				id="<?php echo esc_attr( $args['label_for'] ); ?>"
				data-custom="<?php echo esc_attr( $args['youpay_custom_data'] ); ?>"
				name="youpay_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
			<option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
				<?php esc_html_e( 'red pill', 'youpay' ); ?>
			</option>
			<option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
				<?php esc_html_e( 'blue pill', 'youpay' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'youpay' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'You take the red pill and you stay in Wonderland and I show you how deep the rabbit-hole goes.', 'youpay' ); ?>
		</p>
		<?php
	}
	
	/**
	 * Add the top level menu page.
	 */
	public function youpay_options_page() {
		add_menu_page(
			'YouPay',
			'YouPay Options',
			'manage_options',
			'youpay',
			array($this,'youpay_options_page_html')
		);
	}
	

	/**
	 * Top level menu callback function
	 */
	public function youpay_options_page_html() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
	
		// add error/update messages
	
		// check if the user have submitted the settings
		// WordPress will add the "settings-updated" $_GET parameter to the url
		if ( isset( $_GET['settings-updated'] ) ) {
			// add settings saved message with the class of "updated"
			add_settings_error( 'youpay_messages', 'youpay_message', __( 'Settings Saved', 'youpay' ), 'updated' );
		}
	
		// show error/update messages
		settings_errors( 'youpay_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "youpay"
				settings_fields( 'youpay' );
				// output setting sections and their fields
				// (sections are registered for "youpay", each field is registered to a specific section)
				do_settings_sections( 'youpay' );
				// output save settings button
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}
}
?>
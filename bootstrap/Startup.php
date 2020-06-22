<?php

namespace WooYouPay\bootstrap;

use WooYouPay\controllers\AdminController;
use WooYouPay\controllers\ProcessPayment;
use WooYouPay\controllers\YouPayGateway;
use WooYouPay\controllers\LoaderTrait;
use YouPaySDK\Client;

/**
 * Class startup
 *
 * @package WooYouPay\bootstrap
 */
class Startup {

	/**
	 * Controllers to inject
	 * TODO : Auto dependency injection
	 *
	 * @var string[]
	 */
	protected $controllers = array(
		AdminController::class,
		ProcessPayment::class,
		'delay' => array(
			YouPayGateway::class,
		),
	);

	public static $plugin_slug_static = 'youpay';
	public $plugin_slug;

	/**
	 * The CLI Command name
	 *
	 * @var string
	 */
	protected $cli_command = 'youpay';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var Loader $loader Maintains and registers all hooks for the plugin.
	 */
	public $loader;

	/**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var	  string	$version	The current version of the plugin.
	 */
	public $version;

	/**
	 * The current plugin settings
	 *
	 * @var bool|mixed|void Settings.
	 */
	public $settings;

	/**
	 * The current plugin settings
	 *
	 * @var bool|mixed|void Settings.
	 */
	public $api;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the resources area and
	 * the public-facing side of the site.
	 */
	public function __construct() {
		$this->plugin_slug = self::$plugin_slug_static;
		$this->settings    = get_option( $this->plugin_slug . '_settings', array() );
		$this->version     = YOUPAY_VERSION;
		$this->loader      = new Loader();
		$this->api         = new Client();

		// Setup Client Keys.
		if ( ! empty( $this->settings['keys'] ) ) {
			$keys = $this->settings['keys'];
			$this->api->setToken( $keys->access_token );
			$this->api->setStoreID( $keys->store_id );
		}
	}

	/**
	 * Loader for The Startup File
	 */
	public function loader() {
		$this->loader->add_action( 'admin_init', $this, 'plugin_redirect' );
	}

	/**
	 * Load the controllers
	 */
	public function sort_controllers() {
		$delayed = $this->controllers['delay'] ?? false;
		unset( $this->controllers['delay'] );

		$this->load_controllers();

		if ( ! empty( $delayed ) ) {
			$this->controllers = $delayed;
			$this->loader->add_action( 'plugins_loaded', $this, 'load_delayed' );
		}
	}

	/**
	 * Load Delayed controllers
	 */
	public function load_delayed() {
		$this->loader = new Loader();
		$this->load_controllers();
		$this->loader->run();
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since	1.0.0
	 */
	public static function run() {
		$self = new self();
		$self->sort_controllers();
		$self->loader();
		if ( class_exists( 'WP_CLI' ) && class_exists( 'WP_CLI' ) ) {
			\WP_CLI::add_command( $self->cli_command, '\WooYouPay\controllers\CliController' );
		}
		$self->loader->run();
	}

	/**
	 * Init Controller
	 *
	 * @param string $controller The Controller Class Name.
	 * @return mixed|LoaderTrait
	 */
	protected function init_controller( string $controller ) {
		$controller = new $controller();
		$controller->init( $this );
		return $controller;
	}

	/**
	 * Load the Controllers
	 */
	protected function load_controllers() {
		foreach ( $this->controllers as $key => $controller ) {
			$this->init_controller( $controller );
		}
	}

	public function plugin_redirect() {
		if ( ! empty( $this->settings['redirect'] ) && ! isset( $_GET['mylogin'] ) ) {
			wp_redirect(
				admin_url( 'admin.php?page=' . $this->plugin_slug . '_login_page&mylogin=true' )
			);
		}
	}

	/**
	 * Run on Activation
	 */
	public static function activate() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			echo '<h3>Please install WooCommerce before Activating</h3>';

			// Adding @ before will prevent XDebug output.
			@trigger_error( 'Please install Woocommerce before activating.', E_USER_ERROR );
		}

		update_option(
			self::$plugin_slug_static . '_settings',
			array( 'redirect' => true )
		);
	}

	/**
	 * Run on DeActivation
	 */
	public static function deactivate() {
		delete_option(self::$plugin_slug_static . '_settings');
	}

	/**
	 * Update Plugin Settings.
	 *
	 * @param array $settings Settings to change.
	 */
	public function update_settings( array $settings ) {
		$this->settings = array_merge( $this->settings, $settings );
		update_option( $this->plugin_slug . '_settings', $this->settings );
	}
}

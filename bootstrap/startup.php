<?php

namespace WooYouPay\bootstrap;

use WooYouPay\controllers\AdminController;
use WooYouPay\controllers\ProcessPayment;
use WooYouPay\controllers\YouPayGateway;
use WooYouPay\controllers\LoaderTrait;

class startup {

	/**
	 * controllers to inject
	 * TODO : Auto dependency injection
	 *
	 * @var string[]
	 */
	protected $controllers = array(
		AdminController::class,
		ProcessPayment::class,
		'delay' => array(
			YouPayGateway::class
		)
	);

	/**
	 * The unique identifier of this plugin.
	 *
	 * @access   protected
	 * @var	  string	$plugin_slug The string used to uniquely identify this plugin.
	 */
	protected $plugin_slug = 'mywork-wpmu';

	/**
	 * The CLI Command name
	 *
	 * @var string
	 */
	protected $cli_command = 'mywork';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @access   protected
	 * @var	  loader	$loader	Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var	  string	$version	The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current plugin settings
	 *
	 * @var bool|mixed|void Settings.
	 */
	protected $settings;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the resources area and
	 * the public-facing side of the site.
	 *
	 */
	public function __construct() {
		$this->settings = get_option( 'woocommerce_youpay_settings', array() );
		$this->version  = YOUPAY_VERSION;
		$this->loader   = new loader();
		$this->sort_controllers();
		if ( class_exists( 'WP_CLI' ) && class_exists( 'WP_CLI' ) ) {
			\WP_CLI::add_command( $this->cli_command, '\WooYouPay\controllers\CliController' );
		}

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
		$this->loader = new loader();
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
		$controller->init( $this->plugin_slug, $this->version, $this->settings, $this->loader );
		return $controller;
	}

	protected function load_controllers()
	{
		foreach ($this->controllers as $key => $controller) {
			$this->init_controller($controller);
		}
	}
}

<?php

namespace WooYouPay\bootstrap;

use WooYouPay\controllers\AdminController;
use WooYouPay\controllers\ProcessPayment;
use WooYouPay\controllers\YouPayController;
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
        YouPayController::class,
		// Delayed Controllers, so that it is included after all other plugins
		'delay' => array(
			YouPayGateway::class,
		),
	);

	public static $plugin_slug_static = 'youpay';
	public $plugin_slug;

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
     * Has Api Keys
     *
     * @var bool
     */
    public $has_api_keys;

	/**
	 * The Resource Root
	 *
	 * @var string Url.
	 */
	public $resource_root;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the resources area and
	 * the public-facing side of the site.
	 */
	public function __construct() {

        if ( ! defined( 'YOUPAY_RESOURCE_ROOT' ) ) {
            define('YOUPAY_RESOURCE_ROOT', plugins_url( '/resources/', YOUPAY_PLUGIN_PATH . 'woo-youpay.php' ));
        }

		$this->plugin_slug   = self::$plugin_slug_static;
		$this->settings      = get_option( $this->plugin_slug . '_settings', array() );
		$this->settings['woocommerce'] = get_option( 'woocommerce_' . $this->plugin_slug . '_settings', array() );

		$this->version       = YOUPAY_VERSION;
		$this->loader        = new Loader();
		$this->api           = new Client();
		$this->resource_root = YOUPAY_RESOURCE_ROOT;

		// Setup Client Keys.
		if ( ! empty( $this->settings['keys'] ) ) {
			$keys = $this->settings['keys'];
			if ( ! empty($keys->access_token) && ! empty($keys->store_id)) {
			    $this->api->setToken( $keys->access_token );
                $this->api->setStoreID( $keys->store_id );
                $this->has_api_keys = true;
            }
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
	    // Delay Certain Controllers due to WooComm
        $delayed = false;
        if (! empty($this->controllers['delay'])) {
            $delayed = $this->controllers['delay'];
        }
		unset( $this->controllers['delay'] );

		$this->load_controllers();

		// Handle Delayed Controllers
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
        $self->loader->run();
        // TODO: Add CLI Commands
//		if ( class_exists( 'WP_CLI' ) && class_exists( 'WP_CLI' ) ) {
//			\WP_CLI::add_command( $self->plugin_slug, '\WooYouPay\controllers\CliController' );
//		}
	}

	/**
	 * Load the Controllers
	 */
	protected function load_controllers() {
		foreach ( $this->controllers as $key => $controller ) {
			$this->init_controller( $controller );
		}
	}

	/**
	 * Initialise a Controller
	 *
	 * @param string $controller Controller Class Name.
	 * @return mixed|LoaderTrait
	 */
	protected function init_controller( $controller ) {
		$controller = new $controller();
		$controller->init( $this );
		return $controller;
	}

    /**
     * Redirect to login page logic
     * TODO: Replace with banner message
     */
	public function plugin_redirect() {
	    // if redirect var set (only exists on first redirect)
		if ( ( ! empty( $this->settings['redirect'] ) ||
                (
                    // Redirect all other pages except plugin page
                    (empty( $this->settings['keys'] ) || empty( $this->settings['keys']->access_token ) )
                    && (strpos($_SERVER['REQUEST_URI'], 'plugins.php') === false) && $_SERVER['REQUEST_METHOD'] === 'GET'
                )
            ) && ! isset( $_GET['mylogin'] ) ) {
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

		// TODO: create page
        $wordpress_page = array(
            'post_title'    => 'YouPay Payment Processed',
            'post_content'  => '[youpay-success]',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type' => 'page'
        );
        $post_id = wp_insert_post( $wordpress_page );

        // TODO: check if settings already exist
        update_option(
            self::$plugin_slug_static . '_settings',
            array(
                'redirect' => true,
            )
        );
        update_option(
            'woocommerce_' . self::$plugin_slug_static . '_settings',
            array(
                'redirect_url' => get_permalink($post_id)
            )
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

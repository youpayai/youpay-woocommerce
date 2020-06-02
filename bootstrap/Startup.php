<?php

namespace WooYouPay\bootstrap;

use WooYouPay\Controllers\AdminController;
use WooYouPay\Controllers\ProcessPayment;
use WooYouPay\Controllers\YouPayGateway;
use WooYouPay\Controllers\LoaderTrait;

class Startup {

    /**
     * Controllers to inject
     * TODO : Auto dependency injection
     *
     * @var string[]
     */
    protected $controllers = [
        AdminController::class,
        ProcessPayment::class,
        'delay' => [
            YouPayGateway::class
        ]
    ];

    /**
	 * The unique identifier of this plugin.
     *
     * @access   protected
	 * @var      string    $plugin_slug The string used to uniquely identify this plugin.
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
     * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the resources area and
	 * the public-facing side of the site.
	 *
	 */
	public function __construct() {
        $this->version = YOUPAY_VERSION;
        $this->loader = new Loader();
        $this->sortControllers();
        if ( class_exists( 'WP_CLI' ) && class_exists( 'WP_CLI' ) ) {
            \WP_CLI::add_command( $this->cli_command, '\WooYouPay\Controllers\CliController' );
        }
	}

    /**
     * Load the Controllers
     */
	public function sortControllers()
    {
        $delayed = $this->controllers['delay'] ?? false;
        unset($this->controllers['delay']);

        $this->loadControllers();

        if ( !empty($delayed) ) {
            $this->controllers = $delayed;
            $this->loader->add_action( 'plugins_loaded', $this, 'loadDelayed' );
        }
    }

    public function loadDelayed()
    {
//        $oldLoader = $this->loader;
        $this->loader = new Loader();
        $this->loadControllers();
        $this->loader->run();
//        $this->loader = $oldLoader;
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public static function run() {
	    $self = new self();
		$self->loader->run();
	}

    /**
     * @param string $controller
     * @return mixed|LoaderTrait
     */
    protected function initController(string $controller)
    {
        $controller = new $controller;
//        if ($controller instanceof LoaderTrait) {
            $controller->init($this->plugin_slug, $this->version, $this->loader);
//        }
        return $controller;
    }

    protected function loadControllers()
    {
        foreach ($this->controllers as $key => $controller) {
            $this->initController($controller);
        }
    }
}

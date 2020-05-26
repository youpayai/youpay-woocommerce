<?php

namespace MyWorkWPMU\includes;

use MyWorkWPMU\admin\AdminController;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Mywork_Wpmu
 * @subpackage Mywork_Wpmu/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Mywork_Wpmu
 * @subpackage Mywork_Wpmu/includes
 * @author     Your Name <email@example.com>
 */
class MyWorkWPMU {

    protected $controllers = [
        AdminController::class
    ];

    /**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

    /**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $mywork_wpmu    The string used to uniquely identify this plugin.
	 */
	protected $mywork_wpmu = 'mywork-wpmu';

    /**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
        $this->version = MYWORK_WPMU_VERSION;
        $this->loader = new Loader();
        $this->loadControllers();
	}

    /**
     * Load the controllers
     */
	protected function loadControllers()
    {
        foreach ($this->controllers as $controller)
        {
            if ( $controller instanceof BaseMyWorkClass ) {
                new $controller( $this->mywork_wpmu, $this->version, $this->loader );
            }
        }
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
}

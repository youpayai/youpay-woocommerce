<?php

namespace MyWorkWPMU\includes;

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
abstract class BaseMyWorkClass {

    protected $plugin_slug;
    protected $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $mywork_wpmu       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $mywork_wpmu, $version, Loader $loader ) {

        $this->plugin_slug = $mywork_wpmu;
        $this->version = $version;
        $this->loader($loader);
    }

    abstract public function loader(Loader $loader);
}

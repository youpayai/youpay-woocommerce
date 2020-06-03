<?php

namespace WooYouPay\Controllers;

use WooYouPay\bootstrap\Loader;
/**
 * The file that defines the core plugin class
 *
 * A class definition that bootstrap attributes and functions used across both the
 * public-facing side of the site and the resources area.
 *
 */
trait LoaderTrait {

	/**
	 * @var string The Plugin Slug.
	 */
    protected $plugin_slug;

	/**
	 * @var string The plugin version.
	 */
	protected $version;

	/**
	 * @var array The Plugin Settings.
	 */
	protected $plugin_settings;

    /**
     * Initialize the class and set its properties.
     *
     * @param      string    $plugin_slug       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
	public function init( string $plugin_slug, $version, $plugin_settings, Loader $loader ) {
		$this->plugin_settings = $plugin_settings;
		$this->plugin_slug     = $plugin_slug;
		$this->version         = $version;
		$this->loader( $loader );
	}

	/**
	 * Load the filters and actions for the class
	 *
	 * @param Loader $loader The Loader Object.
	 * @return void
	 */
	abstract public function loader( Loader $loader );
}

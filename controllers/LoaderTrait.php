<?php

namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Loader;
use WooYouPay\bootstrap\Startup;

/**
 * The file that defines the core plugin class
 *
 * A class definition that bootstrap attributes and functions used across both the
 * public-facing side of the site and the resources area.
 */
trait LoaderTrait {

	/**
	 * The Class to hold it all.
	 *
	 * @var Startup $youpay Access to Startup Info.
	 */
	protected $youpay;

	/**
	 * @var string $plugin_slug The Plugin Slug.
	 * @deprecated
	 */
    protected $plugin_slug;

	/**
	 * @var string The plugin version.
	 * @deprecated
	 */
	protected $version;

	/**
	 * @var array The Plugin Settings.
	 * @deprecated
	 */
	protected $plugin_settings;

	/**
	 * Initilise Controller
	 *
	 * @param Startup $youpay Access to all the Startup Info.
	 */
	public function init( $youpay ) {
		$this->youpay = $youpay;
		$this->loader( $youpay->loader );

		/**
		 * Legacy Code
		 */
		$this->plugin_settings = $youpay->settings;
		$this->plugin_slug     = $youpay->plugin_slug;
		$this->version         = $youpay->version;
	}

	/**
	 * Load the filters and actions for the class
	 *
	 * @param Loader $loader The loader Object.
	 * @return void
	 */
	abstract public function loader( Loader $loader );
}

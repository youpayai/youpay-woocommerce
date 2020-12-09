<?php

namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Loader;
use WooYouPay\bootstrap\Startup;

/**
 * A trait that gives the plugin controllers access to settings and the loader function
 */
trait LoaderTrait {

	/**
	 * The Class to hold it all.
	 *
	 * @var Startup $youpay Access to Startup Info.
	 */
	protected $youpay;

	/**
	 * Plugin Slug
	 *
	 * @var string $plugin_slug The Plugin Slug.
	 * @deprecated
	 */
	protected $plugin_slug;

	/**
	 * Plugin Version Number
	 *
	 * @var string The plugin version.
	 * @deprecated
	 */
	protected $version;

	/**
	 * Plugin Settings
	 *
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
		 * Legacy Code.
		 * TODO: Cleanup, I think something weird needs these still
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

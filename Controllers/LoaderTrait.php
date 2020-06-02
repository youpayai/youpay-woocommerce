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

    protected $plugin_slug;
    protected $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param      string    $plugin_slug       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function init( string $plugin_slug, $version, Loader $loader ) {
        $this->plugin_slug = $plugin_slug;
        $this->version = $version;
        $this->loader($loader);
    }

    abstract public function loader(Loader $loader);
}

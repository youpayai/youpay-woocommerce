<?php

namespace WooYouPay\controllers;

use WooYouPay\bootstrap\loader;

class AdminController
{
    use LoaderTrait;

    public function loader(loader $loader)
    {
//        $loader->add_filter( 'woocommerce_payment_gateways', $this,'add_your_gateway_class', 10, 1 );

//        $loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
//        $loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
//        $loader->add_action( 'admin_menu', $this, 'add_admin_page' );
    }

    public function add_your_gateway_class() {
       dd('sdfddf');
    }

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug, YOUPAY_PLUGIN_PATH . '/resources/css/resources-style.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug, YOUPAY_PLUGIN_PATH . '/resources/js/resources-script.js', array( 'jquery' ), $this->version, false );
	}

    public function add_admin_page() {
        add_menu_page(
            'MyWork',
            'MyWork',
            'manage_options',
            $this->plugin_slug,
            array( $this, 'load_admin_page_content' ), // Calls function to require the partial
            'https://s3.mywork.com.au/mw-icon-small.png',
            80
        );
    }

    // Load the plugin resources page partial.
    public function load_admin_page_content() {
        require_once YOUPAY_PLUGIN_PATH . 'resources/views/admin-view.php';
    }
}

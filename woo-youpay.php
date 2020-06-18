<?php

/**
 * Plugin Name:       YouPay - WooCommerce
 * Plugin URI:        http://youpay.link/
 * Description:       Send the bill to someone else to pay.
 * Version:           1.0.0
 * Author:            MyWork
 * Author URI:        http://mywork.com.au/
 *
 * @package           youpay
 */

namespace WooYouPay;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'YOUPAY_VERSION' ) ) {
	define( 'YOUPAY_VERSION', '1.0.0' );
}

//require_once 'bootstrap-local.php';

if ( ! defined( 'YOUPAY_PLUGIN_PATH' ) ) {
	define( 'YOUPAY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

require_once YOUPAY_PLUGIN_PATH . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-junior-zoho-activator.php
 */
function activate() {
	\WooYouPay\bootstrap\Startup::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-junior-zoho-deactivator.php
 */
function deactivate() {
	\WooYouPay\bootstrap\Startup::deactivate();
}

register_activation_hook( __FILE__, '\WooYouPay\activate' );
register_deactivation_hook( __FILE__, '\WooYouPay\deactivate' );

\WooYouPay\bootstrap\Startup::run();

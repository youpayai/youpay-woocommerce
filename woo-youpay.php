<?php
/**
 * Plugin Name:       YouPay for WooCommerce
 * Plugin URI:        http://youpay.link/
 * Description:       Share a YouPay link with someone & let them pay for your order.
 * Version:           1.1.2
 * Author:            YouPay
 * Author URI:        https://youpay.ai/
 *
 * @package           youpay
 */

namespace WooYouPay;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'YOUPAY_VERSION' ) ) {
	define( 'YOUPAY_VERSION', '1.1' );
}

if ( ! defined( 'YOUPAY_BASENAME' ) ) {
	define( 'YOUPAY_BASENAME', plugin_basename(__FILE__) );
}

if ( ! defined( 'YOUPAY_PLUGIN_PATH' ) ) {
	define( 'YOUPAY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

require_once YOUPAY_PLUGIN_PATH . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 */
function activate() {
	\WooYouPay\bootstrap\Startup::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate() {
	\WooYouPay\bootstrap\Startup::deactivate();
}

register_activation_hook( __FILE__, '\WooYouPay\activate' );
register_deactivation_hook( __FILE__, '\WooYouPay\deactivate' );

\WooYouPay\bootstrap\Startup::run();

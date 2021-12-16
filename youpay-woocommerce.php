<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://youpay.ai
 * @since             2.0.0
 * @package           YouPay_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       YouPay WooCommmerce
 * Plugin URI:        http://example.com/youpay-woocommerce-uri/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           2.0.0
 * Author:            YouPay
 * Author URI:        http://youpay.ai/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       youpay-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'YOUPAY_WOOCOMMERCE_VERSION', '2.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-youpay-woocommerce-activator.php
 */
function activate_youpay_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-youpay-woocommerce-activator.php';
	YouPay_WooCommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-youpay-woocommerce-deactivator.php
 */
function deactivate_youpay_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-youpay-woocommerce-deactivator.php';
	YouPay_WooCommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_youpay_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_youpay_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-youpay-woocommerce.php';
require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_youpay_woocommerce() {

	$plugin = new YouPay_WooCommerce();
	$plugin->run();

}
run_youpay_woocommerce();

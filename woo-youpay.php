<?php

/**
 * Plugin Name:       YouPay - WooCommerce
 * Plugin URI:        http://youpay.link/
 * Description:       Send the bill to someone else to pay.
 * Version:           1.0.0
 * Author:            MyWork
 * Author URI:        http://mywork.com.au/
 */

namespace WooYouPay;

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined('YOUPAY_VERSION') ) {
    define( 'YOUPAY_VERSION', '1.0.0' );
}

if ( ! defined('YOUPAY_PLUGIN_PATH') ) {
    define( 'YOUPAY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

require_once YOUPAY_PLUGIN_PATH . 'vendor/autoload.php';

\WooYouPay\bootstrap\Startup::run();

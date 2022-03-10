<?php

/**
 * Fired during plugin activation
 *
 * @link       http://youpay.ai
 * @since      2.0.0
 *
 * @package    YouPay_WooCommerce
 * @subpackage YouPay_WooCommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    YouPay_WooCommerce
 * @subpackage YouPay_WooCommerce/includes
 * @author     Björn Mett <bjorn@mywork.com.au>
 */
class YouPay_WooCommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		//create db table for YP orders
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'youpay_orders';
	
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			youpay_order_id varchar(128) NOT NULL,
			products longtext NOT NULL,
			shopper_data longtext NOT NULL,
			UNIQUE KEY id (id)
		) $charset_collate;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}

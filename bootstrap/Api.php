<?php

namespace WooYouPay\bootstrap;

use YouPaySDK\Client;
use YouPaySDK\Order;

/**
 * Class Api
 *
 * @package WooYouPay\controllers
 */
class Api {

	/**
	 * Store API Key in DB
	 *
	 * @param string $email Email address.
	 * @param string $password Password.
	 */
	public function store_api_key( $email, $password ) {
		$domain = str_replace( array( 'https://', 'http://', 'www.' ), '', site_url() );
		$domain = strstr( $domain, '/', true );
		$keys   = Client::auth( $email, $password, $domain );

		if ( 200 === $keys->status_code ) {
			$settings = get_option( 'woocommerce_youpay_settings' );
			$settings = array_merge( $settings, array( 'keys' => $keys ) );

			update_option( 'woocommerce_youpay_settings', $settings );
		}
	}

	/**
	 * Create Order
	 */
	public function create_order() {
		$client = Client::create( '' );
		$client->listOrders();
	}

}
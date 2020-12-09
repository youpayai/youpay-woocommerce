<?php

namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Loader;
use YouPaySDK\Client;

/**
 * Class AdminController
 *
 * @package WooYouPay\controllers
 */
class AdminController {
	use LoaderTrait;

	/**
	 * Loader
	 *
	 * @param Loader $loader Loader.
	 */
	public function loader( Loader $loader ) {
		$loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
		// $loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
		$loader->add_action( 'admin_menu', $this, 'add_login_page' );
		$loader->add_action( 'admin_post_process_youpay_login', $this, 'process_form_data' );
	}

	/**
	 * Load our StyleSheet in the admin area
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->youpay->plugin_slug, $this->youpay->resource_root . '/css/admin-styles.css', array(), $this->youpay->version, 'all' );
	}

	/**
	 * Add admin script : currently unused
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->youpay->plugin_slug, YOUPAY_PLUGIN_PATH . '/resources/js/resources-script.js', array( 'jquery' ), $this->youpay->version, false );
	}

	/**
	 * Add Admin Pages
	 */
	public function add_login_page() {
		add_submenu_page(
			null,
			'YouPay Login',
			'YouPay Login',
			'manage_options',
			$this->youpay->plugin_slug . '_login_page',
			array( $this, 'load_login_page' )
		);
	}

	/**
	 * Process YouPay Login via API
	 */
	public function process_form_data() {
		$post = $_POST;
		if ( empty( $post['email'] ) || empty( $post['password'] ) ) {
			wp_redirect(
				admin_url( 'admin.php?page=' . $this->youpay->plugin_slug . '_login_page&mylogin=true&yperror=invalid_creds' )
			);
			exit;
		}

		$domain = str_replace( array( 'https://', 'http://', 'www.' ), '', site_url() );

		try {
			$keys = Client::auth( $post['email'], $post['password'], $domain, 'woocommerce' );
		} catch ( \Exception $exception ) {
			wp_redirect(
				admin_url( 'admin.php?page=' . $this->youpay->plugin_slug . '_login_page&mylogin=true&yperror=invalid_creds' )
			);
			exit;
		}

		if ( $keys->status_code !== 200 ) {
			wp_redirect(
				admin_url( 'admin.php?page=' . $this->youpay->plugin_slug . '_login_page&mylogin=true&yperror=invalid_creds' )
			);
			exit;
		}

		$this->youpay->update_settings(
			array(
				'keys'     => $keys,
			)
		);

		// ReSet API Keys.
		$this->youpay->api->setToken( $keys->access_token );
		$this->youpay->api->setStoreID( $keys->store_id );
		$this->youpay->has_api_keys = true;

		$next_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=youpay' );
		// Get store information.
		try {
			$store = $this->youpay->api->getStore( $keys->store_id );
		} catch ( \Exception $exception ) {
			// left blank.
		}
		if ( empty( $store->payment_gateways ) ) {
			$url = $this->youpay->api->api_url . "resources/payment-gateways/new?viaResource=stores&viaResourceId={$keys->store_id}&viaRelationship=payment_gateways&redirect_after=" . urlencode( $next_url );
			wp_redirect( $url );
			exit;
		} else {
			$this->youpay->update_settings(
				array(
					'has_payment_gateways' => true,
				)
			);
		}

		wp_redirect( $next_url );
		exit;
	}

	/**
	 * Show the login page content
	 */
	public function load_login_page() {
		// Remove Redirect Setting
		$this->youpay->update_settings(
			array(
				'redirect' => false,
			)
		);

		require_once YOUPAY_PLUGIN_PATH . 'resources/views/login-view.php';
	}
}

<?php

namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Loader;

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
		$loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
		$loader->add_action( 'admin_menu', $this, 'add_login_page' );


		$loader->add_action( 'admin_post_process_youpay_login', $this, 'process_form_data' );

		//$loader->add_filter( 'woocommerce_payment_gateways', $this,'add_your_gateway_class', 10, 1 );
	}

	public function add_your_gateway_class() {
		dd('sdfddf');
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->youpay->plugin_slug, YOUPAY_PLUGIN_PATH . '/resources/css/resources-style.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->youpay->plugin_slug, YOUPAY_PLUGIN_PATH . '/resources/js/resources-script.js', array( 'jquery' ), $this->version, false );
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
			$this->youpay->plugin_slug,
			array( $this, 'load_login_page' )
		);
	}

	/**
	 * Process YouPay Login via API
	 */
	public function process_form_data() {
		$post = $_POST;
		if ( empty( $post['email'] ) || empty( $post['password'] ) ) {
			echo 'fail';
			exit;
		}

		$this->youpay->api->store_api_key(
			wp_unslash( $post['email'] ),
			wp_unslash( $post['password'] )
		);
	}

	/**
	 * Page Content
	 */
	public function load_login_page() {
		require_once YOUPAY_PLUGIN_PATH . 'resources/views/login-view.php';
	}
}

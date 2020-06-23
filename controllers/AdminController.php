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
		$loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );
		$loader->add_action( 'admin_menu', $this, 'add_login_page' );
		$loader->add_action( 'admin_post_process_youpay_login', $this, 'process_form_data' );
		$loader->add_action( 'woocommerce_after_register_post_type', $this, 'check_youpay_product_exists', 10 );
	}

	/**
	 * Check if the youpay product exits
	 */
	public function check_youpay_product_exists() {
		if ( empty( $this->youpay->settings['product_id'] ) ) {
			// Create product and save to settings.
			$this->youpay->update_settings(
				array(
					'product_id' => $this->create_product(),
				)
			);
		}
	}

	/**
	 * Create Product.
	 *
	 * @return int Product ID.
	 */
	public function create_product() {
		$product = new \WC_Product();

		$product->set_name( 'YouPay Product' ); // Name (title).
		$product->set_description( 'YouPay Payment product. Please do not remove.' );
		$product->set_short_description( '' );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'hidden' );
		$product->set_featured( false );
		$product->set_virtual( true );
		$product->set_regular_price( 1 );
		$product->set_price( 1 );
		$product->set_downloadable( false );
		$product->set_sold_individually( true );
		$product->set_weight( '' );
		$product->set_length( '' );
		$product->set_width( '' );
		$product->set_height( '' );
		$product->set_reviews_allowed( false );
		return $product->save();
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

/*		add_submenu_page(
			'wpengine-common',
			'General Settings',
			'General Settings',
			$capability,
			'wpengine-common',
			array( $this, 'wpe_admin_page' )
		);*/
		add_submenu_page(
			null,//'wpengine-common',
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
			echo 'fail';
			exit;
		}

		$domain = str_replace( array( 'https://', 'http://', 'www.' ), '', site_url() );

		$keys   = Client::auth( $post['email'], $post['password'], $domain );

		$this->youpay->update_settings(
			array(
				'redirect' => false,
				'keys' => $keys,
			)
		);

		wp_redirect('/wp-admin/');
	}

	/**
	 * Page Content
	 */
	public function load_login_page() {
		require_once YOUPAY_PLUGIN_PATH . 'resources/views/login-view.php';
	}
}

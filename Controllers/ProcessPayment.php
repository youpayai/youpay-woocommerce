<?php

namespace WooYouPay\Controllers;

use WooYouPay\bootstrap\Loader;

/**
 * Class ProcessPayment
 *
 * @package WooYouPay\Controllers
 */
class ProcessPayment {

	use LoaderTrait;

	/**
	 * Loader
	 *
	 * @param Loader $loader main loader var.
	 */
	public function loader( Loader $loader ) {
		$loader->add_action( 'parse_request', $this, 'sniff_requests', 0 );
		$loader->add_action( 'woocommerce_add_cart_item_data', $this, 'add_custom_field_item_data', 10, 4 );
	}

	public function add_custom_field_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
		if ( $product_id === $this->get_youpay_product_id() ) {
			$cart_item_data['total_price']  = $this->get_youpay_total();
			$cart_item_data['product_name'] = 'YouPay Payment';
		}
		return $cart_item_data;
	}

	/**
	 * Get YouPay Total Amount
	 *
	 * @return int The total value.
	 */
	public function get_youpay_total() {
		return 100;
	}


	/**
	 * Look for the Requests that have been setup with rewrite rules,
	 *  - rewrite rules are included further on
	 *
	 * @throws \Exception Exception.
	 */
	public function sniff_requests() {
		global $wp;
		if ( ! empty( $wp->query_vars['name'] ) && ! empty( $wp->query_vars['page'] ) && 'youpay' === $wp->query_vars['name'] ) {
			$this->set_you_id( $wp->query_vars['page'] );
			$this->create_order();
			return;
		}
	}

	/**
	 * Set the YouPay ID in the session
	 *
	 * @param string $you_id YouPay ID.
	 */
	public function set_you_id( string $you_id ) {
	}

	/**
	 * Create Order
	 *
	 * @throws \Exception Data Exception.
	 */
	public function create_order() {
		WC()->cart->add_to_cart( $this->get_youpay_product_id() );
		dd($checkout_url = wc_get_page_permalink( 'checkout' ));
		dd( wc_get_checkout_url() );
		exit;
	}

	/**
	 * Get YouPay Product ID
	 *
	 * @return int Get YouPay ID.
	 */
	public function get_youpay_product_id(): int {
		return 16738;
	}

	/**
	 * Get Basic Address
	 *
	 * @return string[]
	 */
	public function get_basic_address() {
		return array(
			'first_name' => 'YouPay User',
			'last_name'  => '',
			'company'    => '',
			'email'      => 'noreply@youpay.link',
			'phone'      => '',
			'address_1'  => '42 Wallaby Way',
			'address_2'  => '',
			'city'       => 'Sydney',
			'state'      => '',
			'postcode'   => '2000',
			'country'    => 'AU',
		);
	}

}

<?php

namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Loader;

/**
 * Class ProcessPayment
 *
 * @package WooYouPay\controllers
 */
class ProcessPayment {

	use LoaderTrait;

	/**
	 * loader
	 *
	 * @param Loader $loader main loader var.
	 */
	public function loader( Loader $loader ) {
		$loader->add_action( 'parse_request', $this, 'sniff_requests', 0 );
		$loader->add_action( 'woocommerce_add_cart_item_data', $this, 'add_custom_field_item_data', 10, 4 );
		$loader->add_action( 'woocommerce_before_calculate_totals', $this, 'before_calculate_totals', 20, 1 );
		$loader->add_action( 'woocommerce_checkout_get_value', $this, 'custom_woocommerce_fill_fields', 10, 2 );
		$loader->add_action( 'woocommerce_checkout_fields', $this, 'custom_override_checkout_fields' );

		$loader->add_action( 'woocommerce_payment_complete', $this, 'payment_complete', 10, 1 );
		$loader->add_action( 'woocommerce_checkout_create_order_line_item', $this, 'custom_checkout_create_order_line_item', 20, 4 );
	}

	/**
	 * Custom Checkout Create Order Line Item
	 *
	 * @param mixed $item item.
	 * @param mixed $cart_item_key cart_item_key.
	 * @param mixed $cart_item cart_item.
	 * @param mixed $order order.
	 */
	public function custom_checkout_create_order_line_item( $item, $cart_item_key, $cart_item, $order ) {
		if ( ! empty( $cart_item['youpay'] ) ) {
			$item->update_meta_data( 'youpay_id', $cart_item['youpay'] );
		}
	}

	/**
	 * Payment Completed
	 *
	 * @param mixed $order_id OrderID number.
	 *
	 * @throws \WC_Data_Exception \Exception Thrown.
	 */
	public function payment_complete( $order_id ) {
		if ( ! $order_id ) {
			return;
		}

		$order = \wc_get_order( $order_id );

		if ( ! $order->is_paid() || $order->get_meta( 'youpay_processed' ) ) {
			return;
		}

		foreach ( $order->get_items() as $key => $item ) {
			foreach ( $item->get_meta_data() as $key => $value ) {
				$data = $value->get_data();
				if ( 'youpay_id' === $data['key'] ) {

					$youpay_order = \wc_get_order( $data['value'] );
					$amount_paid  = (float) $order->get_total();
					$youpay_order->set_discount_total( $amount_paid );

					$previous_total = (float) $youpay_order->get_total();
					$youpay_order->set_total( $previous_total - $amount_paid );

					$youpay_order->add_meta_data( 'youpay_processed', 'true' );
					$youpay_order->save();
					$youpay_order->update_status( 'processing', 'Payment taken by order ID ' . $order_id );

					$order->add_meta_data( 'youpay_processed', 'true' );
					$order->save();

					// TODO: Send data back to youpay.ai
				}
			}
		}


	}

	/**
	 * Add Custom Field Item Data
	 *
	 * @param mixed $cart_item_data Cart Data.
	 * @param mixed $product_id Product ID.
	 * @param mixed $variation_id Variation ID number.
	 * @param int   $quantity Quantity.
	 *
	 * @return mixed
	 */
	public function add_custom_field_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
		if ( $product_id === $this->get_youpay_product_id() ) {
			$cart_item_data['total_price']  = $this->get_youpay_total( WC()->session->get( 'youpay_id' ) );
			$cart_item_data['product_name'] = 'YouPay Payment';
			$cart_item_data['youpay']       = WC()->session->get( 'youpay_id' );
		}
		return $cart_item_data;
	}


	/**
	 * Set the Price and Product name in the cart
	 *
	 * @param mixed $cart_obj The Cart as an object.
	 */
	public function before_calculate_totals( $cart_obj ) {
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['total_price'] ) ) {
				$cart_item['data']->set_price( $cart_item['total_price'] );
			}
			if ( isset( $cart_item['product_name'] ) ) {
				$cart_item['data']->set_name( $cart_item['product_name'] );
			}
		}
	}

	/**
	 * Get YouPay Total Amount
	 *
	 * @param int|bool $order_id Order ID number.
	 *
	 * @return int The total value.
	 */
	public function get_youpay_total( $order_id = false ) {
		if ( ! $order_id) {
			foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
				if (isset($cart_item['youpay']) && $cart_item['youpay']) {
					$order_id = $cart_item['youpay'];
					break;
				}
			}
		}
		$order = \wc_get_order( $order_id );

		if ( ! $order ) {
			return 0;
		}

		return (float) $order->get_total();
	}

	/**
	 * Look for the Requests that have been setup with rewrite rules,
	 *  - rewrite rules are included further on
	 *
	 * @throws \Exception Exception.
	 */
	public function sniff_requests() {
		global $wp;
		if ( ! empty( $_GET['youpay_id'] ) ) {

			// TODO: fetch order based off meta data - match URL rather than ID for safety.

			$youpay_order_id = $_GET['youpay_id'];
			$youpay_order    = \wc_get_order( $youpay_order_id );

			if ( ! $youpay_order || ! $youpay_order->has_status( 'on-hold' ) ) {
				wp_safe_redirect( '/' );
				exit;
			}

			WC()->cart->empty_cart();

			$this->set_you_id( $youpay_order_id );
			$this->create_order();
			exit; ///11.45 17th.....
			return;
		}
	}

	/**
	 * Set the YouPay ID in the session
	 *
	 * @param string $you_id YouPay ID.
	 */
	public function set_you_id( string $you_id ) {
		WC()->session->set( 'youpay_id', $you_id );
	}

	/**
	 * Create Order
	 *
	 * @throws \Exception Data Exception.
	 */
	public function create_order() {
		WC()->cart->add_to_cart( $this->get_youpay_product_id() );
		wp_redirect( wc_get_checkout_url() );
	}

	/**
	 * Get YouPay Product ID
	 *
	 * @return int Get YouPay ID.
	 */
	public function get_youpay_product_id(): int {
		return 16738; // TODO: do this better
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

	/**
	 * UnSet Fields
	 *
	 * @param mixed $fields Checkout Fields.
	 * @return mixed
	 */
	public function custom_override_checkout_fields( $fields ) {
		if ( ! $this->cart_is_youpay() ) {
			return $fields;
		}

		if ( isset( $fields['billing']['billing_last_name']['required'] ) ) {
			$fields['billing']['billing_last_name']['required'] = false;
		}
		unset( $fields['billing']['billing_company'] );
		unset( $fields['billing']['billing_email'] );
		unset( $fields['billing']['billing_phone'] );
		unset( $fields['billing']['billing_postcode'] );
		unset( $fields['billing']['billing_city'] );
		unset( $fields['billing']['billing_country'] );
		unset( $fields['billing']['billing_state'] );
		unset( $fields['billing']['billing_address_1'] );
		unset( $fields['billing']['billing_address_2'] );

		return $fields;
	}

	/**
	 * Set Default Field Values
	 *
	 * @param string $value The Original Value.
	 * @param mixed  $key The Item Key.
	 * @return mixed|string
	 */
	public function custom_woocommerce_fill_fields( $value, $key ) {
		if ( ! $this->cart_is_youpay() || ! empty( $this->plugin_settings['show_all_billing_fields'] ) ) {
			return $value;
		}

		$address = $this->get_basic_address();

		switch ( $key ) :
			case 'billing_first_name':
				return $address['first_name'] ?? $value;
			case 'billing_last_name':
				return $address['last_name'] ?? $value;
			case 'billing_email':
				return $address['email'] ?? $value;
			case 'billing_phone':
				return $address['phone'] ?? $value;
			case 'billing_postcode':
				return $address['postcode'] ?? $value;
			case 'billing_city':
				return $address['city'] ?? $value;
			case 'billing_country':
				return $address['country'] ?? $value;
			case 'billing_state':
				return $address['state'] ?? $value;
		endswitch;
		return $value;
	}

	/**
	 * Is Cart YouPay?
	 *
	 * @return bool
	 */
	public function cart_is_youpay() {
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['youpay'] ) && $cart_item['youpay'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Allow access via static method
	 *
	 * @see cart_is_youpay()
	 * @return bool
	 */
	public static function static_cart_is_youpay() {
		$self = new self();
		return $self->cart_is_youpay();
	}

}

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
		$loader->add_action( 'woocommerce_payment_complete', $this, 'payment_complete', 10, 1 );
	}

	/**
	 * Payment Completed
	 *
	 * @param mixed $order_id OrderID number.
	 *
	 * @throws \WC_Data_Exception \Exception Thrown.
	 */
	public function payment_complete() {
		$order = \wc_get_order( $_GET['youpay_id'] );

		$youpay_order_id = $order->get_meta( 'youpay_order_id' );

		$processed = $order->get_meta( 'youpay_processed', 'true' );

		if ( $processed ) {
			wp_safe_redirect( $this->youpay->settings['woocommerce']['redirect_url'] );
			exit;
		}

		$youpay_order = $this->youpay->api->getOrder( $youpay_order_id );

		if ( ! $youpay_order->completed && empty( (float) $youpay_order->balance ) ) {
			throw new \Exception( 'Error, not paid.' );
		}

		$amount_paid = (float) $youpay_order->total;

		$order->set_discount_total( $amount_paid );

		$previous_total = (float) $order->get_total();
		$order->set_total( $previous_total - $amount_paid );

		$order->add_meta_data( 'youpay_processed', 'true' );
		$order->update_status( 'processing', 'Payment taken by YouPay. Order ID: ' . $youpay_order_id );
		$order->save();

		wp_safe_redirect( $this->youpay->settings['woocommerce']['redirect_url'] );
		exit;
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
			$this->payment_complete();
			exit; ///11.45 17th.....
			return;
		}
	}
}

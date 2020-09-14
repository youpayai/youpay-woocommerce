<?php

namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Loader;

/**
 * Class YouPayController
 *
 * @package WooYouPay\controllers
 */
class YouPayController {

	use LoaderTrait;

	/**
	 * loader
	 *
	 * @param Loader $loader main loader var.
	 */
	public function loader( Loader $loader ) {
        $loader->add_filter( 'woocommerce_get_order_address', $this,
            'get_order_address_filter', 20, 3 );
	}

    public function get_order_address_filter( $data, $type, \WC_Order $order ) {
        $youpay_order_id = $order->get_meta( 'youpay_order_id' );
        if ($type !== 'billing' || empty($youpay_order_id)) {
            return $data;
        }
        return [
            'first_name' => '',
            'last_name'  => '',
            'company'    => '',
            'address_1'  => '',
            'address_2'  => '',
            'city'       => '',
            'state'      => '',
            'postcode'   => '',
            'country'    => '',
        ];
    }
}

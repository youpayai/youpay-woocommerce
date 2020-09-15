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

        $loader->add_action( 'add_meta_boxes', $this,
            'mv_add_meta_boxes', 20, 2 );
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

    function mv_add_meta_boxes($screen, \WP_Post $post)
    {
        if ( $post->post_type === 'shop_order' ) {
            $youpay_order_id = get_post_meta($post->ID, 'youpay_order_id', true);
            if ( ! empty($youpay_order_id) ) {
                add_meta_box(
                    'youpay_order_metabox',
                    __('YouPay','woocommerce'),
                    array($this, 'mv_add_other_fields_for_packaging'),
                    'shop_order',
                    'normal',
                    'high'
                );
            }

        }
    }

    function mv_add_other_fields_for_packaging()
    {
        global $post;

        $youpay_order_id = get_post_meta($post->ID, 'youpay_order_id', true);

//        $order_details = $this->youpay->api->getOrder($youpay_order_id);

        $order_link = $this->youpay->api->api_url . 'resources/orders/' . $youpay_order_id;

        require_once YOUPAY_PLUGIN_PATH . 'resources/views/order-metabox.php';
    }

}

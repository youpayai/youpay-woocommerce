<?php
namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Startup;
use YouPaySDK\OrderItem;

/**
 * YouPay Payment Gateway
 *
 * Provides a Cash on Delivery Payment Gateway.
 *
 * @class	 YouPayGateway
 * @extends	 WC_Payment_Gateway
 * @version	 1.0.0
 * @package	 WooYouPay/controllers
 */
class YouPayGateway extends \WC_Payment_Gateway {

	use LoaderTrait;

    /**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->youpay = new Startup();
		// Setup general properties.
		$this->setup_properties();

		// Load the settings.
        include YOUPAY_PLUGIN_PATH . 'resources/views/form-fields.php';

        $this->init_settings();
	}

	/**
	 * Load actions and filters
	 *
	 * @param \WooYouPay\bootstrap\Loader $loader
	 */
	public function loader( \WooYouPay\bootstrap\Loader $loader ) {
	    // Actions
            //$loader->add_action( 'woocommerce_before_thankyou', $this,
            //'thankyou_page', 10, 1 );
	     $loader->add_action( 'woocommerce_thankyou_' . $this->id, $this,
            'thankyou_page', 10, 1 );
		$loader->add_action( 'woocommerce_email_before_order_table', $this,
            'email_instructions', 10, 3 );
		$loader->add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, $this,
            'process_admin_options', 10 );

		// Filters
		$loader->add_filter( 'woocommerce_payment_complete_order_status', $this,
            'change_payment_complete_order_status', 10, 3 );
		$loader->add_filter( 'woocommerce_payment_gateways', $this,
            'add_your_gateway_class', 20, 1 );
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {
	    // Always Return false if its disabled
	    if ( ! parent::is_available()) {
	        return false;
        }
	    // Check we have
		if ( ! $this->youpay->has_api_keys ) {
		    return false;
        }
        if ( empty($this->youpay->settings['has_payment_gateways']) ) {
            try {
                $store = $this->youpay->api->getStore($this->youpay->settings['keys']->store_id);
            } catch (\Exception $exception) {
                return false;
            }
            if ( empty($store) || empty($store->payment_gateways) ) {
                return false;
            } else {
                $this->youpay->update_settings(array(
                    'has_payment_gateways' => true
                ));
            }
        }
        return true;
	}

	/**
	 * Add our class to the Gateways available
	 *
	 * @param array $methods Array of existing Gateways.
	 * @return mixed
	 */
	public function add_your_gateway_class( $methods ) {
		$methods[] = self::class;
		return $methods;
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
		$this->method_title       = __( 'YouPay', 'youpay' );
		$this->title              = 'YouPay';
		$this->description        = __( 'Send a YouPay link to someone else to pay for you. <br> When you click Place Order you will be given a secure YouPay link to share with your payer.', 'youpay' );
		$this->method_description = __( 'Let someone else pay for you.', 'youpay' );
		$this->id                 = 'youpay';
		$this->icon               = YOUPAY_RESOURCE_ROOT .'/images/youpay-logo-dark-100.png';
		$this->has_fields         = false;
	}

    /**
     * Output the gateway settings screen.
     */
    public function admin_options() {
        parent::admin_options();
        if (empty($this->youpay->settings['has_payment_gateways'])) {
            try {
                $store = $this->youpay->api->getStore($this->youpay->settings['keys']->store_id);
                if (empty($store->payment_gateways)) {
                    $url = $this->youpay->api->api_url . "resources/payment-gateways/new?viaResource=stores&viaResourceId={$store->id}&viaRelationship=payment_gateways";
                    echo "<div class='error'><p><strong>No Payment Gateway setup on YouPay.</strong><br><a href='$url' target='_blank'>Click here to get started.</a></p></div>";
                    echo "<span style='color:#f00'>WARNING: NO PAYMENT GATEWAY HAS BEEN SETUP.</span><br>";
                    echo "<a href='$url' target='_blank'>Click here to add a Payment Gateway</a>";
                } else {
                    $this->youpay->update_settings([
                        'has_payment_gateways' => true
                    ]);
                }
            } catch (\Exception $exception) {
                echo "<div class='error'><p><strong>Error contacting YouPay, please try re-installing.</strong></p></div>";
            }
        }

    }

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( empty( $this->youpay ) ) {
			$this->youpay = new Startup();
		}

		// TODO: Create Order in YouPay and save data locally.
		$order_items = array();
		foreach ( $order->get_items() as $item ) {
			$product   = wc_get_product( $item->get_product_id() );
			$image_id  = $product->get_image_id();
			$image_url = wp_get_attachment_image_url( $image_id );

			$order_items[] = OrderItem::create(
				array(
					'order_item_id' => $item->get_id(),
					'product_id'    => $item->get_product_id(),
					'title'         => $product->get_name(),
					'src'           => $image_url,
					'price'         => $item->get_subtotal(),
					'quantity'      => $item->get_quantity(),
					'total'         => $item->get_total(),
				)
			);
		}

		$total = (float) $order->get_total();
		$subtotal = (float) $order->get_subtotal();
		$extra_fees = $total - $subtotal;

		try {
			$response = $this->youpay->api->createOrderFromArray(
				array(
					'order_id'    => $order_id,
					'title'       => 'Order #' . $order_id,
					'order_items' => $order_items,
					'extra_fees'  => $extra_fees,
					'sub_total'   => $order->get_subtotal(),
					'total'       => $order->get_total(),
					'receiver'    => array(
						'name'      => rtrim( $order->shipping_first_name . ' ' . $order->shipping_last_name ),
						'phone'     => $order->billing_phone,
						'email'     => $order->billing_email,
						'address_1' => $order->shipping_address_1,
						'address_2' => $order->shipping_address_2,
						'suburb'    => $order->shipping_city,
						'state'     => $order->shipping_state,
						'country'   => $order->shipping_country,
						'postcode'  => $order->shipping_postcode,
					),
				)
			);
		} catch (\Exception $exception) {
		    // TODO: handle error gracefully
			throw $exception;
		}

		// TODO: Handle Errors from requests.

		$order->add_meta_data( 'youpay_order_id', $response->id );
		$order->add_meta_data( 'youpay_url', $response->url );
		$order->save();

		// Mark as processing or on-hold (payment won't be taken until delivery).
		$order->update_status( 'on-hold', __( 'Awaiting YouPay Payment.', 'youpay' ) );

		// Remove cart.
		WC()->cart->empty_cart();


		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	function redirect_after_purchase() {
		global $wp;
		if ( is_checkout() && !empty( $wp->query_vars['order-received'] ) && empty($_GET['test']) ) {
			$order_id = $wp->query_vars['order-received'];
			$youpay_order = wc_get_order( $order_id );
			if ($youpay_order && $url = $youpay_order->get_meta( 'youpay_url', true )) {
				$youpay_link  = 'https://youpay.link/share/' . $youpay_order->get_meta( 'youpay_url' );
				wp_redirect( $youpay_link );
				exit;
			}

		}
	}

	/**
	 * Output for the order received page.
	 *
	 * @param mixed $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
        $youpay_order = wc_get_order( $order_id );
        $recipient_name = $youpay_order->get_billing_first_name();
        $youpay_order_id  = $youpay_order->get_meta( 'youpay_order_id' );
        if ( empty($youpay_order_id) ) {
            return;
        }
	    $youpay_js = $this->youpay->api->getCheckoutJSUrl();
		require_once YOUPAY_PLUGIN_PATH . '/resources/views/thankyou-page.php';
	}

	/**
	 * Change payment complete order status to completed for COD orders.
	 *
	 * @since  3.1.0
	 * @param  string          $status Current order status.
	 * @param  int             $order_id Order ID.
	 * @param  \WC_Order|false $order Order object.
	 * @return string
	 */
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && $this->id === $order->get_payment_method() ) {
			$status = 'completed';
		}
		return $status;
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param \WC_Order $order Order object.
	 * @param bool      $sent_to_admin  Sent to admin.
	 * @param bool      $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $sent_to_admin || $this->id !== $order->get_payment_method() ) {
			return;
		}
		echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
	}
}

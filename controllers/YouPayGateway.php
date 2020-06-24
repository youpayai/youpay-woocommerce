<?php
namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Startup;
use YouPaySDK\OrderItem;

/**
 * Cash on Delivery Gateway.
 *
 * Provides a Cash on Delivery Payment Gateway.
 *
 * @class	   WC_Gateway_COD
 * @extends	 WC_Payment_Gateway
 * @version	 2.1.0
 * @package	 WooCommerce/Classes/Payment
 */
class YouPayGateway extends \WC_Payment_Gateway {

	use LoaderTrait;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->youpay = new Startup();
		// Setup general properties.
		$this->show_all_billing_fields = $this->get_option( 'show_all_billing_fields' );
		$this->setup_properties();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
	}

	/**
	 * Load actions and filters
	 *
	 * @param \WooYouPay\bootstrap\Loader $loader
	 */
	public function loader( \WooYouPay\bootstrap\Loader $loader ) {
		$loader->add_action( 'woocommerce_thankyou_' . $this->id, $this, 'thankyou_page', 10, 1 );
		$loader->add_filter( 'woocommerce_payment_complete_order_status', $this, 'change_payment_complete_order_status', 10, 3 );
		$loader->add_action( 'woocommerce_email_before_order_table', $this, 'email_instructions', 10, 3 );
		$loader->add_filter( 'woocommerce_payment_gateways', $this, 'add_your_gateway_class', 20, 1 );
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( ProcessPayment::static_cart_is_youpay() ) {
			return false;
		}
		return parent::is_available();
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
		$this->title              = '<img src="' . $this->youpay->resource_root .'/images/youpay-logo-dark.png" width="150" style="padding: 10px 0 10px 0;"> ';
		$this->description        = '';//__( 'Let someone else pay for you.', 'youpay' );
		$this->method_description = __( 'Let someone else pay for you.', 'youpay' );
		$this->id                 = 'youpay';
		$this->icon               = '';
		$this->has_fields         = false;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'woocommerce' ),
				'label'       => __( 'Enable YouPay', 'youpay' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'show_all_billing_fields' => array(
				'title'       => __( 'Enable/Disable', 'woocommerce' ),
				'label'       => __( 'Show all billing fields', 'youpay' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
		);
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if (empty($this->youpay)) {
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
					'src'           => $image_url,
					'product_id'    => $item->get_product_id(),
					'order_item_id' => $item->get_id(),
					'title'         => $item->get_name(),
					'quantity'      => $item->get_quantity(),
					'price'         => $item->get_subtotal(),
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
				)
			);
		} catch (\Exception $exception) {
			dd($exception->getMessage());
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

	/**
	 * Output for the order received page.
	 *
	 * @param mixed $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		$link  = 'https://youpay.link/' . $order->get_meta( 'youpay_url' );
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
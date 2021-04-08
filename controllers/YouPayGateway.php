<?php
namespace WooYouPay\controllers;

use WooYouPay\bootstrap\Startup;
use YouPaySDK\OrderItem;

/**
 * YouPay Payment Gateway
 *
 * Provides a Cash on Delivery Payment Gateway.
 *
 * @class    YouPayGateway
 * @extends  WC_Payment_Gateway
 * @version  1.0.0
 * @package  WooYouPay/controllers
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
	 * @codingStandardsIgnoreStart
	 */
	public function loader( \WooYouPay\bootstrap\Loader $loader ) {
	     $loader->add_action( 'woocommerce_thankyou_' . $this->id, $this,
            'thankyou_page', 10, 1 );
		$loader->add_action( 'woocommerce_email_before_order_table', $this,
            'email_instructions', 10, 3 );
		$loader->add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, $this,
            'process_admin_options', 10 );
		$loader->add_filter( 'woocommerce_payment_complete_order_status', $this,
            'change_payment_complete_order_status', 10, 3 );
		$loader->add_filter( 'woocommerce_payment_gateways', $this,
            'add_your_gateway_class', 20, 1 );

		$loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );
	}
	// @codingStandardsIgnoreEnd

	/**
	 * Add Checkout Page Script
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->youpay->plugin_slug . '-share', $this->youpay->api->getCheckoutJSUrl(), array(), $this->youpay->version, true );
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( ! parent::is_available() || ! $this->youpay->has_api_keys ) {
			return false;
		}

		if ( ! empty( $this->youpay->settings['has_payment_gateways'] ) ) {
			return true;
		}

		try {
			$store = $this->youpay->api->getStore( $this->youpay->settings['keys']->store_id );
		} catch ( \Exception $exception ) {
			// Left Blank.
		}

		if ( ! empty( $store ) && ! empty( $store->payment_gateways ) ) {
			$this->youpay->update_settings(
				array(
					'has_payment_gateways' => true,
				)
			);
			return true;
		}

		return false;
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
		$title = 'YouPay';
		if ( ! empty( $this->youpay->settings['woocommerce']['title'] ) ) {
			$title = $this->youpay->settings['woocommerce']['title'];
		}
		$this->title              = $title;
		$this->method_title       = __( 'YouPay', 'youpay' );
		$this->description        = __( 'Share a YouPay link with someone & let them pay for your order.<br> When you click Place Order you will be given a secure YouPay link to share with your payer.', 'youpay' );
		$this->method_description = __( 'Share a YouPay link with someone & let them pay for your order.', 'youpay' );
		$this->id                 = 'youpay';
		$this->icon               = YOUPAY_RESOURCE_ROOT . '/images/youpay-logo-dark-100.png';
		$this->has_fields         = false;
	}

	/**
	 * Generate WYSIWYG input field. This is a pseudo-magic method, called for each form field with a type of "wysiwyg".
	 *
	 * @since   2.0.0
	 * @see     WC_Settings_API::generate_settings_html()   For where this method is called from.
	 * @param   mixed $key
	 * @param   mixed $data
	 * @uses    esc_attr()                                  Available in WordPress core since 2.8.0.
	 * @uses    wp_editor()                                 Available in WordPress core since 3.3.0.
	 * @return  string                                      The HTML for the table row containing the WYSIWYG input field.
	 */
	public function generate_wysiwyg_html( $key, $data ) {
		$html = '';

		$id          = str_replace( '-', '', $key );
		$class       = array_key_exists( 'class', $data ) ? $data['class'] : '';
		$css         = array_key_exists( 'css', $data ) ? ( '<style>' . $data['css'] . '</style>' ) : '';
		$name        = "{$this->plugin_id}{$this->id}_{$key}";
		$title       = array_key_exists( 'title', $data ) ? $data['title'] : '';
		$value       = array_key_exists( $key, $this->settings ) ? esc_attr( $this->settings[ $key ] ) : '';
		$description = array_key_exists( 'description', $data ) ? $data['description'] : '';

		ob_start();

		include YOUPAY_PLUGIN_PATH . '/resources/views/wysiwyg.html.php';

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Output the gateway settings screen.
	 */
	public function admin_options() {
		parent::admin_options();
		if ( empty( $this->youpay->has_api_keys ) ) {
			$url = admin_url( 'admin.php?page=' . $this->youpay->plugin_slug . '_login_page&mylogin=true' );
			echo "<div class='error'><p><strong>You have not yet logged into YouPay.</strong><br><a href='$url' target='_blank'>Click here to get started.</a></p></div>";
			echo "<span style='color:#f00'>WARNING: YouPay has not yet been setup.</span><br>";
			echo "<a href='$url' target='_blank'>Click here to login</a>";
			return;
		}
		if ( empty( $this->youpay->settings['has_payment_gateways'] ) ) {
			try {
				$store = $this->youpay->api->getStore( $this->youpay->settings['keys']->store_id );
				if ( empty( $store->payment_gateways ) ) {
					$url = $this->youpay->api->app_url . "resources/payment-gateways/new?viaResource=stores&viaResourceId={$store->id}&viaRelationship=payment_gateways";
					echo "<div class='error'><p><strong>No Payment Gateway setup on YouPay.</strong><br><a href='$url' target='_blank'>Click here to get started.</a></p></div>";
					echo "<span style='color:#f00'>WARNING: NO PAYMENT GATEWAY HAS BEEN SETUP.</span><br>";
					echo "<a href='$url' target='_blank'>Click here to add a Payment Gateway</a>";
				} else {
					$this->youpay->update_settings(
						array(
							'has_payment_gateways' => true,
						)
					);
				}
			} catch ( \Exception $exception ) {
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

		$total      = (float) $order->get_total();
		$subtotal   = (float) $order->get_subtotal();
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
		} catch ( \Exception $exception ) {
			// TODO: handle error gracefully.
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

	/**
	 * Output for the order received page.
	 *
	 * @param mixed $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
		$youpay_order    = wc_get_order( $order_id );
		$recipient_name  = $youpay_order->get_billing_first_name();
		$youpay_order_id = $youpay_order->get_meta( 'youpay_order_id' );
		if ( empty( $youpay_order_id ) ) {
			return;
		}
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
//		echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
	}
}

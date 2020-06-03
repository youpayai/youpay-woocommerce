<?php
namespace WooYouPay\controllers;

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
	public function __construct()
	{
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
	 * @param \WooYouPay\bootstrap\loader $loader
	 */
	public function loader( \WooYouPay\bootstrap\loader $loader ) {
		$loader->add_action( 'woocommerce_thankyou_' . $this->id, $this, 'thankyou_page', 10, 1 );
		$loader->add_filter( 'woocommerce_payment_complete_order_status', $this, 'change_payment_complete_order_status', 10, 3 );
		$loader->add_action( 'woocommerce_email_before_order_table', $this, 'email_instructions', 10, 3 );
		$loader->add_filter( 'woocommerce_payment_gateways', $this, 'add_your_gateway_class' );
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
	 * @param $methods
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
		$this->method_title = $this->title = __( 'YouPay', 'youpay' );
		$this->description  = $this->method_description = __( 'Send the bill to someone else.', 'youpay' );
		$this->id           = 'youpay';
		$this->icon         = apply_filters( 'woocommerce_cod_icon', '' );
		$this->has_fields   = false;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled'			=> [
				'title'	   => __( 'Enable/Disable', 'woocommerce' ),
				'label'	   => __( 'Enable YouPay', 'youpay' ),
				'type'		=> 'checkbox',
				'description' => '',
				'default'	 => 'no',
			],
			'show_all_billing_fields'			=> [
				'title'	   => __( 'Enable/Disable', 'woocommerce' ),
				'label'	   => __( 'Show all billing fields', 'youpay' ),
				'type'		=> 'checkbox',
				'description' => '',
				'default'	 => 'no',
			],
		];
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

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
	 */
	public function thankyou_page( $order_id ) {
	   echo '<div class="card-title">';
	   echo '<h2>YouPay Payment Link</h2>';
	   echo '</div>';

	   echo '<div class="card-title">';
	   $link = get_site_url() . '/youpay/' . $order_id;
	   echo "<a href='$link'>$link</a>";
	   echo '</div>';
	}

	/**
	 * Change payment complete order status to completed for COD orders.
	 *
	 * @since  3.1.0
	 * @param  string		 $status Current order status.
	 * @param  int			$order_id Order ID.
	 * @param  WC_Order|false $order Order object.
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
	 * @param WC_Order $order Order object.
	 * @param bool	 $sent_to_admin  Sent to admin.
	 * @param bool	 $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $sent_to_admin || $this->id !== $order->get_payment_method() ) {
			return;
		}
		echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
	}
}
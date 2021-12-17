<?php
/**
 * 
 * The public-facing functionality of the plugin.
 *
 * @link       http://youpay.ai
 * @since      2.0.0
 *
 * @package    YouPay_WooCommerce
 * @subpackage YouPay_WooCommerce/public
 */

use YouPaySDK\Client;
use YouPaySDK\Order;
use YouPaySDK\OrderItem;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    YouPay_WooCommerce
 * @subpackage YouPay_WooCommerce/public
 * @author     Your Name <email@example.com>
 */
class YouPay_WooCommerce_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $youpay_woocommerce    The ID of this plugin.
	 */
	private $youpay_woocommerce;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $youpay_woocommerce       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $youpay_woocommerce, $version ) {

		$this->youpay_woocommerce = $youpay_woocommerce;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in YouPay_WooCommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The YouPay_WooCommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->youpay_woocommerce, plugin_dir_url( __FILE__ ) . 'css/youpay-woocommerce-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in YouPay_WooCommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The YouPay_WooCommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->youpay_woocommerce, plugin_dir_url( __FILE__ ) . 'js/youpay-woocommerce-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add the youpay banner to the product page.
	 */
	public function youpay_product_page_banner() {
		global $post;

		//get YouPay store id and access token
		$youpay_settings = get_option( 'youpay_options', array() );

		if($youpay_settings['youpay_store_id'] && $youpay_settings['youpay_access_token']){

			$youpay_client = new Client();
			$youpay_client->setToken($youpay_settings['youpay_access_token']);
			$youpay_client->setStoreID($youpay_settings['youpay_store_id']);
		}else{
			try {
				$keys = Client::auth( 'bjorn@mywork.com.au', 'q6shEcRcsJ7gJxi', 'youpaywoocomm.wpengine.com', 'woocommerce' );
				$youpay_credentials = array(
					"youpay_access_token" => $keys->access_token,
					"youpay_store_id" => $keys->store_id
				);
				update_option('youpay_options', $youpay_credentials);
			} catch ( \Exception $exception ) {
				var_dump( $exception );
				exit;
			}
			$youpay_client = new Client();
			$youpay_client->setToken( $keys->access_token);
			$youpay_client->setStoreID( $keys->store_id );
		}
		
		//get product data
		$current_product = wc_get_product($post);
		
		//get image URL
		$image_id  = $current_product->get_image_id();
		$image_url = wp_get_attachment_image_url( $image_id, 'full' );

		$product_data = array(
			'order_item_id' => $current_product->get_id(),
			'product_id'    => $current_product->get_id(),
			'title'         => $current_product->get_name(),
			'src'           => $image_url,
			'price'         => $current_product->get_price(),
			'quantity'      => '1',	// TODO get product page quantity
			'total'         => $current_product->get_price(),
		);

		$order_items[] = OrderItem::create($product_data);

		$extra_fees = array();
		try {

			$response = $youpay_client->createOrderFromArray(
				array(
					'order_id'    => rand(0,99999),
					'title'       => 'Test Order Title',
					'order_items' => $order_items,
					'extra_fees'  => $extra_fees,
					'sub_total'   => $current_product->get_price(),
					'total'       => $current_product->get_price(),
					'receiver'    => array(
						'user_id'	=> 'shopify'
					),
				)
			);
		} catch ( \Exception $exception ) {
			// TODO: handle error gracefully.
			throw $exception;
		}

		// var_dump($response);

		// try {
		// 	$keys = Client::auth( 'bjorn@mywork.com.au', 'q6shEcRcsJ7gJxi', 'youpaywoocomm.wpengine.com', 'woocommerce' );
		// 	// var_dump($keys);
		// } catch ( \Exception $exception ) {
		// 	var_dump( $exception );
		// 	exit;
		// }
		// $youpay_client = new Client();
		// $youpay_client->setToken( $keys->access_token);
		// $youpay_client->setStoreID( $keys->store_id );

		// var_dump($youpay_client);

		echo '<div id="youpay-share-box"><a href="'.$response->redirect.'" target="_blank"><img src="'.plugin_dir_url( __FILE__ ) . 'images/youpay-share-button.png"></a></div>'; // Change to desired image url

	}
	

}

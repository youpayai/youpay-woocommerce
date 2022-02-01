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
	 * new endpoint test
	 */
	public function set_youpay_endpoints() {

		//check stock
		register_rest_route( 'youpay', '/check-stock', array(
		  'methods' => 'POST',
		  'callback' => [$this, 'check_stock'],
		) );

		//check stock
		register_rest_route( 'youpay', '/check-price', array(
			'methods' => 'POST',
			'callback' => [$this, 'check_price'],
			) );
	}

	/**
	 * Check stock for products in YouPay order
	 *
	 * @param array $data Options for the function.
	 * @return json 
	 */
	function check_stock( $req ) {
		$request_data = $req->get_json_params();
		$youpay_order_id = $request_data['data']['youpay_order_id'];
		
		$youpay_order = $this->getYouPayOrder($youpay_order_id);
		$youpay_order_products = unserialize($youpay_order->products);
		$in_stock = true;
		foreach ($youpay_order_products as $product_data) {
			//check if product in stock
			$product = wc_get_product( $product_data->product_id );
			//check if stock is managed for product
			if($product->get_manage_stock()){
				//check stock qty
				if($product->get_stock_quantity()<1) {
					$in_stock = false;
				}
			}
		}
		$json = array();
		$json['data'] = array(
			'in_stock'	=> $in_stock
		);
		$json['meta'] = array(
			'success'	=> true
		);
		return new WP_REST_Response( $json, 200 );
	}

	/**
	 * Check price for products in YouPay order
	 *
	 * @param array $data Options for the function.
	 * @return json 
	 */
	function check_price( $req ) {
		$request_data = $req->get_json_params();
		$youpay_order_id = $request_data['data']['youpay_order_id'];
		
		$youpay_order = $this->getYouPayOrder($youpay_order_id);
		$youpay_order_products = unserialize($youpay_order->products);
		$in_stock = true;
		foreach ($youpay_order_products as $product_data) {
			//check if product in stock
			$product = wc_get_product( $product_data->product_id );
			//check if stock is managed for product
			if($product->get_manage_stock()){
				//check stock qty
				if($product->get_stock_quantity()<1) {
					$in_stock = false;
				}
			}
		}
		$json = array();
		$json['data'] = array(
			'in_stock'	=> $in_stock
		);
		$json['meta'] = array(
			'success'	=> true
		);
		return new WP_REST_Response( $json, 200 );
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
			$this->saveYouPayOrder($response->id, $order_items);
		} catch ( \Exception $exception ) {
			// TODO: handle error gracefully.
			throw $exception;
		}

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
	
	/**
	 * Add the youpay banner to the cart page.
	 */
	public function youpay_cart_page_banner() {
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
				// var_dump( $exception );
				exit;
			}
			$youpay_client = new Client();
			$youpay_client->setToken( $keys->access_token);
			$youpay_client->setStoreID( $keys->store_id );
		}
		
		//get cart data
		$current_cart = WC()->cart;
		foreach ( $current_cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
			$product_id = $cart_item['product_id'];
			$quantity = $cart_item['quantity'];
			$image_id  = $product->get_image_id();
			$image_url = wp_get_attachment_image_url( $image_id, 'full' );
			// $price = WC()->cart->get_product_price( $product );
			// $subtotal = WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] );
			// $link = $product->get_permalink( $cart_item );
			// $any_attribute = $cart_item['variation']['attribute_whatever'];
			// $meta = wc_get_formatted_cart_item_data( $cart_item );

			$product_data = array(
				'order_item_id' => $product_id,
				'product_id'    => $product_id,
				'title'         => $product->get_name(),
				'src'           => $image_url,
				'price'         => $product->get_price(),
				'quantity'      => $quantity,	// TODO get product page quantity
				'total'         => $product->get_price(),
			);

			$order_items[] = OrderItem::create($product_data);
		 }

		$extra_fees = array();
		try {

			$response = $youpay_client->createOrderFromArray(
				array(
					'order_id'    => rand(0,99999),
					'title'       => 'Test Order Title',
					'order_items' => $order_items,
					'extra_fees'  => $extra_fees,
					'sub_total'   => $current_cart->subtotal,
					'total'       => $current_cart->total,
					'receiver'    => array(
						'user_id'	=> 'shopify'
					),
				)
			);
			$this->saveYouPayOrder($response->id, $order_items);
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

	//save product data in youpay orders table
	function saveYouPayOrder($youpay_order_id, $order_items){
		global $wpdb;
		$table_name = $wpdb->prefix . 'youpay_orders';
		$data = array(
			'youpay_order_id'	=> $youpay_order_id,
			'products'			=> serialize($order_items),
		);
		$wpdb->insert($table_name,$data);
	}

	//save product data in youpay orders table
	function getYouPayOrder($youpay_order_id){
		global $wpdb;
		$table_name = $wpdb->prefix . 'youpay_orders';
		$youpay_order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE youpay_order_id = '".$youpay_order_id ."'" ) );
		return $youpay_order;
	}

}

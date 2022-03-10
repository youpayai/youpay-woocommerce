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

		//got to youpay order checkout
		register_rest_route( 'youpay', '/order', array(
			'methods' => 'GET',
			'callback' => [$this, 'order'],
		  ) );

		//get checkout URL
		register_rest_route( 'youpay', '/checkout', array(
			'methods' => 'POST',
			'callback' => [$this, 'checkout'],
		  ) );

		//get shipping
			register_rest_route( 'youpay', '/shipping', array(
			'methods' => 'POST',
			'callback' => [$this, 'shipping'],
		  ) );

		//check stock
		register_rest_route( 'youpay', '/create-order-single', array(
			'methods' => 'GET',
			'callback' => [$this, 'create_order_single'],
		  ) );
		
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
	 * Redirect to checkout with YP order items in cart
	 *
	 * @param array $data Options for the function.
	 * @return json 
	 */
	function order( $req ){

		$youpay_order_id = $req->get_param('youpay_order_id');

		wc()->frontend_includes();
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
		WC()->customer = new WC_Customer( get_current_user_id(), true );
		WC()->cart = new WC_Cart();

		$youpay_order = $this->getYouPayOrder($youpay_order_id);
		if($youpay_order){
			$youpay_order_products = unserialize($youpay_order->products);
			// add youpay order products to cart
			foreach ($youpay_order_products as $product_data) {
				WC()->cart->add_to_cart($product_data->product_id,$product_data->quantity);
			}
		}

		nocache_headers();
		wp_redirect(WC()->cart->get_checkout_url());
		exit;
	}

	/**
	 * Check stock for products in YouPay order
	 *
	 * @param array $data Options for the function.
	 * @return json 
	 */
	function checkout( $req ) {

		wc()->frontend_includes();
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
		WC()->customer = new WC_Customer( get_current_user_id(), true );
		WC()->order = new WC_Order();

		$json = array();
		$request_data = $req->get_json_params();
		$youpay_order_id = $request_data['data']['youpay_order_id'];
		//set shopper data (for address)
		$shopper = $request_data['data']['shopper'];
		
		$youpay_order = $this->getYouPayOrder($youpay_order_id);
		if($youpay_order){

			$json = array();
			$json['data'] = array(
				'redirect'	=> get_home_url()."/wp-json/youpay/order?youpay_order_id=".$youpay_order_id,
			);
			$json['meta'] = array(
				'success'	=> true
			);
		}else{
			//order not found
			$json['data'] = array(
				'error'	=> 'No YouPay order found'
			);
		}
		return new WP_REST_Response( $json, 200 );
	}

	/**
	 * Return available shipping methods for YouPay order
	 *
	 * @param array $req Options for the function.
	 * @return json 
	 */
	function shipping( $req ){

		$json = array();

		wc()->frontend_includes();
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
		WC()->customer = new WC_Customer( get_current_user_id(), true );
		WC()->cart = new WC_Cart();

		$request_data = $req->get_json_params();
		//set customer data (for address)
		$shopper = $request_data['data']['shopper'];

		WC()->customer->set_shipping_location($shopper['address']['country'],$shopper['address']['state'],$shopper['address']['postcode'],$shopper['address']['suburb']);

		//set cart
		$youpay_order_id = $request_data['data']['youpay_order_id'];
		$youpay_order = $this->getYouPayOrder($youpay_order_id);
		if($shopper && $shopper['address']['country'] && $shopper['address']['state'] && $shopper['address']['postcode'] && $shopper['address']['suburb']){
			if($youpay_order){
				$youpay_order_products = unserialize($youpay_order->products);
				// add youpay order products to cart
				foreach ($youpay_order_products as $product_data) {
					WC()->cart->add_to_cart($product_data->product_id,$product_data->quantity);
				}
				//find shipping methods for products and address
				$shipping_options = array();
				foreach( WC()->session->get('shipping_for_package_0')['rates'] as $method_id => $rate ){
					$rate_label = $rate->label; // The shipping method label name
					$rate_cost_excl_tax = floatval($rate->cost); // The cost excluding tax
					// The taxes cost
					$rate_taxes = 0;
					foreach ($rate->taxes as $rate_tax) {
						$rate_taxes += floatval($rate_tax);
					}
					// The cost including tax
					$rate_cost_incl_tax = $rate_cost_excl_tax + $rate_taxes;

					$shipping_options[] = array(
						'title'			=> $rate_label,
						'description'	=> $rate_label,
						'price'			=> $rate_cost_incl_tax,	//excl taxes TODO tax handling?
						'data'			=> array(
							'example'	=> 'test'
						)
					);
				}

				if(empty($shipping_options)){
					//order not found
					$json['data'] = array(
						'error'	=> 'No shipping methods found'
					);
				}else{
					
					$json['shipping_options'] = $shipping_options;
				}

			}else{
				//order not found
				$json['data'] = array(
					'error'	=> 'No YouPay order found'
				);
			}
		}else{
			//no shopper or address data
			$json['data'] = array(
				'error'	=> 'No shopper or address data provided'
			);
		}

		return new WP_REST_Response( $json, 200 );
	}



	/**
	 * Check stock for products in YouPay order
	 *
	 * @param array $data Options for the function.
	 * @return json 
	 */
	function check_stock( $req ) {

		$json = array();
		$request_data = $req->get_json_params();
		$youpay_order_id = $request_data['data']['youpay_order_id'];
		
		$youpay_order = $this->getYouPayOrder($youpay_order_id);
		if($youpay_order){
			$youpay_order_products = unserialize($youpay_order->products);
			$in_stock = true;
			foreach ($youpay_order_products as $product_data) {
				//check if product in stock
				$product = new WC_Product( $product_data->product_id );

				//check if stock is managed for product
				if($product->get_manage_stock()){
					//check stock qty

					if($product_data->variants['variant_id']){
						//variable product
						$product_variation = new WC_Product_Variation($product_data->variants['variant_id']);
						$stock_quantity = $product_variation->get_stock_quantity();
					}else{
						//check if stock is managed for product
						$stock_quantity = $product->get_stock_quantity();
					}

					if($stock_quantity < $product_data->quantity) {
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
		}else{
			//order not found
			$json['data'] = array(
				'error'	=> 'No YouPay order found'
			);
		}
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
		$price_match = true;
		foreach ($youpay_order_products as $product_data) {
			//check if product in stock

			$youpay_price = $product_data->price;
			if($product_data->variants['variant_id']){
				//variable product
				$product_variation = new WC_Product_Variation($product_data->variants['variant_id']);
				$product_price = $product_variation->get_price();
			}else{
				//simple product
				$product = new WC_Product( $product_data->product_id );
				//check if stock is managed for product
				$product_price = $product->get_price();
			}

			if($product_price != $youpay_price){
				//compare youpay price with product price
				$price_match = false;
			}
		}
		$json = array();
		$json['data'] = array(
			'price_match'	=> $price_match
		);
		$json['meta'] = array(
			'success'	=> true
		);
		return new WP_REST_Response( $json, 200 );
	}

	/**
	 * Callback function to generate YouPay order and return redirect URL.
	 *
	 * @param array $data Options for the function.
	 * @return json * 
	 */
	public function create_order_single($req) {

		//get YouPay store id and access token
		$youpay_settings = get_option( 'youpay_options', array() );

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
		$product_id = (int)$req->get_param('product_id');
		$variant_id = (int)$req->get_param('variant_id');
		$qty = (int)$req->get_param('qty');
		if($variant_id){
			//variant product
			$variant = new WC_Product_Variation($variant_id);
			$product_id = $variant->get_parent_id();
			$product_name = $variant->get_name();
			$product_price = $variant->get_price();
			//get image URL
			$image_id  = $variant->get_image_id();
			

		}else{
			//simple product
			$product = wc_get_product( $product_id );
			$product_id = $product->get_id();
			$product_name = $product->get_name();
			$product_price = $product->get_price();
			$image_id  = $product->get_image_id();
		}
		
		$image_url = wp_get_attachment_image_url( $image_id, 'full' );

		$product_data = array(
			'order_item_id' => $product_id,
			'product_id'    => $product_id,
			'variants'      => array(
				'variant_id' 	=> $variant_id,
			),
			'title'         => $product_name,
			'src'           => $image_url,
			'price'         => (float)$product_price,
			'quantity'      => $qty,
			'total'         => $qty * (float)$product_price,
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
					'sub_total'   => (float)$product_price,
					'total'       => $qty *  (float)$product_price,
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

		header('Location: '.$response->redirect);
		exit;
		
	}

	/**
	 * Add the youpay banner to the product page.
	 */
	public function youpay_product_page_banner() {
		global $post;
		$current_product = wc_get_product($post);
		$current_product_id = $current_product->get_id();

		echo '<div id="youpay-share-box"><a href="/wp-json/youpay/create-order-single/?product_id='.(int)$current_product_id.'" target="_blank"><img src="'.plugin_dir_url( __FILE__ ) . 'images/youpay-share-button.png"></a></div>'; // Change to desired image 

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
			if($cart_item['variation_id']){
				$variant_id = $cart_item['variation_id'];
		    }else{
				$variant_id = 0;
			}
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
				'variants'      => array(
					'variant_id' 	=> $variant_id,
				),
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

	function getAllShippingZones(){
		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new WC_Shipping_Zone( $raw_zone );
		}
			$zones[] = new WC_Shipping_Zone( 0 ); // ADD ZONE "0" MANUALLY
		return $zones;
	}

	/**
	* Find a matching zone for a given package.
	* @since  2.6.0
	* @uses   wc_make_numeric_postcode()
	* @param  object $package
	* @return WC_Shipping_Zone
	*/
	public static function get_zone_matching_package($package)
	{
		$country = strtoupper(wc_clean($package['destination']['country']));
		$state = strtoupper(wc_clean($package['destination']['state']));
		$continent = strtoupper(wc_clean(WC()->countries->get_continent_code_for_country($country)));
		$postcode = wc_normalize_postcode(wc_clean($package['destination']['postcode']));
		$cache_key = WC_Cache_Helper::get_cache_prefix('shipping_zones') . 'wc_shipping_zone_' . md5(sprintf('%s+%s+%s', $country, $state, $postcode));
		$matching_zone_id = wp_cache_get($cache_key, 'shipping_zones');
		if (false === $matching_zone_id) {
			$data_store = WC_Data_Store::load('shipping-zone');
			$matching_zone_id = $data_store->get_zone_id_from_package($package);
			wp_cache_set($cache_key, $matching_zone_id, 'shipping_zones');
		}
		return new WC_Shipping_Zone($matching_zone_id ? $matching_zone_id : 0);
	}

}

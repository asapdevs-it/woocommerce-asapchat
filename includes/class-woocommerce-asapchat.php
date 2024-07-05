<?php
require_once(plugin_dir_path(dirname( __FILE__ ))."/class-asapchat.php");
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       asapchat.io
 * @since      1.0.0
 *
 * @package    Woocommerce_asapchat
 * @subpackage Woocommerce_asapchat/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woocommerce_asapchat
 * @subpackage Woocommerce_asapchat/includes
 * @author     asapchat <kontakt@asapchat.io>
 */
class Woocommerce_asapchat extends Asapchat {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woocommerce_asapchat_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOOCOMMERCE_asapchat_VERSION' ) ) {
			$this->version = WOOCOMMERCE_asapchat_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woocommerce-asapchat';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woocommerce_asapchat_Loader. Orchestrates the hooks of the plugin.
	 * - Woocommerce_asapchat_i18n. Defines internationalization functionality.
	 * - Woocommerce_asapchat_Admin. Defines all hooks for the admin area.
	 * - Woocommerce_asapchat_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-asapchat-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-asapchat-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-asapchat-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-asapchat-public.php';

		$this->loader = new Woocommerce_asapchat_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woocommerce_asapchat_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woocommerce_asapchat_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woocommerce_asapchat_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_option_to_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'setting_fields' );
		$this->loader->add_action( 'admin_notice', $plugin_admin, 'show_notice' );
		$this->loader->add_action( 'init', $plugin_admin, 'handle_main_route', 10,0 );

		// $this->loader->add_action( 'admin_get_test_asapchat', $plugin_admin, 'test_asapchat');
		// $this->loader->add_action( 'admin_post_test_asapchat', $plugin_admin, 'test_asapchat');
	}
 
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woocommerce_asapchat_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woocommerce_asapchat_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function prepare_order_for_response($order){
		$billing_address = $order->get_billing_address_1().", ". $order->get_billing_address_2();
		$delivery_address = $order->get_shipping_address_1().", ". $order->get_shipping_address_2();
		$product_list = [];
		$items = $order->get_items();
		foreach ($items as $item) {
			$product = $item->get_product();
			$sku = $product->get_sku();
			$sku = $sku ? $sku : $item->get_id();
			$qty = $item->get_quantity();
			$total_price = $item->get_total();
			$price = number_format(($total_price/$qty), 2);

			$product_list[] =[
				"name"=>$product->get_title(),
				"sku"=>(string) $sku,
				"price"=>(string) $price,
				"amount"=>(string) $qty,
				"total"=>(string) $total_price,
			];
		}
		$translateStatus = $order->get_status();
		$statusName = wc_get_order_status_name($translateStatus);

		return [
			"order_number"=>(string) $order->get_id(),
			"is_paid"=> $order->is_paid(),
			"client_name"=>$order->get_billing_first_name()." ".$order->get_billing_last_name(),
			"client_email"=>$order->get_billing_email(),
			"product_list"=>$product_list,
			"total_price"=>$order->get_total(),
			"current_status"=>$statusName,
		   //  "statuses_history"=><Array>[
		   //  "name"=><String>,
		   //  "date"=><Date format "Y-m-d H=>i=>s">,
		   //  ],
		   //  "lading_number"=><String>,
			"client_phone"=>$order->get_billing_phone(),
			"delivery_address"=>$delivery_address,
			"billing_address"=>$billing_address,
			"delivery_method"=>$order->get_shipping_method(),
			"payment_method"=>$order->get_payment_method_title()
		   ];
	}

	public function get_order_response($order, $type_shop){
		$order_arr = $this->prepare_order_for_response($order);

		return [
			"message"=>"Success",
			"data"=> $order_arr,
			"type"=> $type_shop 
		];
	}

	public function getOneOrderByNumber($order_number){
		$order = wc_get_order($order_number);
		$type_shop = $this->get_type_shop();

		if(!$order) return [
			"message"=>"Error",
			"data"=>"Empty",
			"type"=> $type_shop 
		];
		return $this->get_order_response($order, $type_shop);
	}

	public function getAllOrdersByEmail($email){	
		$type_shop = $this->get_type_shop();
		$args   = array(
			'billing_email' => $email,
			'return'        => 'objects',
		);
		$orders = wc_get_orders( $args );

		if(!$orders || !count($orders)) return [
			"message"=>"Error",
			"data"=>"Empty",
			"type"=> $type_shop 
		];

		
		$orders_list = [];

		foreach ($orders as $order) {
			$orders_list[] = $this->prepare_order_for_response($order);
		}
		$response = [
			"message"=>"Success",
			"data"=> $orders_list,
			"type"=> $type_shop 
		];
		return $response;
	}


	public function getProducts($productKey){
		$type_shop = $this->get_type_shop();
		// $products = wc_get_products(array(
		// 	'search' => $productKey,
		// 	'stock_status' => 'instock',
		// 	'orderby' => 'popularity',
		// 	'limit' => 1
		// ));
		
		global $wpdb;

		$productKey = sanitize_text_field($productKey);
		$productKey = esc_sql($productKey);
		$productKey = str_replace(' ', '%', $productKey);

		$post_table = $wpdb->prefix . 'posts';
		$postmeta_table = $wpdb->prefix . 'postmeta';
			// ORDER BY pm.meta_value + 0 DESC
			// LEFT JOIN $postmeta_table pm ON p.ID = pm.post_id AND pm.meta_key = '_sale_count'


		$sql = $wpdb->prepare("
			SELECT p.ID
			FROM $post_table p
			WHERE (p.post_title LIKE %s
			OR p.post_content LIKE %s
			OR p.post_excerpt LIKE %s
			OR p.post_name LIKE %s)
			AND p.post_type = %s
			AND p.post_status = %s
			LIMIT 10",
			'%' . $productKey . '%',
			'%' . $productKey . '%',
			'%' . $productKey . '%',
			'%' . $productKey . '%',
			'product',
			'publish'
		);

		$products = $wpdb->get_results($sql);
		
		if(!$products || !count($products)) return [
			"message"=>"Error",
			"data"=>"Empty",
			"type"=> $type_shop 
		];
		$products_list = [];
		foreach ($products as $product) {
			$productid = isset($product->ID) ? $product->ID : false;
			if(!$productid) continue;

			$product = wc_get_product($productid);
			$products_list[] = [
				"id"=>$product->get_id(),
				"name"=>$product->get_title(),
				"sku"=>$product->get_sku(),
				"price"=>$product->get_price(),
				"link"=>$product->get_permalink(),
				"image"=>$product->get_image(),
				"description"=>$product->get_description(),
				"short_description"=>$product->get_short_description(),
				"categories"=>$product->get_categories(),
				"tags"=>$product->get_tags(),
				"attributes"=>$product->get_attributes(),
			];
		}
		$response = [
			"message"=>"Success",
			"data"=> $products_list,
			"type"=> $type_shop 
		];
		return $response;
	
	}

}



// // Initiating the history process registering pending status on order creation
// add_action( 'woocommerce_checkout_create_order', 'init_order_status_history', 20, 4 );
// function init_order_status_history( $order, $data ){
//     // Set the default time zone (http://php.net/manual/en/timezones.php)
//     date_default_timezone_set('Europe/Paris');

//     // Init order status history on order creation.
//     $order->update_meta_data( '_status_history', array( time() => 'pending' ) );
// }

// // Getting each status change history and saving the data
// add_action( 'woocommerce_order_status_changed', 'order_status_history', 20, 4 );
// function order_status_history( $order_id, $old_status, $new_status, $order ){
//     // Set the default time zone (http://php.net/manual/en/timezones.php)

//     date_default_timezone_set('Europe/Paris');
//     // Get order status history
//     $order_status_history = $order->get_meta( '_status_history' ) ? $order->get_meta( '_status_history' ) : array();

//     // Add the current timestamp with the new order status to the history array
//     $order_status_history[time()] = $new_status;

//     // Update the order status history (as order meta data)
//     $order->update_meta_data( '_status_history', $order_status_history );
//     $order->save(); // Save
// }
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(plugin_dir_path(dirname( __FILE__ ))."/includes/class-woocommerce-servicedesk.php");

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       servicedesk20.com
 * @since      1.0.0
 *
 * @package    Woocommerce_Servicedesk
 * @subpackage Woocommerce_Servicedesk/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_Servicedesk
 * @subpackage Woocommerce_Servicedesk/admin
 * @author     Service Desk 2.0 <kontakt@servicedesk20.com>
 */
class Woocommerce_Servicedesk_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Servicedesk_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Servicedesk_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-servicedesk-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woocommerce_Servicedesk_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_Servicedesk_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-servicedesk-admin.js', array( 'jquery' ), $this->version, false );

	}


	public function add_option_to_menu(){
		add_menu_page(
			'Service Desk 2.0', // page <title>Title</title>
			'Service Desk 2.0', // link text
			'manage_options', // user capabilities
			'service_desk', // page slug
			array($this,'render_option_menu'), // this function prints the page content
			'dashicons-images-alt', // icon (from Dashicons for example)
			4 // menu position
		);
	}

	public function render_option_menu(){
		echo "<h1>Service Desk 2.0</h1>";
		?>
<div class="wrap">
    <form method="post" action="options.php">
        <?php
												settings_fields( 'service_desk_settings' ); // settings group name
												do_settings_sections( 'service_desk' ); // just a page slug
												submit_button();
											?>
    </form>
</div>
<?php 
	
	}


	public function setting_fields(){
		$page_slug = 'service_desk';
		$option_group = 'service_desk_settings';

		add_settings_section(
			'service_desk_id', // section ID
			'API KEY', // title (optional)
			'', // callback function to display the section (optional)
			$page_slug
		);

		register_setting( $option_group, 'api_key_sd', array($this,"validate_api_key_sd"));


		add_settings_field(
			'api_key_sd',
			'Klucz z SD',
			array($this,'print_input_key'),
			$page_slug,
			'service_desk_id',
			array(
				'label_for' => 'api_key_sd',
				'class' => 'api_key_sd',
				'name' => 'api_key_sd',
				'type'=>"text"
			)
		);

 	}

	
public function print_input_key($args){
	$value = get_option("api_key_sd");
	echo "<input type='text' id='api_key_sd' name='api_key_sd' value='$value' />";
}


public function validate_api_key_sd($input){
		// if( $input < 2 ) { // some conditions
			// add_settings_error(
			// 	'service_desk_settings_errors',
			// 	'not-enough',
			// 	'The minimum amount of slides should be at least 2!',
			// 	'error' // success, warning, info
			// );
			// get the previous field value if validation fails
			// $input = get_option( 'api_key_sd' );
		// }
	 
		return $input;
}

public function show_notice() {

	if(
		isset( $_GET[ 'page' ] ) 
		&& 'service_desk' == $_GET[ 'page' ]
		&& isset( $_GET[ 'settings-updated' ] ) 
		&& true == $_GET[ 'settings-updated' ]
	) {
		?>
<div class="notice notice-success is-dismissible">
    <p>
        <strong>Service Desk 2.0 settings saved.</strong>
    </p>
</div>
<?php
	}

}

public function get_type_shop(){
	return "wordpress";
}

public function get_api_key(){
	$value = get_option("api_key_sd");
	return $value;
}

public function returnError($error = false){
	$response = [
		"message"=>"Error",
		"data"=>$error ? $error : "Brak route/key/mode",
		"type"=>""
	];
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($response);
	exit;
}

public function retrieveJsonPostData()
  {
    $rawData = file_get_contents("php://input");
    // return json_decode($rawData);
	parse_str($rawData, $result);
    return $result;
  }

public function handle_main_route(){
	$service_desk = new Woocommerce_Servicedesk20();

	$request = $_SERVER['REQUEST_URI'];
	$isRouteSd = strpos( $request, 'servicedesk/api' ) !== false;
	if(!$isRouteSd) return;

	$method = $_SERVER['REQUEST_METHOD'];
	$headers = getallheaders();

	$keyBearer = trim(isset($headers['Authorization']) ? str_replace("Bearer", "", $headers['Authorization']) : "");
	$data = $this->retrieveJsonPostData();
	$mode = isset($data["mode"]) ? $data["mode"] : false;
	$email = isset($data["email"]) ? $data["email"] : false;
	$order_number = isset($data["order_number"]) ? $data["order_number"] : false;

	if(!$keyBearer || !$mode) return $this->returnError();

	$keySDwoo = $this->get_api_key();
	$isAuth = $service_desk->check_auth($keyBearer, $keySDwoo);
	$shop_type = $service_desk->get_type_shop();

	// header('Content-Type: application/json; charset=utf-8');
	// echo json_encode([$email, $mode, $keyBearer, $isAuth]);
	// exit;
	if(!$isAuth) return $this->returnError("Błąd autoryzacji");

	switch ($mode) {
		case 'checkConnection':
			$response = $service_desk->checkConnection($isAuth, $shop_type);
			break;

		case 'getOneOrderByNumber':
			$response = $service_desk->getOneOrderByNumber($order_number);
		break;

		case 'getAllOrdersByEmail':
			$response = $service_desk->getAllOrdersByEmail($email);
		break;
		
		default:
			# code...
			break;
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($response);
	exit;
}

public function test_service_desk(){
	var_dump("works");
}

}



// fetch("https://betazmilosci.test/servicedesk/api", 
//         {
//             method: 'POST',
//             headers: {
//                'Content-Type': 'application/x-www-form-urlencoded',                 
//                'Accept': '*/*' 
//             },            
//             body: new URLSearchParams({
//                 // 'client_id':clientId,    
//                 // 'client_secret':clientSecret,
//                 // 'code':code,    
//                 // 'grant_type': grantType,
//                 // 'redirect_uri':'',
//                 // 'scope':scope
//             })
//         }
//         )
//     .then(
//         r => r.json()
//     ).then(
//         r => r.access_token
//     );
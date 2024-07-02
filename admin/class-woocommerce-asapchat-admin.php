<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
require_once(plugin_dir_path(dirname( __FILE__ ))."/includes/class-woocommerce-asapchat.php");

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       asapchat.io
 * @since      1.0.0
 *
 * @package    Woocommerce_asapchat
 * @subpackage Woocommerce_asapchat/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woocommerce_asapchat
 * @subpackage Woocommerce_asapchat/admin
 * @author     asapchat <kontakt@asapchat.io>
 */
class Woocommerce_asapchat_Admin {

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
		 * defined in Woocommerce_asapchat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_asapchat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-asapchat-admin.css', array(), $this->version, 'all' );

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
		 * defined in Woocommerce_asapchat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woocommerce_asapchat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-asapchat-admin.js', array( 'jquery' ), $this->version, false );

	}


	public function add_option_to_menu(){
		add_menu_page(
			'asapchat', // page <title>Title</title>
			'asapchat', // link text
			'manage_options', // user capabilities
			'asapchat', // page slug
			array($this,'render_option_menu'), // this function prints the page content
			'dashicons-images-alt', // icon (from Dashicons for example)
			4 // menu position
		);
	}

	public function render_option_menu(){
		echo "<h1>asapchat</h1>";
		?>
<div class="wrap">
    <form method="post" action="options.php">
        <?php
												settings_fields( 'asapchat_settings' ); // settings group name
												do_settings_sections( 'asapchat' ); // just a page slug
												submit_button();
											?>
    </form>
</div>
<?php 
	
	}


	public function setting_fields(){
		$page_slug = 'asapchat';
		$option_group = 'asapchat_settings';

		add_settings_section(
			'asapchat_id', // section ID
			'API KEY', // title (optional)
			'', // callback function to display the section (optional)
			$page_slug
		);

		register_setting( $option_group, 'api_key_asapchat', array($this,"validate_api_key_asapchat"));


		add_settings_field(
			'api_key_asapchat',
			'Klucz baer asapchat',
			array($this,'print_input_key'),
			$page_slug,
			'asapchat_id',
			array(
				'label_for' => 'api_key_asapchat',
				'class' => 'api_key_asapchat',
				'name' => 'api_key_asapchat',
				'type'=>"text"
			)
		);

 	}

	
public function print_input_key($args){
	$value = get_option("api_key_asapchat");
	echo "<input type='text' id='api_key_asapchat' name='api_key_asapchat' value='$value' />";
}


public function validate_api_key_asapchat($input){
		// if( $input < 2 ) { // some conditions
			// add_settings_error(
			// 	'asapchat_settings_errors',
			// 	'not-enough',
			// 	'The minimum amount of slides should be at least 2!',
			// 	'error' // success, warning, info
			// );
			// get the previous field value if validation fails
			// $input = get_option( 'api_key_asapchat' );
		// }
	 
		return $input;
}

public function show_notice() {

	if(
		isset( $_GET[ 'page' ] ) 
		&& 'asapchat' == $_GET[ 'page' ]
		&& isset( $_GET[ 'settings-updated' ] ) 
		&& true == $_GET[ 'settings-updated' ]
	) {
		?>
<div class="notice notice-success is-dismissible">
    <p>
        <strong>asapchat settings saved.</strong>
    </p>
</div>
<?php
	}

}

public function get_type_shop(){
	return "wordpress";
}

public function get_api_key(){
	$value = get_option("api_key_asapchat");
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
	parse_str($rawData, $result);
	if(isset($_POST['mode']) && $_POST['mode']) return $_POST;
	
	$data = json_decode($rawData) && json_decode($rawData)->mode ? json_decode($rawData) : $result;
	return (array)$data;
  }

public function handle_main_route(){
	$asapchat = new Woocommerce_asapchat();

	$request = $_SERVER['REQUEST_URI'];
	$isRouteSd = strpos( $request, 'asapchat/api' ) !== false;
	if(!$isRouteSd) return;

	$method = $_SERVER['REQUEST_METHOD'];
	$headers = getallheaders();

	$keyBearer = trim(isset($headers['Authorization']) ? str_replace("Bearer", "", $headers['Authorization']) : "");
	if(!$keyBearer){
		$keyBearer = isset($headers['authorization']) ? trim(str_replace("Bearer", "", $headers['authorization'])) : "";
	}
	$data = (array) $this->retrieveJsonPostData();
	$mode = isset($data["mode"]) ? $data["mode"] : false;
	$email = isset($data["email"]) ? $data["email"] : false;
	$order_number = isset($data["order_number"]) ? $data["order_number"] : false;
	$productKey = isset($data["productKey"]) ? $data["productKey"] : false;

	if(!$keyBearer || !$mode) return $this->returnError();

	$keySDwoo = $this->get_api_key();
	$isAuth = $asapchat->check_auth($keyBearer, $keySDwoo);
	$shop_type = $asapchat->get_type_shop();

	// header('Content-Type: application/json; charset=utf-8');
	// echo json_encode([$email, $mode, $keyBearer, $isAuth]);
	// exit;
	if(!$isAuth) return $this->returnError("Błąd autoryzacji");

	switch ($mode) {
		case 'checkConnection':
			$response = $asapchat->checkConnection($isAuth, $shop_type);
			break;

		case 'getOneOrderByNumber':
			$response = $asapchat->getOneOrderByNumber($order_number);
		break;

		case 'getAllOrdersByEmail':
			$response = $asapchat->getAllOrdersByEmail($email);
		break;

		case 'getProducts':
			$response = $asapchat->getProducts($productKey);
		break;
		
		default:
			# code...
			break;
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($response);
	exit;
}

public function test_asapchat(){
	var_dump("works");
}

}



// fetch("https://betazmilosci.test/asapchat/api", 
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
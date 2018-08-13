<?php
/*
Plugin Name: Checkbook.io
Plugin URI: www.checkbook.io
Description: WooCommerce plugin for Checkbook.io payments
Version: 0.0.2
Author: Checkbook.io
Author URI: www.checkbook.io
Text Domain: checkbook-io
Domain Path: /languages
*/
//One page 1

if ( ! defined( 'ABSPATH' ) ) exit;

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}


/**
* Add the gateway to WC Available Gateways
*
* @since 1.0.0
* @param array $gateways all available WC gateways
* @return array $gateways all WC gateways + checkbookio gateway
*/
function checkbookio_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_Checkbook';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'checkbookio_add_to_gateways' );

function add_query_vars_filter( $vars ) {
  $vars[] = "auth_code";
  return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );



/**
* Initialize the tingle.js file (for the modal)
*/
function checkbookio_customjs_init() {
	wp_enqueue_script("jquery");
	wp_enqueue_script( 'tingle-js', plugins_url( 'js/tingle.js', __FILE__ ));
	wp_enqueue_script( 'scripts-js', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ), '', true );
}
add_action('wp_enqueue_scripts','checkbookio_customjs_init');


/**
* Initialize the tingle.css file (for the modal)
*/
function checkbookio_customcss_init() {
	$plugin_url = plugin_dir_url(__FILE__ );

	wp_enqueue_style( 'style1', $plugin_url . 'css/tingle.css' );
	wp_enqueue_style( 'style2', $plugin_url . 'css/styles.css' );

}
add_action( 'wp_enqueue_scripts', 'checkbookio_customcss_init' );



/**
* Adds plugin page links
*
* @since 1.0.0
* @param array $links all plugin links
* @return array $links all plugin links + our custom links (i.e., "Settings")
*/
function checkbookio_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=checkbookio_gateway' ) . '">' . __( 'Configure', 'wc-gateway-checkbookio' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'checkbookio_gateway_plugin_links' );

/**
* Checkbook.io Payment Gateway
*
*
* @class 		WC_Gateway_Checkbook
* @extends		WC_Payment_Gateway
* @version		1.0.0
* @package		WooCommerce/Classes/Payment
* @author 		Checkbook.io
*/
add_action( 'plugins_loaded', 'checkbookio_gateway_init', 11 );


function checkbookio_gateway_init() {

	class WC_Gateway_Checkbook extends WC_Payment_Gateway {

		/**
		* Constructor for the gateway.
		*/
		public function __construct() {
			session_start();
			$this->id                 = 'checkbookio_gateway';
			$this->icon               = plugins_url( 'images/main-logo.svg', __FILE__ );
			$this->has_fields         = true;
			$this->method_title       = __( 'checkbookio', 'wc-gateway-checkbookio' );
			$this->method_description = __( 'Allows Checkbook.io payments via digital checks. '. "\n". 'In order to configure this plugin, you must set the callback URL in the Checkbook.io API dashboard to: ' . plugins_url( 'callback.php', __FILE__ ), 'wc-gateway-checkbookio' );

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->clientID     = $this->get_option('clientID');
			$this->checkRecipient = $this->get_option('checkRecipient');
			$this->recipientEmail = $this->get_option('recipientEmail');
			$this->apiSecret = $this->get_option('secretKey');
			$this->redirectURL = plugins_url( 'callback.php', __FILE__ );
			$this->sandbox = $this->get_option('sandbox');
			$this->customEmailAddress = $this->get_option('customEmailAddress');
			$this->baseURL = 'https://checkbook.io';
			if($this->sandbox == "yes"){
				$this->baseURL = 'https://sandbox.checkbook.io';
			}

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );


      if(isset($_GET['auth_code'])){
        $_SESSION['auth_code'] = get_query_var('auth_code');
      }





		}



		/**
		* Initialize Gateway Settings Form Fields
		*/
		public function init_form_fields() {

			$this->form_fields = apply_filters( 'wc_checkbookio_form_fields', array(

				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wc-gateway-checkbookio' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Checkbook.io Payments', 'wc-gateway-checkbookio' ),
					'default' => 'yes'
				),
				'sandbox' => array(
					'title'   => __( 'Sandbox Mode', 'wc-gateway-checkbookio' ),
					'type'    => 'checkbox',
					'label'   => __( 'Use Checkbook in Sandbox mode', 'wc-gateway-checkbookio' ),
					'default' => 'no'
				),
				'customEmailAddress' => array(
					'title'   => __( 'Allow User To Input Recipient', 'wc-gateway-checkbookio' ),
					'type'    => 'checkbox',
					'label'   => __( 'Allow the user to input the recipient of the check. This will add a form field for the user to place a custom email address.', 'wc-gateway-checkbookio' ),
					'default' => 'no'
				),
				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-checkbookio' ),
					'default'     => __( 'Checkbook.io Payment', 'wc-gateway-checkbookio' ),
					'desc_tip'    => true,
				),
				'clientID' => array(
					'title'       => __( 'Client ID', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'Please enter your Checkbook.io API ClientID here', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'secretKey' => array(
					'title'       => __( 'API Secret', 'wc-gateway-checkbookio' ),
					'type'        => 'password',
					'description' => __( 'Please enter your Checkbook.io API Secret here', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'checkRecipient' => array(
					'title'       => __( 'Check Recipient (Your/Your Business\' Name)', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'Please enter the name of the check recipient. (Person/business to whom payments on the site should be directed)', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'recipientEmail' => array(
					'title'       => __( 'Email (Your/Your Business\' Email)', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'Please enter the email address to which check reciepts should be sent.', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'redirectURL' => array(
					'title'       => __( 'Redirect URL (This value should not be changed, and should be set as the redirect URL in the Checkbook API Dashboard.)', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'This value should not be changed.', 'wc-gateway-checkbookio' ),
					'default'     => plugins_url( 'callback.php', __FILE__ ),
					'desc_tip'    => true,
				)
			) );
		}

		/**
		* Create the UI for the payment fields. In this case the only payment field is the button to authenticate.
		*/
		public function payment_fields()
		{

			$oauth_url = $this->baseURL . "/oauth/authorize?client_id=" . $this->clientID . '&response_type=code&state=asdfasdfasd&scope=check&redirect_uri=' . $this->redirectURL;
			if($this->customEmailAddress == "yes"){
				echo '
				<input type="text" id = "customName" name="customName" onkeyup="updateEmail(\''. plugins_url( 'emailaddress.php', __FILE__ ) .'\')"placeholder="Check Recipient Name..." value="'.$_SESSION['custom_name'].'">
				<br>
				<input type="text" id = "customEmailAddress" onkeyup="updateEmail(\''. plugins_url( 'emailaddress.php', __FILE__ ) .'\')" name="customEmailAddress" onkeyup="updateEmail(\'' . plugins_url( 'emailaddress.php', __FILE__ ).'\')" placeholder="Check Recipient Email Address..." value="'.sanitize_email($_SESSION['custom_email_address']).'">
				<br>';
			}
			?>
			<div id="txtHint">
				<?php
				if(!$_SESSION['auth_code'] == NULL)
				{
					echo '<p style="color:green;"> Authorization complete. You are now ready to make a payment via Checkbook. </p>';
				}
				else
				{
					echo ' <a id="authenticatecheckbook" href="javascript:openCheckbookModal(\''. $oauth_url .'\')"> Pay with Checkbook </a>';
				}
				?>
			</div>


			<?php
		}

		public function validate_fields()
		{
			if($_SESSION['auth_code'] == NULL)
			{
				wc_add_notice(  'Please press "Pay with Checkbook" to authorize payments. ', 'error' );
				return false;
			}
			else
			{
				return true;
			}
		}

		/**
		* Process the payment and return the result
		*
		* @param int $order_id
		* @return array
		*/
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			$data = "client_id=". $this->clientID ."&grant_type=authorization_code&scope=check&code=".$_SESSION['auth_code']."&redirect_uri=".$this->redirectURL."&client_secret=". $this->apiSecret;
			$response = wp_remote_post( $this->baseURL . "/oauth/token", array(
				'method' => 'POST',
				'timeout' => 30,
				'redirection' => 10,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded'
				),
				'body' => $data,
				'cookies' => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		}else{
			$response = $response['body'];
			$formattedData = json_decode($response, true);
			error_log(print_r($response, true));
			$bearerToken = $formattedData['access_token'];
		}
			if($this->customEmailAddress == "yes"){
				if(isset($_SESSION['custom_name']) && isset($_SESSION['custom_email_address'])){
					$this->checkRecipient = $_SESSION['custom_name'];
					$this->recipientEmail = sanitize_email($_SESSION['custom_email_address']);
					error_log($this->recipientEmail);
					add_action( 'woocommerce_email_after_order_table', 'wdm_add_shipping_method_to_order_email', 10, 2 );
					function wdm_add_shipping_method_to_order_email( $order, $is_admin_email ) {
						echo '<p><h1>Check Recipient Details:</h1><h4> Name: '  . $_SESSION['custom_name'].'  </h4><h4> Email: '. sanitize_email($_SESSION['custom_email_address']) .' </h4> </p>';
					}
				}else{
					wc_add_notice('Payment error: Please enter a check recipient name and email address.', 'error');
					return;
				}
			}

			$argdata = (array(
				'name' => $this->checkRecipient,
				'recipient' => $this->recipientEmail,
				'amount' => (float)$order->get_data()['total']
			));
			$data = json_encode($argdata);
			$response = wp_remote_post( $this->baseURL . "/v3/check/digital", array(
				'method' => 'POST',
				'timeout' => 30,
				'redirection' => 10,
				'httpversion' => '1.1',
				'blocking' => true,
				'headers' => array(
					'Authorization' => 'Bearer ' . $bearerToken,
					'Cache-Control' => 'no-cache',
					'Content-Type' => 'application/json',
				),
				'body' => $data,
				'cookies' => array()
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		} else {
			$response = $response['body'];
			if(array_key_exists('id', json_decode($response, true)))
			{
				$order->update_status( 'completed', __( 'Order Complete.', 'wc-gateway-checkbookio' ) );
				WC()->cart->empty_cart();
				session_destroy();
				return array(
					'result' 	=> 'success',
					'redirect'	=> $this->get_return_url($order)
				);
			}
			else
			{
				//There was an issue that resulted in the payment failing. Prevent the site from registering this as a compelted transaction.
				session_destroy();
				wc_add_notice( __('Payment error: Something went wrong. Please refresh the page and try again. (Error: ' . json_decode($response, true)['error']. ')', 'checkbook') . $error_message, 'error' );
				return;
			}
		}
	}
}
}

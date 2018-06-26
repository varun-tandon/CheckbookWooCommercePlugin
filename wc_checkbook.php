<?php
/*
Plugin Name: WooCommerce Checkbook.io plugin
Plugin URI: www.checkbook.io
Description: WooCommerce plugin for Checkbook.io payments
Version: 0.0.2
Author: Checkbook.io
Author URI: www.checkbook.io
Text Domain: checkbook-io
Domain Path: /languages
*/
//One page 1

defined( 'ABSPATH' ) or exit;


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
function wc_checkbookio_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_checkbookio';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_checkbookio_add_to_gateways' );



/**
 * Ensure that the session has started
 */
function sess_start() {
    if (!session_id())
    session_start();

}

add_action('init','sess_start', 1);




/**
 * Initialize the tingle.js file (for the modal)
 */
function tingle_js_init() {
    wp_enqueue_script( 'tingle-js', plugins_url( 'js/tingle.js', __FILE__ ));
}
add_action('wp_enqueue_scripts','tingle_js_init');


/**
 * Initialize the tingle.css file (for the modal)
 */
function tingle_css_init() {
    $plugin_url = plugin_dir_url(__FILE__ );

    wp_enqueue_style( 'style1', $plugin_url . 'css/tingle.css' );
}
add_action( 'wp_enqueue_scripts', 'tingle_css_init' );




/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_checkbookio_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=checkbookio_gateway' ) . '">' . __( 'Configure', 'wc-gateway-checkbookio' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_checkbookio_gateway_plugin_links' );


// function load_scripts()
// {
//     // Register the script like this for a plugin:
//     wp_register_script( 'custom-script', plugins_url( 'scripts.js', __FILE__ ) );
//     // or
//     // Register the script like this for a theme:
 
//     // For either a plugin or a theme, you can then enqueue the script:
//     wp_enqueue_script( 'custom-script' );
// }
// add_action( 'process_payment', 'load_csripts' );

/**
 * checkbookio Payment Gateway
 *
 * Provides an checkbookio Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @class 		WC_Gateway_checkbookio
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		SkyVerge
 */
add_action( 'plugins_loaded', 'wc_checkbookio_gateway_init', 11 );

function wc_checkbookio_gateway_init() {

	class WC_Gateway_checkbookio extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {

			$this->id                 = 'checkbookio_gateway';
			$this->icon               = 'https://checkbook.io/static/homepage/images/main-logo.svg';
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
      $this->redirectURL = $this->get_option('redirectURL');
			$this->sandbox = $this->get_option('sandbox');
			$this->baseURL = 'https://checkbook.io';
			if($this->sandbox == "yes"){
				$this->baseURL = 'https://sandbox.checkbook.io';
			}
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      
			add_action('init', array($this, 'startSession'), 1);

		  	// debug_to_console( "completed init" );
		  	// debug_to_console($this->clientID);
			
			$_SESSION['clientID'] = $this->clientID;
      	$_SESSION['redirectURL'] = $this->redirectURL;

        $filename = getcwd(). '/wp-content/plugins/checkbook-io-payment/api.txt';

				//open or create the file
				$handle = fopen($filename,'w+');

				//write the data into the file
				fwrite($handle, $this->apiSecret);

				//close the file
				fclose($handle);
		 $filename = getcwd(). '/wp-content/plugins/checkbook-io-payment/baseURL.txt';

				//open or create the file
				$handle = fopen($filename,'w+');

				//write the data into the file
				fwrite($handle, $this->baseURL);

				//close the file
				fclose($handle);
		}
	
		public function startSession() {
        if (!session_id()) {
            session_start();
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
					'title'   => __( 'Enable/Disable', 'wc-gateway-checkbookio' ),
					'type'    => 'checkbox',
					'label'   => __( 'Use Checkbook in Sandbox mode', 'wc-gateway-checkbookio' ),
					'default' => 'no'
				),
				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-checkbookio' ),
					'default'     => __( 'Checkbook.io Payment', 'wc-gateway-checkbookio' ),
					'desc_tip'    => true,
				),
				
				// 'description' => array(
				// 	'title'       => __( 'Description', 'wc-gateway-checkbookio' ),
				// 	'type'        => 'textarea',
				// 	'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-gateway-checkbookio' ),
				// 	'default'     => __( 'Please remit payment to Store Name upon pickup or delivery.', 'wc-gateway-checkbookio' ),
				// 	'desc_tip'    => true,
				// ),
				
				// 'instructions' => array(
				// 	'title'       => __( 'Instructions', 'wc-gateway-checkbookio' ),
				// 	'type'        => 'textarea',
				// 	'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-gateway-checkbookio' ),
				// 	'default'     => '',
				// 	'desc_tip'    => true,
				// ),
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
					'title'       => __( 'Check Recipient', 'wc-gateway-checkbookio' ),
					'type'        => 'text',
					'description' => __( 'Please enter the name of the check recipient. (Person/business to whom payments on the site should be directed)', 'wc-gateway-checkbookio' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'recipientEmail' => array(
					'title'       => __( 'Email', 'wc-gateway-checkbookio' ),
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
		
	
		public function payment_fields(){
			$oauth_url = $this->baseURL . "/oauth/authorize?client_id=" . $this->clientID . '&response_type=code&state=asdfasdfasd &scope=check&redirect_uri='
			. 
			get_site_url() 
			.

			'/wp-content/plugins/checkbook-io-payment/callback.php';
			
      $_SESSION['oauth_url'] = $oauth_url;
			// $oauth_url = "https://sandbox.checkbook.io/oauth/authorize?client_id=" . $this->clientID . '&response_type=code&scope=check&redirect_uri='. 'http://127.0.0.1:8888/wordpress/checkout/';
			?>
			<link rel="stylesheet" href= <?php '"'. plugins_url( 'css/tingle.css', __FILE__ ) .'"'?> >
			<script src=<?php '"'. plugins_url( 'js/tingle.js', __FILE__ ) .'"'?>></script>
				<div id="txtHint">
			<?php 
				if($_SESSION['authorized'] == "true"){
          echo '<p style="color:green;"> Authorization complete. You are now ready to make a payment via Checkbook. </p>';
        }else{
         echo ' <a id="authenticatecheckbook" href="javascript:test()"> Pay with Checkbook </a>';
       }
			?>
          </div>
		

			     <!-- <iframe id = "authIframe"src= <?php echo '"'.  $oauth_url .'"';?> scrolling="yes" ></iframe> -->

			

			<style>
				#authIframe{
					width:calc(80vw);
					height:calc(87vh);
				}
        .tingle-modal-box__content {
  				padding: 0.5rem 0.5rem !important;
				}
        iframe{
        	margin-bottom:0px;
        }
        .tingle-modal__close {
        	font-size:4rem !important;
        }
				.tingle-modal-box {
        width:40% !important;
        height:90% !important;
				}
			</style>
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
			<script src="https://unpkg.com/micromodal/dist/micromodal.min.js"></script>
			<script>
       var modal = new tingle.modal({
					    footer: false,
					    stickyFooter: false,
					    closeMethods: ['overlay', 'button', 'escape'],
					    closeLabel: "Close",
					    cssClass: ['custom-class-1', 'custom-class-2'],
					    onOpen: function() {
					        console.log('modal open');
					    },
					    onClose: function() {
					        console.log('modal closed');
					    },
					    beforeClose: function() {
					        // here's goes some logic
					        // e.g. save content before closing the modal
					        return true; // close the modal
					        return false; // nothing happens
					    }
					});

					// set content
					modal.setContent('<iframe id = "authIframe"src="'  +  <?php echo '"' . $oauth_url . '"'; ?> + '" scrolling="yes" ></iframe>');
			
        

       
       


							// Get the modal
				
				


			// function updateAuthStatus(){
			// 	// console.log("test");
			// 	 var xhttp = new XMLHttpRequest();
			//   xhttp.onreadystatechange = function() {
			//     if (this.readyState == 4 && this.status == 200) {
			//      document.getElementById("txtHint").innerHTML = this.responseText;
			//     }
			//   };
			//   xhttp.open("GET", "'. plugins_url( 'result.txt', __FILE__ ) .'", true);
			//   xhttp.send();
			// }
			// setInterval(updateAuthStatus, 1000);

			function test(){ 
				// document.getElementById("myDialog").showModal();
				// childWindow = window.open("'. $oauth_url . '","name" ,"height=600,width=600");
									// if (window.focus) {newwindow.focus()}
					// instanciate new modal

					

					// add a button
					

					// open modal
					modal.open();

					// close modal
					// modal.close();
            


			}
			function test2(){
				window.location.href = "'.$oauth_url.'";
			}


			
			</script> <?php
		}
	
		public function validate_fields(){
      if(!$_SESSION['authorized'] == "true"){
        wc_add_notice(  'Please press "Pay with Checkbook" to authorize payments. ', 'error' );
        return false;
      }else{
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
			
			//$str = file_get_contents(getcwd(). '/wp-content/plugins/checkbook-io-payment/bearer.json');


			


			// $request = new HttpRequest();
			// $request->setUrl('https://sandbox.checkbook.io/v3/check/digital');
			// $request->setMethod(HTTP_METH_POST);

			// $request->setHeaders(array(
			//   'Cache-Control' => 'no-cache',
			//   'Content-Type' => 'application/json'
			// ));

			// $request->setBody('{
			// 	"name":' . $this->checkRecipient . ',
			// 	"recipient":' . $this->recipientEmail . ', 
			// 	"amount": '. $order['data']['total_tax'] .'
			// }');

			// try {
			//   $response = $request->send();

			//   echo $response->getBody();
			// } catch (HttpException $ex) {
			//   echo $ex;
			//   error_log($ex);
			//   return array(
			// 	'result' 	=> 'failure',
			// 	'redirect'	=> $this->get_return_url($order)
			// );
			// }

			$curl = curl_init();
			
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $this->baseURL . "/v3/check/digital",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "{\n\t\"name\":\"". $this->checkRecipient .".\",\n\t\"recipient\":\"". $this->recipientEmail ."\", \n\t\"amount\": ". $order->get_data()['total'] . "\n}",
			  CURLOPT_HTTPHEADER => array(
			  	"Authorization: Bearer " . $_SESSION['bearerToken'] ."",
			    "Cache-Control: no-cache",
			    "Content-Type: application/json",
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  error_log("cURL Error #:" . $err);
			} else {
			   error_log($response);
        //array_key_exists('id', json_decode($response, true))
      
         if(array_key_exists('id', json_decode($response, true))){
           // Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'complete', __( 'Order Complete.', 'wc-gateway-checkbookio' ) );

			
			// Remove cart
			WC()->cart->empty_cart();


			// $oauth_url = "https://sandbox.checkbook.io/oauth/authorize?client-id=" . $this->clientID . '&response_type=code&scope=check&redirect_uri='. home_url( $wp->request );


			// $oauth_url = "https://sandbox.checkbook.io/oauth/authorize?client_id=" . $this->clientID . '&response_type=code&scope=check&redirect_uri='. 'http://127.0.0.1:8888/wordpress/wp-content/plugins/checkbook-io-payment/callback.php';
			
			      session_destroy();

			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url($order)
			);
           
         }else{
                   session_destroy();

           wc_add_notice( __('Payment error: Something went wrong. Please refresh the page and try again. (Error: ' . json_decode($response, true)['error']. ')', 'checkbook') . $error_message, 'error' );
           return;
           
         }
            
			
	
		


			
			}

			
		}

	
  } // end \WC_Gateway_checkbookio class



//   function debug_to_console( $data ) {
//     $output = $data;
//     if ( is_array( $output ) )
//         $output = implode( ',', $output);

//     echo "<script>console.log( 'Debug Objects: " . $output . "' );</script>";
// }
}
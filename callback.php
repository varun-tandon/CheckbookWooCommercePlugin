<?php
// require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
if(isset($_GET['error'])){
  ?>
	<script>
  window.parent.modal.close();
	</script>
	<?php
}else{
$authorization_code = filter_var($_GET['code'], FILTER_SANITIZE_STRING);
?>
<script>
window.parent.document.location.href = window.parent.document.location.href + "?auth_code=<?php echo $authorization_code ?>";
</script>

<?php
}
// function checkbook_io_start_session(){
//   if (!session_id())
//   session_start();
// }
// checkbook_io_start_session();
//Start the session to access $_SESSION variables
//Retrieve data from $_SESSION and server files
// $client_id = $_SESSION['clientID'];
// $client_secret = file_get_contents(realpath(__DIR__ . '../../..') . '/secure/checkbook-io/api.txt');
// $baseURL = file_get_contents(realpath(__DIR__ . '/../..') . '/checkbook-io/baseURL.txt');
// $redirect_uri= $_SESSION['redirectURL'];
// $authorization_code = $_GET['code'];

// if(!$authorization_code)
// {
//   //Likely denial or failed authoriation (redirects to the OAuth page again)
//   echo '<script> alert("Authentication failed, please try again."); location.href ="' . $_SESSION['oauth_url'] . '"  </script>';
// }
// else
// {
//   //POST for token exchange
//   $data = "client_id=". $client_id ."&grant_type=authorization_code&scope=check&code=".$authorization_code."&redirect_uri=".$redirect_uri."&client_secret=". $client_secret;
//   error_log($data);
//   $response = wp_remote_post( $baseURL . "/oauth/token", array(
//     'method' => 'POST',
//     'timeout' => 30,
//     'redirection' => 10,
//     'httpversion' => '1.1',
//     'blocking' => true,
//     'headers' => array(
//       'Content-Type' => 'application/x-www-form-urlencoded'
//     ),
//     'body' => $data,
//     'cookies' => array()
//   )
// );

// if ( is_wp_error( $response ) ) {
//   $error_message = $response->get_error_message();
//   echo "Something went wrong: $error_message";
// } else {
  // $response = $response['body'];
  // $formattedData = json_decode($response, true);
  // error_log(print_r($response, true));
  // $_SESSION['bearerToken'] = $formattedData['access_token'];
//   $_SESSION['authorized'] = "true";

//   echo '
//   <h1> Payment Complete. Please close this window if it does not do so automatically </h1>
//   <script>
//   alert("Authorization complete. Please close this window if it does not close automatically.");
//   window.parent.document.getElementById("txtHint").innerHTML = "<p style=\"color:green;\"> Authorization complete. You are now ready to make a payment via Checkbook. </p>";
//   window.parent.modal.close();
//   </script>';
// }





//   // $url = $baseURL . '/oauth/token';
//   // $handle = curl_init($url);
//   // $data = array(
//   //   'client_id' => $client_id,
//   //   'grant_type' => 'authorization_code',
//   //   'scope' => 'check',
//   //   'code' => $authorization_code,
//   //   'redirect_uri' => $redirect_uri,
//   //   'client_secret' => $client_secret
//   // );
//   // curl_setopt($handle, CURLOPT_POST, true);
//   // $test = curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
//   // $test = curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
//   // $resp = curl_exec($handle);
//   // $formattedData = json_decode($resp, true);
//   // //Pass bearer token into session
//   // $_SESSION['bearerToken'] = $formattedData['access_token'];
//   // $_SESSION['authorized'] = "true";
//   //
//   // echo '
//   // <h1> Payment Complete. Please close this window if it does not do so automatically </h1>
//   // <script>
//   // alert("Authorization complete. Please close this window if it does not close automatically.");
//   // window.parent.document.getElementById("txtHint").innerHTML = "<p style=\"color:green;\"> Authorization complete. You are now ready to make a payment via Checkbook. </p>";
//   // window.parent.modal.close();
//   // </script>';

// }
?>

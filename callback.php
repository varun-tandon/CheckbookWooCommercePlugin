<?php

function checkbook_io_start_session(){
  if (!session_id())
  session_start();
}
checkbook_io_start_session();
//Start the session to access $_SESSION variables
//Retrieve data from $_SESSION and server files
$client_id = $_SESSION['clientID'];
$client_secret = file_get_contents(realpath(__DIR__ . '/../..') . '/checkbook-io/api.txt');
$baseURL = file_get_contents(realpath(__DIR__ . '/../..') . '/checkbook-io/baseURL.txt');
$redirect_uri= $_SESSION['redirectURL'];
$authorization_code = $_GET['code'];

if(!$authorization_code)
{
  //Likely denial or failed authoriation (redirects to the OAuth page again)
  echo '<script> alert("Authentication failed, please try again."); location.href ="' . $_SESSION['oauth_url'] . '"  </script>';
}
else
{
  //POST for token exchange
  $url = $baseURL . '/oauth/token';
  $handle = curl_init($url);
  $data = array(
    'client_id' => $client_id,
    'grant_type' => 'authorization_code',
    'scope' => 'check',
    'code' => $authorization_code,
    'redirect_uri' => $redirect_uri,
    'client_secret' => $client_secret
  );
  curl_setopt($handle, CURLOPT_POST, true);
  $test = curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
  $test = curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
  $resp = curl_exec($handle);
  $formattedData = json_decode($resp, true);
  //Pass bearer token into session
  $_SESSION['bearerToken'] = $formattedData['access_token'];
  $_SESSION['authorized'] = "true";

  echo '
  <h1> Payment Complete. Please close this window if it does not do so automatically </h1>
  <script>
  alert("Authorization complete. Please close this window if it does not close automatically.");
  window.parent.document.getElementById("txtHint").innerHTML = "<p style=\"color:green;\"> Authorization complete. You are now ready to make a payment via Checkbook. </p>";
  window.parent.modal.close();
  </script>';

}
?>

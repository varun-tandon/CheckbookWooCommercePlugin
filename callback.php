<?php
//On page 2
// echo '<h1>' . $_SESSION['test'] . '</h1>';
session_start();

$client_id = $_SESSION['clientID'];
$client_secret = file_get_contents('api.txt');
$baseURL = file_get_contents('baseURL.txt');
// $redirect_uri= plugins_url( 'callback.php', __FILE__ );
$redirect_uri= $_SESSION['redirectURL'];
$authorization_code = $_GET['code'];

if(!$authorization_code){
    echo '<script> alert("Authentication failed, please try again."); location.href ="' . $_SESSION['oauth_url'] . '"  </script>';
}else{
$url = $baseURL . '/oauth/token';
// $data = array(
//     'client_id' => $client_id,
//     'grant_type'=> 'authorization_code',
//     'scope' => 'check',
//     'client_secret' => $client_secret,
//     'redirect_uri' => $redirect_uri,
//      'code' => $authorization_code
//  );
// $options = array(
//     'http' => array(
//         'method'  => 'POST',
//         'content' => json_encode($data)
//     )
// );
// $context  = stream_context_create($options);
// $result = file_get_contents($url, false, $context);
// var_dump($result);


/*

client_id: CLIENT_ID
grant_type: authorization_code
scope: check
code: AUTHORIZATION_CODE
redirect_uri: REDIRECT_URI
client_secret: SECRET_KEY

*/

$handle = curl_init($url);
$data = array('client_id' => $client_id, 
    'grant_type' => 'authorization_code',
'scope' => 'check',
'code' => $authorization_code,
'redirect_uri' => $redirect_uri,
'client_secret' => $client_secret);
curl_setopt($handle, CURLOPT_POST, true);
$test = curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
$test = curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
$resp = curl_exec($handle);
$formattedData = json_decode($resp, true);
$_SESSION['bearerToken'] = $formattedData['access_token'];
$_SESSION['authorized'] = "true";
echo '<h1> Payment Complete. Please close this window if it does not do so automatically </h1>
<script>
alert("Authorization complete. Please close this window if it does not close automatically.");
window.parent.document.getElementById("txtHint").innerHTML = "<p style=\"color:green;\"> Authorization complete. You are now ready to make a payment via Checkbook. </p>";
window.parent.modal.close();
</script>
';

// var_dump($formattedData);
// var_dump($formattedData["access_token"]);
//set the filename
// $filename = 'bearer.json';

// //open or create the file
// $handle = fopen($filename,'w+');

// //write the data into the file
// fwrite($handle,$formattedData["access_token"]);

// //close the file
// fclose($handle);


// $filename = 'result.txt';

// //open or create the file
// $handle = fopen($filename,'w+');

// //write the data into the file
// fwrite($handle,'<p style="color:green;"> Authentication Complete. </p>');

// //close the file
// fclose($handle);
}
?>


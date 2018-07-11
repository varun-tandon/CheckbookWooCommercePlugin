<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function checkbook_io_start_session(){
  if (!session_id())
  session_start();
}
checkbook_io_start_session();

if(isset($_POST['custom_email_address']) && isset($_POST['custom_name'])){
  $_SESSION['custom_email_address'] = $_POST['custom_email_address'];
  $_SESSION['custom_name'] = $_POST['custom_name'];
  echo "Session variable created";
}else{
  var_dump(http_response_code(404));
  echo "Failure";
}

die();

<?php

/* Starts the session */
function checkbook_io_start_session(){
  if (!session_id())
  session_start();
}

checkbook_io_start_session();

/* Handle custom email address and name input */
if(isset($_POST['custom_email_address']) && isset($_POST['custom_name'])){
  $_SESSION['custom_email_address'] = $_POST['custom_email_address'];
  $_SESSION['custom_name'] = $_POST['custom_name'];
  echo "Email: " . $_SESSION['custom_email_address'] . " Name: " . $_SESSION['custom_name'];
}else{
  var_dump(http_response_code(404));
  echo "Failure";
}

die();

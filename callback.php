<?php
/* Handle the OAuth Callback */

// if there is an error, close the modal
if (isset($_GET['error'])) {
  ?>
  	<script>
    window.parent.modal.close();
  	</script>
	<?php
} else {
  // get the authorization code
  $authorization_code = filter_var($_GET['code'], FILTER_SANITIZE_STRING);

  // redirect to the checkout page with the code
  ?>
    <script>
    window.parent.document.location.href = window.parent.document.location.href + "?auth_code=<?php echo $authorization_code ?>";
    </script>
  <?php
}
?>

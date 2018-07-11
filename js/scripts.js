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

modal.setContent('<iframe id = "authIframe"src="'  +  <?php echo '"' . $oauth_url . '"'; ?> + '" scrolling="yes" ></iframe>');

function openCheckbookModal()
{
    modal.open();
}

function updateEmail(){
var customEmail = $("#customEmailAddress").val();
var customName = $("#customName").val();
 $.ajax({
      url: "'. plugins_url( 'emailaddress.php', __FILE__ ). '", //window.location points to the current url. change is needed.
      type: "POST",
      data: {
        custom_email_address: customEmail,
        custom_name: customName
      },
      success: function( response){
        console.log(response);
      },
      error: function(error){
        console.log("error");
      }
});

}

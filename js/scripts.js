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

function openCheckbookModal(url)
{

    modal.setContent('<iframe id = "authIframe"src="'  +  url + '" scrolling="yes" ></iframe>');
    modal.open();
}

function updateEmail(phpfileaddress){
var customEmail = jQuery("#customEmailAddress").val();
var customName = jQuery("#customName").val();
 jQuery.ajax({
      url: phpfileaddress, //window.location points to the current url. change is needed.
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

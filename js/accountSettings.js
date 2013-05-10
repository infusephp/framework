$(function() {
	$('#delete-account-btn').click(function(e) {
		e.preventDefault();
		
		// show the modal
		$('#deleteAccountModal').modal();
		
		$('#delete-account-yes').unbind().click(function(e) {
			e.preventDefault();
			
			// hide the old modal
			$('#deleteAccountModal').modal('hide');
			
			// clear form
			$('#deleteAccountModal2 input[type=password]').val(''); 
			
			// Prompt for password
			$('#deleteAccountModal2').modal();
			
			return false;
		});
		
		return false;
	});
	
	$('#delete-account-password').keypress(function(e) {
		if( e.keyCode == 13 )
			$('#delete-account-form').submit();
	});
});
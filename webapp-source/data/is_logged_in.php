<?php
include('./bootstrap.php');

if($_SESSION['user']) {	
	header('Content-Type: text/xml');	
		
	print('<response>' . "\n");
	print('<response_state success="true" />' . "\n");
	print('<user username ="' . $_SESSION['user']['username'] . '" 
		email="' . $_SESSION['user']['email'] . '" 
		user_id="' . $_SESSION['user']['user_id'] . '" 
		full_name="' . $_SESSION['user']['full_name'] . '" />' . "\n");
	print('</response>' . "\n");		
}
else {
		header('Content-Type: text/xml');	
		
		print('<response>' . "\n");
		print('  <response_state success="false" />' . "\n");
		print('  <error error_code="no_session" message="No Current Session" />' . "\n");
		print('</response>' . "\n");
}


?>

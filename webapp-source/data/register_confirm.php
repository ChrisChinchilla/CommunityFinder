<?php
include('./bootstrap.php');

$link=mysql_connect($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW']) or die ('I cannot connect to the database because: ' . mysql_error());
$success = mysql_select_db($SITE['DB_NAME'], $link);

	$email = $_POST['email'];
	$password = md5($_POST['password']);
	$rego_code = $_POST['rego_code'];	
	
	$query = " SELECT *
							FROM `user`
							WHERE `email` = CONVERT( _utf8 '$email'
							USING latin1 )
							COLLATE latin1_swedish_ci
							AND `rego_code` = CONVERT( _utf8 '$rego_code'
							USING latin1 )
							COLLATE latin1_swedish_ci
							LIMIT 0 , 30 ";
							
	$resource = mysql_query($query, $link);

	// if we get a match, all good, set the user to active!  (should only ever be 1 record, as rego_code is very random)
	//if($resource =! false && mysql_num_rows($resource) == 1) {
	if(mysql_num_rows($resource) == 1) {
			
			$record = mysql_fetch_assoc($resource);
			
			$query = " UPDATE `user` SET `state` = 'active', `rego_code` = '' WHERE `user`.`user_id` = " . $record['user_id'] . " LIMIT 1 ; ";
			
			$success = mysql_query($query, $link);
			
			if($success) {
					// send email
					$recipient = $email; //recipient
					$subject = "VictoriaMyCommunity.org --  Registration Complete"; //subject
					$mail_body = "Hi " . $record['full_name'] . ", \n\nAll Done! You have successfully joined VictoriaMyCommunity.org.\n\nYour login details are:\nusername: " . $record['username'] . "\nemail address: " . $record['email'] . "\n\nYou can login using either of these details at http://" . $SITE['DOMAIN'] . ",\n\nThanks,\nVictoriaMyCommunity.org."; //mail body
					$header = "From: Register <register@victoria.org> \r\n"; //optional headerfields
				
					mail($recipient, $subject, $mail_body, $header); //mail command :) 
					 
					header('Content-Type: text/xml');	
						
					print('<response>' . "\n");
					print('<response_state success="true" />' . "\n");
					print('<user email="' . $record['email'] . '" full_name="' . $record['full_name'] . '" username="' . $record['username'] . '" />' . "\n");
					print('</response>' . "\n");
						
					$_SESSION['user'] = array();
					$_SESSION['user']['user_id'] = $record['user_id'];
					$_SESSION['user']['email'] = $record['email'];
					$_SESSION['user']['full_name'] = $record['full_name'];
					$_SESSION['user']['username'] = $record['username'];
			}
			
	}
	else {
	
		header('Content-Type: text/xml');	
		
		print('<response>' . "\n");
		print('  <response_state success="false" />' . "\n");
		print('  <error error_code="incorrect_rego_code" message="Incorrect Email or Error Code" />' . "\n");
		print('</response>' . "\n");

		$_SESSION['user'] =	null;
		
	}


?>

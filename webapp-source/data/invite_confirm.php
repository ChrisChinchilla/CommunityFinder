<?php
include('./bootstrap.php');

$link=mysql_connect($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW']) or die ('I cannot connect to the database because: ' . mysql_error());
$success = mysql_select_db($SITE['DB_NAME'], $link);

	$email = $_POST['email'];
	$password = md5($_POST['password']);
	$rego_code = $_POST['rego_code'];	
	$full_name = $_POST['full_name'];	
	$username = $_POST['username'];	
	
	// query that email and rego code match....
	$query_confirm_rego = " SELECT * FROM `user` 
							WHERE `email` = CONVERT( _utf8 '$email' USING latin1 ) COLLATE latin1_swedish_ci 
							AND `rego_code` = CONVERT( _utf8 '$rego_code' USING latin1 ) COLLATE latin1_swedish_ci 
							LIMIT 0 , 30 ";
	$result_confirm_rego = mysql_query($query_confirm_rego, $link);
	
	// query if username already exists
	$query_username = " SELECT *
							FROM `user`
							WHERE `username` = CONVERT( _utf8 '$username'
							USING latin1 )
							COLLATE latin1_swedish_ci
							LIMIT 0 , 30 ";
	$result_username = mysql_query($query_username, $link);
	
	
	if(mysql_num_rows($result_username) > 0) {
		header('Content-Type: text/xml');	
		print('<response>' . "\n");
		print('  <response_state success="false" />' . "\n");
		print('  <error error_code="username_exists" message="Username already exists" />' . "\n");
		print('</response>' . "\n");
		$_SESSION['user'] =	null;
	}
	else if(mysql_num_rows($result_confirm_rego) == 0) {
		header('Content-Type: text/xml');	
		print('<response>' . "\n");
		print('  <response_state success="false" />' . "\n");
		print('  <error error_code="incorrect_rego_code" message="Incorrect Email or Error Code" />' . "\n");
		print('</response>' . "\n");
		$_SESSION['user'] =	null;
	}
	else if(mysql_num_rows($result_confirm_rego) == 1) {
			$record = mysql_fetch_assoc($result_confirm_rego);
			
			$query_upd = " UPDATE `user` SET `state` = 'active', `rego_code` = '',  
										`full_name` = '$full_name', `username` = '$username', `password` = '$password'    
									    WHERE `user`.`user_id` = " . $record['user_id'] . " LIMIT 1 ; ";
			$success = mysql_query($query_upd, $link);
			
			if($success) {
					// send email
					$recipient = $email; //recipient
					$subject = "AGrowingCommunity.org --  Registration Complete"; //subject
					$mail_body = "Hi, \n\nAll Done! You have successfully signed up.\n\nBelow are your login details...\n\nusername: " . $username . "\npassword: " . $password . "\n\nYou can login using these details at HTTP://" . $SITE['DOMAIN']. " ,\nThanks."; //mail body
					$header = "From: Register <register@agrowingcommunity.org> \r\n"; //optional headerfields
				
					mail($recipient, $subject, $mail_body, $header); //mail command :) 
					 
					header('Content-Type: text/xml');	
						
					print('<response>' . "\n");
					print('<response_state success="true" />' . "\n");
					print('<user email="' . $record['email'] . '" full_name="' . $full_name . '" username="' . $username . '" />' . "\n");
					print('</response>' . "\n");
						
					$_SESSION['user'] = array();
					$_SESSION['user']['user_id'] = $record['user_id'];
					$_SESSION['user']['email'] = $record['email'];
					$_SESSION['user']['full_name'] = $record['full_name'];
					$_SESSION['user']['username'] = $record['username'];
			
					// AND set ownership on any invited markers....
					$query_invited_markers = "SELECT * FROM `marker_to_user` 
																		WHERE `user_id` = " . $_SESSION['user']['user_id'] . " 
																		AND `state` = 'invited' ";
					$result_invited_markers = mysql_query($query_invited_markers, $link);
					
					while ($row = mysql_fetch_assoc($result_invited_markers)) {							
							$query_mtu = "UPDATE `marker_to_user` 
														SET `state` = 'owner'  
														WHERE `user_id` = " . $_SESSION['user']['user_id'] . "  
														AND `marker_id` = " . $row['marker_id'];
							$result_mtu = mysql_query($query_mtu, $link);
					
							$query_mtu2 = "UPDATE `marker_to_user` 
														SET `state` = 'previous_owner' 
														WHERE `user_id` <> " . $_SESSION['user']['user_id'] . " 
														AND `marker_id` = " . $row['marker_id'];
							$result_mtu2 = mysql_query($query_mtu2, $link);
					}
			}		
	}

?>

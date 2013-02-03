<?php
include('./bootstrap.php');


if($_SESSION['user']['user_id']) {
		$link=mysql_connect($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW']) or die ('I cannot connect to the database because: ' . mysql_error());
		$success = mysql_select_db($SITE['DB_NAME'], $link);
		
		$user_id = $_SESSION['user']['user_id'];
		$title = $_POST['title'];
		$message = $_POST['message'];
		$users = $_POST['users'];		
		
		if(strchr($users, '@')) {
			//is an email, so email them instead....
			$recipient = $users; //recipient
			$subject = $title; //subject
			$mail_body = $message; //mail body
			$header = "From: Register <register@agrowingcommunity.org> \r\n" .
								"Reply-To: " . $_SESSION['user']['email'] . " \r\n" .
								"X-Mailer: PHP/" . phpversion();
			mail($recipient, $subject, $mail_body, $header); //mail command :) 	
		}
		else {
		
			//INSERT covnersation record
			$insert_conversation_query = "INSERT INTO conversation (conversation_id, title)  VALUES (NULL, '$title')";
			$insert_conversation_result = mysql_query($insert_conversation_query, $link);	
			$conversation_id = mysql_insert_id();
			
			//INSERT conversation_to_user record for sender
			$insert_conversation_to_user_query_1 = "INSERT INTO conversation_to_user (conversation_to_user_id, conversation_id, user_id, read_flag)  
				VALUES (NULL, '$conversation_id', '" . $_SESSION['user']['user_id'] . "', 1)";
			$insert_conversation_to_user_result = mysql_query($insert_conversation_to_user_query_1, $link);
		
			//INSERT conversation_to_user record for recipients	
			//first calc there user_id from the username..
			$search_query = "SELECT user_id FROM user WHERE username = '$users'";
			$search_result = mysql_query($search_query, $link);
			$row = mysql_fetch_assoc($search_result);
			
			$insert_conversation_to_user_query_2 = "INSERT INTO conversation_to_user (conversation_to_user_id, conversation_id, user_id, read_flag)  
				VALUES (NULL, '$conversation_id', '" . $row['user_id'] . "', 0)";
			$insert_conversation_to_user_result = mysql_query($insert_conversation_to_user_query_2, $link);
	
			//INSERT conversation_message record
			$insert_conversation_message_query = "INSERT INTO conversation_message (conversation_message_id, conversation_id, user_id, message)  
				VALUES (NULL, '$conversation_id', '" . $_SESSION['user']['user_id'] . "', '$message')";
			$insert_conversation_message_result = mysql_query($insert_conversation_message_query, $link);
	
			
	
			
			//OUTPUT
			header('Content-Type: text/xml');			
			print('<response>' . "\n");
			print('<response_state success="true" />' . "\n");
			//print('<conversation message="' . $message . '" title="' . $title . '" conv id="' . $conversation_id . '">' . "\n");
			//while ($row = mysql_fetch_assoc($result)) {	
			//	print('  <conversation_message conversation_message_id="' . $row['conversation_message_id'] . '" '
			//		. 'user="' . $row['user_id'] . '" timestamp="' . $row['timestamp'] . '" message	="' . $row['message'] .'"/>' . "\n");
			//}
			//print('</conversation>' . "\n");
			print('</response>' . "\n");
		}
	}
	else {
		header('Content-Type: text/xml');	
		print('<response>' . "\n");
		print('  <response_state success="false" />' . "\n");
		print('  <error message="Not logged in" />' . "\n");
		print('</response>' . "\n");
	}

?>

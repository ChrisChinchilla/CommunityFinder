<?php
include('./bootstrap.php');

	if($_SESSION['user']['user_id']) {
	
		// returns all conversations for a given user as xml document
	
		$link=mysql_connect($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW']) or die ('I cannot connect to the database because: ' . mysql_error());
		$success = mysql_select_db($SITE['DB_NAME'], $link);
		
		$main_query =  " SELECT *
					FROM conversation, conversation_to_user, user 
					WHERE user.user_id = " . $_SESSION['user']['user_id'] . " 
					AND user.user_id = conversation_to_user.user_id 
					AND conversation_to_user.conversation_id = conversation.conversation_id 
					AND conversation_to_user.deleted = 0 
					ORDER BY conversation.conversation_id DESC";
										
		$result = mysql_query($main_query, $link);
			
		header('Content-Type: text/xml');			
		print('<response>' . "\n");
		print('<response_state success="true" />' . "\n");
		
		print('<conversations>' . "\n");
		while ($row = mysql_fetch_assoc($result)) {	
			// get all participants
			$participants_query="SELECT * 
								 FROM conversation_to_user, user 
								 WHERE conversation_id = " . $row['conversation_id'] . '  
								 AND conversation_to_user.user_id = user.user_id';
								 
			$participants_result = mysql_query($participants_query, $link);
			
			$participants = '';
			while($participants_row = mysql_fetch_assoc($participants_result)) {
				if($participants_row['user_id'] != $_SESSION['user']['user_id']) {
					// not current user, so add to list
					$participants .= $participants_row['username'];
				}
			}
			// get last timestamp and message
			$last_query="SELECT * 
						 FROM conversation_message 
						 WHERE conversation_id = " . $row['conversation_id'] . " ORDER BY timestamp";
			$last_result = mysql_query($last_query, $link);
			
			while($last_row = mysql_fetch_assoc($last_result)) {
				// will be set to the last value...
				$last_message = $last_row['message'];
				$last_timestamp = $last_row['timestamp'];
			}
			
			//output
			print('  <conversation conversation_id="' . $row['conversation_id'] . '" '
				. 'title="' . $row['title'] . '" participants="' . $participants . '" read_flag="' 
				. $row['read_flag'] . '" timestamp="' . $last_timestamp . '" last_message="' . $last_message . '"/>' . "\n");
		}
		
		print('</conversations>' . "\n");
		print('</response>' . "\n");

	}
	
	else {
	
		header('Content-Type: text/xml');	
		
		print('<response>' . "\n");
		print('  <response_state success="false" />' . "\n");
		print('  <error message="Not logged in" />' . "\n");
		print('</response>' . "\n");
			
	}

?>

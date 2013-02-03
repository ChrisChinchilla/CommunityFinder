<?php	
include('./bootstrap.php');
include('./functions.php');

$marker_id = $_POST['lid'];
$title = htmlspecialchars($_POST['title']);
$desc = htmlspecialchars($_POST['desc']);
//$type = htmlspecialchars($_POST['type']);
//$subtype = htmlspecialchars($_POST['subtype']);
$lat = $_POST['lat'];
$lng = $_POST['lng'];
$tags = htmlspecialchars($_POST['tags']);
$www = htmlspecialchars($_POST['www']);
$wiki = htmlspecialchars($_POST['wiki']);
$rss = htmlspecialchars($_POST['rss']);
$datetime_on = $_POST['datetime_on'];
$start_datetime = $_POST['start_datetime']; // passed in as UTC
$end_datetime = $_POST['end_datetime']; // passed in as UTC
$alt_contact_on = $_POST['is_invite'];
$alt_contact_email = htmlspecialchars($_POST['invite_email']);
$alt_contact_url = htmlspecialchars($_POST['invite_url']);
$alt_contact_text = htmlspecialchars($_POST['invite_text']);
$address = htmlspecialchars($_POST['address']);
$phone = htmlspecialchars($_POST['phone']);

// Tidy up datetime
if($datetime_on) {
	// add leading 0 to any single diget...
	$start_datetime = preg_replace('/(\D)(\d\D)/', '${1}0$2', $start_datetime);
	$end_datetime = preg_replace('/(\D)(\d\D)/', '${1}0$2', $end_datetime);
} else {
	$start_datetime = '9999-01-01 00:00:00';
	$end_datetime = '9999-01-01 00:00:00';
}


$success = false;
$listing_id = ($_GET['listing_id']) ? $_GET['listing_id'] : '';
$marker_owner_id = ANONYMOUS_USER;
$mysqli = new mysqli($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW'], $SITE['DB_NAME']);

// get marker details to check owner is the user or anonymous 
if (!mysqli_connect_errno()) {
	$query = "SELECT DISTINCT marker_to_user.user_id
				FROM marker
				INNER JOIN marker_to_user ON marker.marker_id = marker_to_user.marker_id 
				WHERE marker_to_user.state =  'owner'
				AND marker.deleted =0 
				AND marker.marker_id = ?
				";
	if ($stmt = $mysqli->prepare($query)) {
		$stmt->bind_param("s", $listing_id);
		$stmt->execute();
		$stmt->bind_result($marker_owner_id);
		$stmt->fetch();
		$stmt->close();
	}
	else
	{
		$errMsg = "Cannot connect to DB to get user detail";
	}
}
$marker_owner_id = (int) $marker_owner_id; $marker_owner_id = ANONYMOUS_USER;

if($_SESSION['user']['user_id'] && ($marker_owner_id == $_SESSION['user']['user_id'] || $marker_owner_id == ANONYMOUS_USER)) {
	// update (add + remove) tags to reflect updated tags string  
	updateTags($marker_id, $tags, $mysqli);
	// check connection
	if (!mysqli_connect_errno()) {
		// prepare statement

		if($stmt = $mysqli->prepare("	UPDATE marker SET title = ?, description = ?, lat = ?, lng = ?, www = ?, wiki= ?, rss = ?, 
																datetime_on = ?, start_datetime = ?, end_datetime = ?, alt_contact_on = ?, alt_contact_email = ?, alt_contact_url = ?, alt_contact_text = ?, address = ?, phone = ?, last_updated = now()  
															WHERE marker.marker_id = ?")) {

			$stmt->bind_param("ssddsssississsssi", $title, $desc, $lat, $lng, $www, $wiki, $rss, $datetime_on, 
																	$start_datetime, $end_datetime, $alt_contact_on, $alt_contact_email, $alt_contact_url, $alt_contact_text, $address, $phone, $marker_id );

			$stmt->execute();
			//print($mysqli->affected_rows);
			
			//TODO: check if successfull then respond accordingly
			//if($is_invite && $invite_email) {
				if(0) {
			// CHECK INVITE EMAIL
			/*$query_invite_email =  "SELECT *
									FROM `user`
									WHERE `email` = CONVERT( _utf8 '$invite_email'
									USING latin1 )
									COLLATE latin1_swedish_ci";
			$result_invite_email = mysql_query($query_invite_email, $link);
			$num_rows = mysql_num_rows ($result_invite_email);
			
			// IF NEW EMAIL
			if($num_rows == 0) { 
				// ADD INVITE USER
				// create rego code to validate email address							
				$rego_code = md5(uniqid(rand(), true));		
				$query_add_user = "INSERT INTO `user` 
								(`user_id` , `full_name` , `username` , `email` , 	`password` , `state` , `rego_code`, `added_on` )
								VALUES ( NULL , ' ', ' ', '$invite_email ', ' ', 'invited', '$rego_code', NOW() );";			
				$invited_user_id = (mysql_query($query_add_user, $link)) ? mysql_insert_id() : null;
			}
			else if ($num_rows == 1) {
				
				//ELSE EMAIL EXISTS AND IF USER.STATE == 'invited'
				$row = mysql_fetch_assoc($result_invite_email);
				if($row['state'] == 'invited') {
					$invited_user_id = $row['user_id'];
					$rego_code = $row['rego_code'];
				}
				//	IF STATE ==  'active'
				else if($row['state'] == 'active') {
					//ERROR cant invite an existing member
					$invited_user_id = null;
				}
				
			}
			
			if($invited_user_id) {
					// ADD MARKER TO USER  (as invitee) if not already done previously...
					$query_mtu_exists = "SELECT * FROM `marker_to_user`  
														  WHERE `marker_id` = $marker_id 
														  AND `user_id` = $invited_user_id ";
					$result_mtu_exists = mysql_query($query_mtu_exists, $link);
					$num_rows = mysql_num_rows ($result_mtu_exists);									  
					if($num_rows == 0) {
						$query_mtu = "INSERT INTO `marker_to_user` (`marker_id`, `user_id`, `state`)
									VALUES ($marker_id, $invited_user_id, 'invited');";
						$result_mtu = mysql_query($query_mtu, $link);
					}	
					// INVITE EMAIL
					$recipient = $invite_email; //recipient
					$subject = "AGrowingCommunity.org --  Invitation"; //subject
					$mail_body = "Hi,\n\n" . $_SESSION['user']['username'] . " has added a listing about you to AGrowingCommunity.org.  ";
					$mail_body .= "You can view your listing by going to the following link:\n\n";
					$mail_body .= "$title ($subtype)\n";
					$mail_body .= "$description\n";
					$mail_body .= 'http://' . $SITE['DOMAIN'] . '/app.php?marker_id=' . $marker_id . "\n\n";
					$mail_body .= "You can take control of this listing, using our 1 step sign up, at the following link: \n";
					$mail_body .= 'http://' . $SITE['DOMAIN'] . '/app.php?action=invite_confirm&rego_email=' . $invite_email . '&rego_code=' . $rego_code . "\n\n";
					$mail_body .= "If you don't respond....\n";
					$header = "From: Register <register@agrowingcommunity.org> \r\n"; //optional headerfields
					mail($recipient, $subject, $mail_body, $header); //mail command :) 
					
					// INIVITE SUCCESSS RESPONSE
					header('Content-Type: text/xml');
					print('<response>');
					print('<response_state success="true" />');
					print('<invite_user_state success="true" />');
					print('<resource lid="' . $lid  . '"/>');
					print('</response>');
			}
			else {
					// INVITE ERROR RESPONSE	
					header('Content-Type: text/xml');
					print('<response>');
					print('<response_state success="true" />');
					print('<invite_user_state success="false" />');
					print('<resource lid="' . $lid . '" />');
					print('</response>');
		*/
			}
			// NORMAL RESPONSE
			else {
				$success = true;
			}
			$stmt->close(); // close statement
		}
		else{
			$errMsg = "Could not update DB";
		}
	}
	else
	{
		$errMsg = "Could not connect to DB";
	}
}
else{
	$errMsg = "Not logged in / No access";
}
$mysqli->close(); // close connection


header('Content-Type: text/xml');
print('<response>' . "\n");
if($success){
	print('<response_state success="true" />' . "\n");
	print('<resource lid="' . $lid . '" />' . "\n");
}	
else {
	print('  <response_state success="false" />' . "\n");
	print('  <error message="' . $errMsg . '" />' . "\n");
}
print('</response>');


?>

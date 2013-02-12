<?php	 	
include('./bootstrap.php');

//$type = $_POST['type'];
$subtype = $_POST['subtype'];
$name = $_POST['name'];
$description = $_POST['desc'];
$lat = $_POST['lat'];
$lng = $_POST['lng'];
$is_invite = $_POST['is_invite'];
$invite_email = $_POST['invite_email'];
$alt_contact = $_POST['alt_contact'];
$error = "";
if($_SESSION['user']['user_id']) {
	$user_id = $_SESSION['user']['user_id'];
#} # disable anynoymous adding
#else {$user_id = 133;} // 133 is anonymous user... # disable anynoymous adding

	$mysqli = new mysqli($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW'], $SITE['DB_NAME']);
	// GET subtype_id from subtype table...
	if (!mysqli_connect_errno()) {
		if($stmt = $mysqli->prepare("SELECT subtype_id FROM subtype 
								WHERE subtype.subtype_cd = ? ")) {
			$stmt->bind_param("s", $subtype);
			$stmt->execute();
			//$marker_id = $stmt->insert_id;
			$stmt->store_result();
			$stmt->bind_result($subtype_id);
			$stmt->fetch();
			$stmt->close();
		}
	}
	// Insert Marker Record
	if (!mysqli_connect_errno()) {
		if($stmt = $mysqli->prepare("INSERT INTO marker (marker_id, alt_contact_email, alt_contact_url, title, description, subtype_id, lat, lng,  www, rss) 
							VALUES (NULL , '', '' , 'title', 'description', ?, ?, ?, NULL , NULL );")) {
			$stmt->bind_param("sdd", $subtype_id, $lat, $lng);
			$stmt->execute();
			$marker_id = $stmt->insert_id;
			$stmt->store_result();
			//$success = $stmt->affected_rows;
			//print($stmt->error);
			$stmt->close();
		}
	}
	// Insert Marker to User Record
	if ($marker_id && !mysqli_connect_errno()) {
		if($stmt = $mysqli->prepare("INSERT INTO marker_to_user (marker_id, user_id, state)
								VALUES (?, ?, 'owner');")) {
			$stmt->bind_param("ii", $marker_id, $user_id);
			$stmt->execute();
			$stmt->store_result();
			$success = $stmt->affected_rows;
			$stmt->close();
		}
	}
	$mysqli->close();
}
else{
	$error = "Not authorized";
}
	if ($success) {
		header('Content-Type: text/xml');
		print('<response>');
		print('<response_state success="true" />');
		print('<marker lid="' . $marker_id . '" />');
		print('</response>');		
	}
	else {
		header('Content-Type: text/xml');
		print('<response>');
		print('<response_state success="false" />');
                print('<error message="' . $error .'" />');
		print('</response>');		
	}
?>

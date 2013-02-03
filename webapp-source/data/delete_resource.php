<?php

// TODO: change so only flags delte?????

include('./bootstrap.php');

$marker_id = $_POST['lid'];

//TODO: if marker owned, check user logged in, if anon, allow any LOGGED IN user to delete
if($_SESSION['user']['user_id']) {
	
	$user_id = $_SESSION['user']['user_id'];

	// Remove Marker to user record
	$mysqli = new mysqli($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW'], $SITE['DB_NAME']);
	if (!mysqli_connect_errno()) {
		if($stmt = $mysqli->prepare("DELETE FROM marker_to_user WHERE marker_to_user.marker_id = ? AND (marker_to_user.user_id = ? OR marker_to_user.user_id = 133) LIMIT 1")) {
			$stmt->bind_param("ii", $marker_id, $user_id);
			$stmt->execute();
			$stmt->store_result();
			$success = $stmt->affected_rows;
			$stmt->close();
		}
	}
	// TODO Remove marker to tag records...
	
	// Remove Marker record
	if ($success && !mysqli_connect_errno()) {
		if($stmt = $mysqli->prepare("DELETE FROM marker WHERE marker.marker_id = ? LIMIT 1")) {
			$stmt->bind_param("i", $marker_id);
			$stmt->execute();
			$stmt->store_result();
			$success = $stmt->affected_rows;
			$stmt->close();
		}
	}
	if ($success) {
			header('Content-Type: text/xml');
			print('<response>');
			print('<response_state success="true" />');
			print('</response>');				
	}
	else {
			header('Content-Type: text/xml');
			print('<response>');
			print('<response_state success="false" />');
			print('<error message="Not logged in" />');
			print('</response>');				
	}
	$mysqli->close();

}
?>

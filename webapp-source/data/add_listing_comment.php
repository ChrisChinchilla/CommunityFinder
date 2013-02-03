<?php	 	
include('./bootstrap.php');

$lid = $_POST['lid'];
$name = htmlspecialchars($_POST['name']);
$comment_text = htmlspecialchars($_POST['comment_text']);

if($_SESSION['user']['user_id']) {
	$user_id = $_SESSION['user']['user_id'];
} 
else {$user_id = 133;} // 133 is anonymous user...

	$mysqli = new mysqli($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW'], $SITE['DB_NAME']);
	if (!mysqli_connect_errno() && $comment_text) {
		if($stmt = $mysqli->prepare("INSERT INTO marker_comment 
										(marker_id, user_id, comment, added_on) VALUES (?, ?, ?, NOW())")) {
			$stmt->bind_param("iis", $lid, $user_id, $comment_text);
			$stmt->execute();
			$id = $stmt->insert_id;
			$stmt->store_result();
			$stmt->close();
		}
	}
	if ($id) {
		header('Content-Type: text/xml');
		print('<response>');
		print('<response_state success="true" />');
		print('<marker resource_id="' . $marker_id . '" />');
		print('</response>');		
	}
	else {
		header('Content-Type: text/xml');
		print('<response>');
		print('<response_state success="false" />');
		print('<error message="Could not save comment" />');
		print('</response>');		
	}
	$mysqli->close();
?>

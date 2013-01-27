<?php	 	
include('./bootstrap.php');

$match_text = ($_GET['q']) ? $_GET['q'] : '';
//match_text may contain multiple tags, only match last tag, the tag after last comma
$temp = explode(',', $match_text);
$match_text = array_pop($temp); 
$match_text = '%' . $match_text . '%';

$prefix = ($_GET['prefix']) ? $_GET['prefix'] : null;
$prefix = preg_replace('/\+/', ' ', $prefix);

	
		$mysqli = new mysqli($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW'], $SITE['DB_NAME']);
		if (!mysqli_connect_errno()) {
			if($prefix != null) {			
				$query =  " SELECT tag, prefix FROM tag 
									WHERE tag.tag LIKE ? 
									AND tag.prefix = ?";
				
				if($stmt = $mysqli->prepare($query)) {
						$stmt->bind_param('ss', $match_text, $prefix);
						
						$stmt->execute();		
						$stmt->store_result();
						$stmt->bind_result($tag, $prefix);
				}	
				
				header('Content-Type: text');
				while ($stmt->fetch()) {
					//$tag = ($prefix) ? $prefix . ':' . $tag : $tag;
					print($tag . "\n");
				}
				$stmt->close();
			}
			else {			
				$query =  " SELECT tag, prefix FROM tag 
									WHERE tag.tag LIKE ? AND tag.prefix = '' ";
				
				if($stmt = $mysqli->prepare($query)) {
						$stmt->bind_param('s', $match_text);
						
						$stmt->execute();		
						$stmt->store_result();
						$stmt->bind_result($tag, $prefix);
				}	
				
				header('Content-Type: text');
				while ($stmt->fetch()) {
					$tag = ($prefix) ? $prefix . ':' . $tag : $tag;
					print($tag . "\n");
				}
				$stmt->close();
			}
		}
		$mysqli->close();

?>

<?php
include('./bootstrap.php');
include('./functions.php');

$north_lat = $_GET['north_lat'];
$east_lng = $_GET['east_lng'];
$south_lat = $_GET['south_lat'];
$west_lng = $_GET['west_lng'];
$filter_subtype = ($_GET['filter_subtype']) ? $_GET['filter_subtype'] : '';
$filter_subtype = ($filter_subtype == 'ALL' || $filter_subtype == 'all' || $filter_subtype == 'null') ? '': $filter_subtype;
$filter_text = ($_GET['filter_text']) ? $_GET['filter_text'] : '';
$filter_text = ($filter_text == 'null') ? '' : $filter_text;
$username = ($_GET['username']) ? $_GET['username'] : '';
$username = ($username == 'null') ? '' : $username;
$listing_id = ($_GET['listing_id']) ? $_GET['listing_id'] : '';

$format = ($_GET['format']) ? $_GET['format'] : 'xml';


		$mysqli = new mysqli($SITE['DB_HOST'], $SITE['DB_USERNAME'], $SITE['DB_PW'], $SITE['DB_NAME']);
		if (!mysqli_connect_errno()) {
				$types = '';
				$bindings = '';
				$query =  " SELECT DISTINCT marker.lat, marker.lng, marker.title, marker.description, marker.marker_id, user.username, type.type_cd, subtype.subtype_cd, subtype.subtype_name, marker.www, marker.wiki, marker.rss, marker.datetime_on, marker.start_datetime, marker.end_datetime, marker.alt_contact_on, marker.alt_contact_email, marker.alt_contact_url, marker.alt_contact_text, marker.address, marker.phone, marker_to_user.user_id
							FROM marker
							INNER JOIN marker_to_user ON marker.marker_id = marker_to_user.marker_id
							INNER JOIN user ON marker_to_user.user_id = user.user_id
							INNER JOIN subtype ON marker.subtype_id = subtype.subtype_id
							INNER JOIN  `type` ON subtype.type_id = type.type_id
							LEFT OUTER JOIN marker_to_tag ON marker.marker_id = marker_to_tag.marker_id
							LEFT OUTER JOIN tag ON marker_to_tag.tag_id = tag.tag_id
							WHERE marker_to_user.state =  'owner'
							AND marker.deleted =0 ";

				////////// bounding box					
				if ($north_lat && $south_lat && $west_lng && $east_lng) {
					if($west_lng < $east_lng) {
						$query .= "		AND marker.lat BETWEEN ? AND ?
												AND marker.lng BETWEEN ? AND ? ";
					}
					else {
						$query .= "		AND marker.lat BETWEEN ? AND ?
												AND ((marker.lng BETWEEN ? AND 180) OR (marker.lng BETWEEN -180 AND ?))";	
					}
					$types .= 'dddd';
					$bindings .= '$south_lat, $north_lat, $west_lng, $east_lng, ';
				}

				////////// subtype restricted				
				if ($filter_subtype  != '' ) { 
					if( strpos($filter_subtype, ',') ) {
						$subtype_array = explode(',', $filter_subtype);
						$query .= "AND subtype.subtype_cd IN (";
						for($i = 0; $i < count($subtype_array); $i++) {
								$query .= "?,";
								$types .= 's';
								$bindings .= '$subtype_array[' . $i . '], ';
						}  
						$query = rtrim($query, ','); // remove last , before close braket
						$query .= ") "; 
					}
					else {
						$query .= "AND subtype.subtype_cd IN (?) ";
						$types .= 's';
						$bindings .= '$filter_subtype, ';
					}
				}
				////////// search text
				if ($filter_text  != '' ) { 
					if(preg_match('/:/', $filter_text)) {
						$tag_array = explode(':', $filter_text);
						$prefix = $tag_array[0];
						$tag = '%' . $tag_array[1] . '%';
						$query .= "AND marker.marker_id in 
									(SELECT marker_to_tag.marker_id FROM marker_to_tag
									   INNER JOIN tag ON marker_to_tag.tag_id = tag.tag_id
									   WHERE tag.tag LIKE ? AND tag.prefix = ?) ";
						$types .= 'ss';
						$bindings .= '$tag, $prefix';
					}
					else {
						$filter_text = '%' . $filter_text . '%';
						$query .= "AND (marker.title LIKE ? 
											OR marker.description LIKE ? 
											OR subtype.subtype_name LIKE ? 
											OR marker.marker_id in 
												(SELECT marker_to_tag.marker_id FROM marker_to_tag
												   INNER JOIN tag ON marker_to_tag.tag_id = tag.tag_id
												   WHERE tag.tag LIKE ? )) ";
						$types .= 'ssss';
						$bindings .= '$filter_text, $filter_text, $filter_text, $filter_text, ';
					}
					
				}
				////////// username
				if ($username) {
					if( strpos($username, ',') ) {
						$username_array = explode(',', $username);
						$query .= "AND user.username IN (";
						for($i = 0; $i < count($username_array); $i++) {
								$query .= "?,";
								$types .= 's';
								$bindings .= '$username_array[' . $i . '], ';
						}  
						$query = rtrim($query, ','); // remove last , before close braket
						$query .= ") "; 
					}
					else {
						$query .= "AND user.username = ? ";
						$types .= 's';
						$bindings .= '$username, ';
					}					
				}
				////////// listing__id
				if ($listing_id  != '' ) { 
						$query .= "AND marker.marker_id = ? ";
						$types .= 'i';
						$bindings .= '$listing_id, ';
				}			
				$query .= "ORDER BY marker.lat, marker.lng ";
				
				
				if($stmt = $mysqli->prepare($query)) {
						$bindings = preg_replace('/,\s$/', '', $bindings);
						$bind_php = '$stmt->bind_param("' . $types . '", ' . $bindings . ');';
						eval($bind_php); // OH THE HORROR
						$stmt->execute();		
						$stmt->store_result();
						$stmt->bind_result($lat, $lng, $title, $description, $marker_id, $username, $type_cd, $subtype_cd, $subtype_name, $www, $wiki, $rss, 
							$datetime_on, $start_datetime, $end_datetime, $alt_contact_on, $alt_contact_email, $alt_contact_url, $alt_contact_text, $address, $phone, $user_id);
				}	
				//print ($mysqli->error);

				// ////////////////////////////////
				// print start of file block				
				if ($format == 'xml') {	
						header('Content-Type: text/xml');
						print('<markers>' . "\n");
				}
				else if ($format == 'json') {
						header("Content-Type: text/plain");
						$markers = array();
						$markers["markers"] = array();
						
				}
				else if ($format == 'csv') {
						header("Content-Type: text/plain");	
						print('"name","description","lat","lng","added by","category","phone","address","www","rss","tags", "user_id", "edit", "delete"' . "\n");
				}
				else if ($format == 'kml') {
						header('Content-Type: text/xml');
						print('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
						print('<kml xmlns="http://earth.google.com/kml/2.2">	' . "\n");			
				}
				if ($format == 'rss') {	
						header('Content-Type: text/xml');
						print('<rss version="2.0">' . "\n");
						print('<channel>' . "\n");
						print('<title>ASP @ BellaOnline</title>' . "\n");
						print('<link>http://site.com</link>' . "\n");
						print('<description></description>' . "\n");
						print('<language>en-us</language>' . "\n");
						print('<copyright>Copyright 2010. Creative Commons Share Alike</copyright>' . "\n");
						print('<ttl>240</ttl>' . "\n");
						print('<image>' . "\n");
						print('<url>http://www.bellaonline.com/images/bella.gif</url>' . "\n");
						print('<title>Site Name</title>' . "\n");
						print('<link>http://site.com</link>' . "\n");
						print('</image>	' . "\n");			
				}

				// ////////////////////////////////
				// print middle of file block, ie one for each listing
				while ($stmt->fetch()) {
					$user_id = (int) $user_id;
					if($user_id == 0) $user_id = ANONYMOUS_USER;
					$theUser = (int) @$_SESSION['user']['user_id'];

					$edit = false;
					$delete = false;
					 // Delete listing	Admin / Owner / (logged in users can delete anonymous)
					 // Edit Lisitng	Admin / Owner / Anyone for anon
					if($user_id == ANONYMOUS_USER) $edit = true;
					if($user_id == ANONYMOUS_USER && $theUser != 0) $delete = true;
					if($theUser == $user_id) {
						$edit = true;
						$delete = true;
					}

					// do the subquery to get tags...
					$query = 'SELECT tag, prefix FROM tag, marker_to_tag 
										  WHERE tag.tag_id = marker_to_tag.tag_id 
										  AND     marker_to_tag.marker_id = ? 
										  ORDER BY prefix, tag';
					if($stmt_inner = $mysqli->prepare($query)) {
								
						$stmt_inner->bind_param('i', $marker_id);
						$stmt_inner->execute();		
						$stmt_inner->store_result();
						$stmt_inner->bind_result($tag, $prefix);
						$tags = '';
						
						while($stmt_inner->fetch()) {
								$tag = ($prefix) ? $prefix . ':' . $tag : $tag;
								$tags .= ($tags == '') ? $tag : ',' . $tag;
						}	
					}


									
					if ($format == 'xml') {	
							printf('<marker lat="%f" lng="%f" name="%s" description="%s" lid="%d" username="%s" type="%s" subtype="%s" subtype_name="%s" www="%s" wiki="%s" rss="%s" datetime_on="%d" start_datetime="%s" end_datetime="%s" is_invite="%d" invite_email="%s" invite_url="%s" invite_text="%s" address="%s" phone="%s" tags="%s" user_id="%s" edit="%s" delete="%s" />', 
											$lat, $lng, stripslashes($title), stripslashes($description), $marker_id, $username, 
											$type_cd, $subtype_cd, $subtype_name, $www, $wiki, $rss, $datetime_on, $start_datetime, $end_datetime, $alt_contact_on, 
											$alt_contact_email, $alt_contact_url, $alt_contact_text, $address, $phone, $tags, $user_id, $edit, $delete);
							print("\n");
					}
					else if ($format == 'json') {
						$markers["markers"][] = array("lat" => $lat, "lng" => $lng, "name" => stripslashes($title), "desc" => stripslashes($description), "lid" => $marker_id, "username" => $username, 
											"type" => $type_cd, "subtype" => $subtype_cd, "subtype_name" => $subtype_name, "www" => $www, "wiki" => $wiki, "rss" => $rss, 
											"ac_on" => $alt_contact_on, "ac_email" => $alt_contact_email, "ac_url" => $alt_contact_url, "ac_text" => $alt_contact_text, "address" => $address, "phone" => $phone, "tags" => $tags, "user_id" => $user_id, "edit" => $edit, "delete" => $delete);
					}
					else if ($format == 'csv') {
							printf('"%s","%s","%f","%f","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"', 
								stripslashes($title), stripslashes($description), $lat, $lng, 
								$username, $subtype_name, $phone, $address, $www, $rss, $tags, $user_id, $edit, $delete);
							print("\n");
					}	
					else if ($format == 'kml') {
							print('<Placemark>' . "\n");
							print('<name>' . stripslashes($title) . '</name>' . "\n");
							print('<description>' . stripslashes($description) . '</description>' . "\n");
							print('<Point>' . "\n");
							print('<coordinates>' . $lat . ',' . $lng . '</coordinates>' . "\n");
							print('</Point>' . "\n");
							print('</Placemark>	' . "\n");
					}
					else if ($format == 'rss') {
						print('<item>' . "\n");
						print('<title>' . stripslashes($title) . '</title>' . "\n");
						print('<link>http://www.bellaonline.com/articles/art29843.asp</link>' . "\n");
						print('<description>' . stripslashes($description) . '</description>' . "\n");
						print('<pubDate></pubDate>' . "\n");
						print('</item>' . "\n");
					}
				}

				// ////////////////////////////////
				// print end of file block				
				if ($format == 'xml') {	
						print('</markers>' . "\n");
				}
				else if ($format == 'json') {
					print(json_encode($markers));
				}
				else if ($format == 'csv') {
						//nothing
				}	
				else if ($format == 'kml') {
						print('</kml>' . "\n");
				}
				else if ($format == 'rss') {
						print('</channel>' . "\n" . '</rss>' . "\n");
				}
				
				$stmt->close();
		}
		$mysqli->close();

?>

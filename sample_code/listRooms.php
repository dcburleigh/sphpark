<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Test One</title>

<link href="../styles/deldev.css" rel="stylesheet" type="text/css" />

<link href="sparkui.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<p>
		<a href="">Start over</a>
	</p>
	<?php
	require_once 'config_inc.php';
	#print "form data: ";
	#print_r($_POST);

	$max_items = 10;
	$msg_id = '';
	$email = '';
	$person_id = '';
	$room_id;
	$match_value = '';

	if (isset($_POST['match_value'])){
		$match_value = $_POST['match_value'];
	}
	if (isset($_POST['max_items'])){
		$max_items = $_POST['max_items'];
	}

	require_once 'SparkClient.php';
	$t = $spark_token;
	if ( $user_access_token) {
		$t = $user_access_token;
	}
	$sp = new SparkClient($t);

	if ( $sp->error ) {
		die("startup failed: " . $sp->error);
	}

	if ( isset($_POST['list_rooms'])){

		print "<h3>Rooms</h3>";

		#$msg = $sp->getRooms();
		#		$msg = $sp->getRooms( array('showSipAddress' => 1));
		$limit = $max_items;
		if ($match_value){
			# don't limit
			$limit = '';
		}

		print " match=$match_value limit=$limit";
		$msg = $sp->getRooms( array('showSipAddress' => 1,  'max' => $limit));

		if ( $sp->error ) {
			die("get failed: " . $sp->error);
		}
		if ( 0 ) {
			print "<div class='raw_content'>";
			print_r( $sp->rooms);
			print "</div>";
		}


		print "<p>" . $sp->num_rooms . " Rooms</p>";


		print '<form method="post" action="">';
		print "<ul>";
		$n = 0;
		foreach ($sp->rooms as $room  ){
			if ($n >= $max_items ){
				break;
			}

			if ( $match_value && ! preg_match("/$match_value/i", $room->{'title'} ) ) {
					continue;
				}
				$b = '<input type=submit name="room_id" value="' . $room->{'id'} . '" />';

				$n++;
				print "<li>"
				. "$n) "
				#. $b
				. $room->{'title'}
				. " SIP: " . $room->{'sipAddress'}
				#. $room->{'id'}
				. "</li>";
		}
		print "</ul>";
		print '</form>';

	} // get

	?>


	<form method="post" action="">

		Match: <input type=text name=match_value
			value="<?php echo $match_value; ?>" /> Max: <input type=test
			name="max_items" value="<?php echo $max_items; ?>" /> <input
			type="submit" name="list_rooms" value="List rooms" />
	</form>

</body>
</html>

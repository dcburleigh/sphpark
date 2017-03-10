<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>List spark users</title>

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

	$verbose = 0;
	$max_items = 10;
	$msg_id = '';
	$match_email = '';
	$person_id = '';
	$match_value = '';

	if (isset($_POST['match_value'])){
		$match_value = $_POST['match_value'];
	}
	if (isset($_POST['match_email'])){
		$match_email = $_POST['match_email'];
	}
	if (isset($_POST['max_items'])){
		$max_items = $_POST['max_items'];
	}
	$verbose_ch = '';
	if (isset($_POST['verbose'])){
		$verbose_ch = 'CHECKED';
		$verbose = $_POST['verbose'];
	}

	require_once 'SparkClient.php';
	$t = $spark_token;
	if ( $user_access_token) {
		$t = $user_access_token;
	}
	$sp = new SparkClient($t);
	/*
	 * verify
	$ok = $sp->validateToken();
	$me = $sp->getMe();
	//print_r($me);
	print("<p>valid: $ok Me: " . $me->displayName . "</p>");
	
	$me = $sp->getPerson('me');
	//print_r($me);
	print("<p> Me: " . $me->displayName . "</p>");
	
	$id = 'Y2lzY29zcGFyazovL3VzL1dFQkhPT0svNzNjMmJjNTktZjQ0Mi00YmIwLTk0ZWQtMjIzYTJhMWM1ZDZj';
	$msg = $sp->getMessage();
	print_r($msg);
	
	$text = '{"id":"Y2lzY29zcGFyazovL3VzL1dFQkhPT0svNzNjMmJjNTktZjQ0Mi00YmIwLTk0ZWQtMjIzYTJhMWM1ZDZj","name":"darin3","targetUrl":"http://www.silurian.org/spark/hook_test.php","resource":"messages","event":"created","filter":"roomId=Y2lzY29zcGFyazovL3VzL1JPT00vNmViOGNjYTAtY2MyMS0xMWU1LWFkY2ItODM5OWFiYjg0ZGRh","orgId":"Y2lzY29zcGFyazovL3VzL09SR0FOSVpBVElPTi9udWxs","createdBy":"Y2lzY29zcGFyazovL3VzL1BFT1BMRS9jODg5NjJhNC03OTIwLTQzNjItOWI4MC01ZjRmMjE5ZmVhZGE","appId":"Y2lzY29zcGFyazovL3VzL0FQUExJQ0FUSU9OL251bGw","status":"active","created":"2016-02-05T23:18:30.339Z","actorId":"Y2lzY29zcGFyazovL3VzL1BFT1BMRS9jODg5NjJhNC03OTIwLTQzNjItOWI4MC01ZjRmMjE5ZmVhZGE","data":{"id":"Y2lzY29zcGFyazovL3VzL01FU1NBR0UvMzZmMmQwZjAtZjkzMy0xMWU2LWJiNDAtZDE1MjIzMWYyZTcy","roomId":"Y2lzY29zcGFyazovL3VzL1JPT00vNmViOGNjYTAtY2MyMS0xMWU1LWFkY2ItODM5OWFiYjg0ZGRh","roomType":"group","personId":"Y2lzY29zcGFyazovL3VzL1BFT1BMRS9jODg5NjJhNC03OTIwLTQzNjItOWI4MC01ZjRmMjE5ZmVhZGE","personEmail":"darin.burleigh@cdw.com","created":"2017-02-22T19:15:04.191Z"}}';
	$obj = json_decode($text);

	print_r($obj);
	print " <br/>id=" . $obj->id . " ID=";
	print_r( $obj->data->id);
	 */
	
	if ( $sp->error ) {
		die("startup failed: " . $sp->error);
	}

	if ( isset($_POST['list_members'])){
	
		if ( $_POST['room_name']){
			
			
			$room_name = $_POST['room_name'];
			$sp->getRooms( );
			// print_r($msg);
			print " Nrooms=" . $sp->num_rooms;
			foreach ( $sp->rooms as $room){
				if ( $room->{'title'} == $room_name){
					print_r($room);
					$room_id = $room->id;
					#$room_id = $room->{'id'};
					#print "room id= $room_id ==" . $room->id;
					break;
				}
			}
			
		}
		elseif($_POST['room_id']){
			$room_id = $_POST['room_id'];
			$room = $sp->getRoom( $room_id );
			$room_name = $room->title;
		}
		print "room: $room_id name: $room_name";
		print "<h3>members</h3>";
		$msg = $sp->getMemberships( array( 'roomId' => $room_id ) );
		
		if ( $sp->error ) {
			die("get failed: " . $sp->error);
		}
		if ( $verbose ) {
			print "<div class='raw_content'>";
			print_r($msg);
			print_r( $sp->memberships);
			print "</div>";
		}

		print " N=" . $sp->num_memberships;
		foreach ( $sp->memberships as $m ) {
		$p = $sp->getPerson ( $m->{'personId'} );
		print  "<br/>" . $p->{'displayName'} . "\n";
		}
		
		
	}
	
	if ( isset($_POST['list_people'])){

		print "<h3>People</h3>";

		#$msg = $sp->getPeople();
		$limit = $max_items;
		if ($match_value){
			# don't limit
			$limit = '';
		}

		print " match=$match_value limit=$limit";
		$msg = $sp->getPeople( array( 'displayName' => $match_value, 'email' => $match_email,  'max' => $limit));

		if ( $sp->error ) {
			die("get failed: " . $sp->error);
		}
		if ( $verbose ) {
			print "<div class='raw_content'>";
			print_r( $sp->people);
			print "</div>";
		}


		print "<p>" . $sp->num_people . " People</p>";


		print '<form method="post" action="">';
		print "<ul>";
		$n = 0;
		foreach ($sp->people as $person ){
			if ($n >= $max_items ){
				break;
			}

			$link = '  Show <input type=submit name="person_id" value="' . $person->{'id'} . '" />';

			$name = $person->{'firstName'} . ' ' . $person->{'lastName'};
			$name .= '<span class="displayName">' . $person->{'displayName'} . '</span>';

			$id = '<input name=personId value="' . $person->{'id'} . '" />';
			$ne = count( $person->{'emails'} );
			$nr = count( $person->{'roless'} );

			$n++;
			print "<li>"
				. "$n) ". $name

				. "  $ne emails "
				. " Email: " . implode(', ', $person->{'emails'})
				. " $id "
					
				. " Status: " . $person->{'status'}
			. ' (' . $person->{'lastActivity'} . ') '

					. "</li>"
					;
		}
		print "</ul>";
		print '</form>';

	} // get

	?>

<hr>
	

	<form method="post" action="">

		Match name: <input type=text name=match_value
			value="<?php echo $match_value; ?>" /> Email: <input type=text
			name=match_email value="<?php echo $match_email; ?>" /> Max: <input
			type=test name="max_items" value="<?php echo $max_items; ?>" />
		Verbose: <input type=test name="verbose"
			value="<?php echo $verbose; ?>" /> <input type="submit"
			name="list_people" value="List people" />
	</form>
	
	
<hr>
	<form method="post" action="">

		<div>Room ID: <input type=text name="room_id"
			value="<?php echo $room_id; ?>" /> </div>
			
			<div>Room Name: <input type=text
			name="room_name" value="<?php echo $room_name; ?>" /> </div>
			
		Verbose: <input type=checkbox name="verbose" <?php echo $verbose_ch; ?> />
		<input type="submit"
			name="list_members" value="List room members" />
	</form>

</body>
</html>

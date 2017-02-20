<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Show Thread</title>

<link href="deldev.css" rel="stylesheet" type="text/css" />

<link href="sparkui.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<p>
		<a href="">Start over</a>
	</p>
	<?php
	require_once 'config_inc.php';
	require_once 'utils.php';

	require_once 'StaffDB.php';

	#print "form data: ";
	#print_r($_POST);

	// filter arguments
	$max_items = 10;
	$end_message_id = '';
	$match_value = '';

	/*
	 *  defaults
	*/
	$show_thread = 0;
	$room_id = 'Y2lzY29zcGFyazovL3VzL1JPT00vMjdiZDk2OTAtYzNhMy0xMWU1LWEzZTgtNjE3NGNlNGM5Njc0';
	$room_name = 'Darin Test';
	//
	$room_id = 'Y2lzY29zcGFyazovL3VzL1JPT00vODg0YmUwYmQtOGQyNS0zMDU5LTlmNWEtY2Q1OGUxNDc1ODgz';
	$room_name = "Mike R";
	//
	$room_id = 'Y2lzY29zcGFyazovL3VzL1JPT00vNTVhYThmMjAtYWI4NS0xMWU2LThjNDItNmI1ZTU3NDg1NTBm';
	$room_name = '3 of us';
	//
	$room_id = 'Y2lzY29zcGFyazovL3VzL1JPT00vNGFhODcxYTAtMjlhMy0xMWU2LThjMTQtNzE0NmJhZDBmOGE5';
	$room_name = 'bot 4';

	/*
	 * args
	*/
	if (isset($_POST['end_message_id'])){
		$end_message_id = $_POST['end_message_id'];
		$show_thread = 1;
	}
	if (isset($_POST['end_date'])){
		$end_date = $_POST['end_date'];
		$show_thread = 1;
	}
	if (isset($_POST['end_days'])){
		$end_days = $_POST['end_days'];
		$show_thread = 1;
	}
	if (isset($_POST['match_value'])){
		$match_value = $_POST['match_value'];
		$show_thread = 1;
	}
	if (isset($_POST['max_items'])){
		$max_items = $_POST['max_items'];
		$show_thread = 1;
	}

	require_once 'SparkThread.php';
	if ( isset($user_access_token)){
		$t = $user_access_token;
	}
	elseif( isset($spark_token)){
		$t = $spark_token;
	}
	$sp = new SparkThread($t);
	if (0){

		$sp->setDaysRange(120, 60 );
		if ( $sp->error ){
			raise_error("thread: failed " . $sp->error);
		}

		print "<p>Begin: " . $sp->begin_date . " End:" . $sp->end_date . "</p>";
		print "<p>Days Begin: " . $sp->begin_days. " End:" . $sp->end_days . "</p>";

		$sp->clearFilter();

	} // test

	$sp->setRoom($room_id, $room_name);

	if ( $end_message_id) {
		$sp->setEndMessage($end_message_id);
	}
	elseif ( $end_date) {
		$sp->setDateRange(null, $end_date);
	}
	elseif ( $end_days) {
		$sp->setDaysRange(null, $end_days);
	}

	if ( $sp->error ) {
		#die("startup failed: " . $sp->error);
		raise_error("startup failed: " . $sp->error);
	}

	if ( $show_thread ){

		print "<h3>Thread for '$room_name' </h3>";

		#$sp->getMessages( array( roomId => $room_id) );
		$sp->max_messages = $max_items;
		$sp->getThread();
		$sp->reviewThread();

		if ( $sp->error ) {
			#			die("get failed: " . $sp->error);
			raise_error("get failed: " . $sp->error);
		}

		/*
		 * summary
		*/
		if ( $sp->messageOrderOK() ){
			print "<p>Order OK </p>";
		}
		else {

			print "<p>NOT in Order </p>";
		}
		print "<p>" . $sp->num_messages . " Messages</p>";
		print "<p>Begin: " . $sp->begin_date . " End:" . $sp->end_date . "</p>";
		print "<p>Days Begin: " . $sp->begin_days. " End:" . $sp->end_days . "</p>";
		
		$msg = $sp->getMessageRange();
		print "<p> meesage ID: " . $msg[0] . "  -- " . $msg[1];
		$staff = new StaffDB();

		/*
		 $staff->runSelectQuery(array('email' => 'darin.burleigh@cdw.com'));
		print " n=" . $staff->lastCount();
		$row = $staff->nextRow();
		print_r($row);
		*/

		//
		$staff->prepareQuery( array( 'email'),  array( 'active' => 1) );
		$i = 0;
		foreach( array_keys($sp->members) as $sp_user_id ){

			$i++;
			$email = 'xxx';
			$email = $sp->members[$sp_user_id]['email'];

			$rows = $staff->executeQuery( array(':email' => $email) );
			if ( $staff->lastCount() == 1) {
				$row = $rows[0];
				$name = $row['name'];
				$name .= ' / ' . $row['bincID'];

			}
			else {

				$name = '???';
			}

			$sp->members[$sp_user_id]['name'] =  $row['name'];
			$sp->members[$sp_user_id]['user_id'] =  $row['bincID'];
			$sp->members[$sp_user_id]['label'] = $i;
			if ( $row['wiki_name']){

				$sp->members[$sp_user_id]['wiki_name'] = $row['wiki_name'];
			}
			else {

				$sp->members[$sp_user_id]['wiki_name'] = '';
			}

			#print " type=" . gettype($rows);
			#print "row $row ";
			#print_r($row);
			#print " type=" . gettype($row);
			#print " n=" . $staff->lastCount();
			#print " q=" . $staff->getSQL();
			#			print "<p>" . $name . " ($email)  [$sp_user_id] </p>";
			print "<p>" . $sp->getMemberName($sp_user_id) . " </p>";
			print "<p>Wiki " . $sp->getMemberName($sp_user_id, 'wiki') . " </p>";
		}


		if ( 1 ) {
			$nr = $sp->num_messages * 2;
			$nc = 100;
			dprint("nr=$nr nc=$nc");
			$sp->heading = "Example";
			print "<div class='raw_content'><textarea rows=$nr cols=$nc >";
			#print_r( $sp->rooms);
			print $sp->asText('wiki');
			print "</textarea></div>";
		}


		if ( 0 ) {
			print "<div class='raw_content'>";
			#print_r( $sp->rooms);
			print $sp->asText();
			print "</div>";
		}

		/*
		 *
		print '<form method="post" action="">';
		print "<ul>";
		$n = 0;
		foreach ($sp->messages as $messages ){
			if ($n >= $max_items ){
				break;
		}
			
		}
		print "</ul>";
		print '</form>';
		*/

	} // get

	?>

	<form method="post" action="">

		Before message ID: <input type=text name=end_message_id
			value="<?php echo $end_message_id; ?>" /> Before Date: <input
			type=text name=end_date value="<?php echo $end_date; ?>" /> Messages
		more than : <input type=text name=end_days
			value="<?php echo $end_days; ?>" /> Days ago Match: <input type=text
			name=match_value value="<?php echo $match_value; ?>" /> Max: <input
			type=test name="max_items" value="<?php echo $max_items; ?>" /> <input
			type="submit" name="list_messages" value="Show thread" />
	</form>

</body>
</html>

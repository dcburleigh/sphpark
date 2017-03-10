<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>post message </title>

<link href="../styles/deldev.css" rel="stylesheet" type="text/css" />

<link href="sparkui.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<p>
		<a href="">Start over</a>
	</p>

	<div class=menu>
		<a href="listRooms.php">list rooms</a>
	</div>
	<?php  
	require_once 'config_inc.php';
	#print "form data: ";
	print_r($_POST);


	$room_id = $default_room_id;

	if ( isset($_POST['message'])){

		$room_id = $default_room_id;

		require_once 'SparkClient.php';
		$t = $spark_token;
		if ( $user_access_token) {
			$t = $user_access_token;
		}
		$sp = new SparkClient($t);

		if ( $sp->error ) {
			die("startup failed: " . $sp->error);
		}
		
		print "post to room '$room_id' <br/>". $_POST['message'];

		$sp->postMessage( array('roomId' => $room_id, 'text' => $_POST['message']) );


	}

	?>

	<form method="post" action="">
		<div>
			Message:
			<textarea name=message rows=5 cols=50></textarea>
		</div>
		<div>
			Room ID <input type=text name="room_id"
				value="<?php echo $room_id; ?>" />
		</div>
		<input type="submit" name="post_message" value="post message" />
	</form>

</body>
</html>

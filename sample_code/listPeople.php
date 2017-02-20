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
	if (isset($_POST['verbose'])){
		$verbose = $_POST['verbose'];
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

			$ne = count( $person->{'emails'} );
			$nr = count( $person->{'roless'} );

			$n++;
			print "<li>"
				. "$n) ". $name

				. "  $ne emails "
				. " Email: " . implode(', ', $person->{'emails'})
					
				. " Status: " . $person->{'status'}
			. ' (' . $person->{'lastActivity'} . ') '

					. "</li>"
					;
		}
		print "</ul>";
		print '</form>';

	} // get

	?>


	<form method="post" action="">

		Match name: <input type=text name=match_value
			value="<?php echo $match_value; ?>" /> Email: <input type=text
			name=match_email value="<?php echo $match_email; ?>" /> Max: <input
			type=test name="max_items" value="<?php echo $max_items; ?>" />
		Verbose: <input type=test name="verbose"
			value="<?php echo $verbose; ?>" /> <input type="submit"
			name="list_people" value="List people" />
	</form>

</body>
</html>

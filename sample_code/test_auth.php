<?php session_start(); ?>
<html>
<head>
<title>Test Auth</title>

<link href="sparkui.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<p>
		<a href="">Start over</a>
	</p>

	<h2>Test auth</h2>

	<?php

	$app_name = 'MyAPP';
	
	require_once 'config_inc.php';

	/*
	 * set your user_id here
	*
	*/
	if ( ! isset($user_id)) {
		// FAIL

		print "Invalid User";
		exit(-1);
	}

	// debugging ....
	if (!isset($_SESSION['count'])) {
		$_SESSION['count'] = 0;
	} else {
		$_SESSION['count']++;
	}
	print "<p>c=" . $_SESSION['count'] . "</p>\n";

	$auth = 0;
	$state = '1. request auth';
	
	if ( isset($_SESSION['auth'])  ) {
		$auth = $_SESSION['auth'];
	}
	require_once 'SparkApp.php';
	$app = new SparkAppAuth();

	// init from config_inc
	$app->user_id = $user_id;
	$app->redirect_uri = $redirect_url;


	if (isset($_POST['request_auth'])) {
		// handle request
		$app->getAccesCode();

		if ( $app->error ) {
			print "<p>ERROR: " . $app->error;
			die(-3);
		}

	} // process req
	elseif ( isset($_GET['code']) ) {
		// handle request
		$app->handleAuthFormResponse($_GET);

		if ( $app->error ) {
			print "<p>ERROR: " . $app->error;
			die(-3);
		}

	} // get code
	elseif ( $app->hasUserToken()) {
		// TODO: verify token is not expired

		print "<p>OK!</p>";
	} // do stuff
	elseif ( $app->hasAuthCode() ) {
		print "<p>get token: ";
		$app->requestAccessToken();

		if ( $app->error ) {
			print "<p>ERROR: " . $app->error;
			die(-3);
		}

		print "<p>Success!</p>";

	} // has auth code, get token



	// lookup token

	// if no token
	print "<h4>Step 1. Request permission</h4>";

	$app->requestAuthForm();
	if ( $app->error ) {
		print "<p>ERROR: " . $app->error . "</p>";
	}
	print "<h4>Step 2. Get access token </h4>";

	print "<h4>Step 3. Refresh access token </h4>";


	?>
	<div class='footer'>DONE</div>
</body>
</html>

<?php

require_once 'config_inc.php';
require_once 'HookHandler.php';
require_once 'AuthHandler.php';
require_once 'SparkApp.php';

$ah = new AuthHandler(  array (
		'logfile' => './auth.log'
) );
$ah->log ( "started " );

try {

	processHookRequest ();
} catch ( Exception $e ) {
	// print "got error=" . $e->getMessage() ;
	$ah->log ( "process failed: err=" . $e->getMessage () );
}
$ah->printResponse ();

function processHookRequest() {
	global $ah, $client;
  global $redirect_url, $user_id;

	$ah->getRequest ();

	if ($ah->error) {
		$ah->log ( "ERROR: " . $ah->error );
		return;
	}

	// $ah->log ( "name = " . $ah->hook_name );

	try {
		/*
		 * TODO
		 * get app-specific parameters from request object, via handler
		 *
		 * $client->getAppData( $ah);
		 *
		 */

     /*
      * set from config:
      *  client_id
      *  client_secret
      *  redirect_uri  - this URI, must match request
      *
      * set from Spark auth:
      *  code
      */

      $code = $ah->data['code'];
      $ah->log ( "got request data: code=$code " );

      $ah->log("url=$redirect_url  user=$user_id");
      
      $app = new SparkAppAuth();
      $app->redirect_uri = $redirect_url;
      $app->user_id = $user_id;
      $app->setAccessCode( $code);
      $ah->log("got app user=" . $app->user_id);

      if ( ! $app->hasAuthCode() ) {
        $ah->log("no auth code err="  . $app->error);
        return;
      }

      $app->requestAccessToken();
      if ( $app->error ) {
        $ah->log("token request failed: " . $app->error);
        return;
      }

      $ah->log("got token");
      
      // save token 


	} catch ( Exception $e ) {
		$ah->responseText ();
		$ah->log ( "get failed: " . $e->getMessage () );
		return;
	}

	/*
	 * TODO
	 * client specific errors
	 *
	 * if ( $client->error ){
	 * $ah->log("APP failed: error=" . $client->error);
	 * $ah->log( print_r($msg, true) );
	 * return;
	 * }
	 *
	 */

	/*
	 * TODO
	 *
	 * Switch( app-specific arguments)
	 *
	 *  do stuff
	 */

	$ah->addResponseElement ( 'contents', 'ok' );
}



?>

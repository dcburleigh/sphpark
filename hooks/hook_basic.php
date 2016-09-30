<?php
/*
 * endpoint for handling webhook, typically from Spark
 *
 */
require_once 'HookHandler.php';
require_once 'config_inc.php';

$wh = new HookHandler ( array (
		'logfile' => '../logs/hook_basic.log' 
) );
$wh->log ( "started " );

/*
 *
 * require_once 'Client.php';
 * $client = new Client( ); # TODO
 * $wh->log("got client");
 */

try {
	
	processHookRequest ();
} catch ( Exception $e ) {
	// print "got error=" . $e->getMessage() ;
	$wh->log ( "process failed: err=" . $e->getMessage () );
}
$wh->printResponse ();

function processHookRequest() {
	global $wh, $client;
	
	$wh->getRequest ();
	
	if ($wh->error) {
		$wh->log ( "ERROR: " . $wh->error );
		return;
	}
	
	// $wh->log ( "name = " . $wh->hook_name );
	
	try {
		/*
		 * TODO
		 * get app-specific parameters from request object, via handler
		 *
		 * $client->getAppData( $wh);
		 *
		 */
		
		$wh->log ( "got request data " );
	} catch ( Exception $e ) {
		$wh->responseText ();
		$wh->log ( "get failed: " . $e->getMessage () );
		return;
	}
	
	/*
	 * TODO
	 * client specific errors
	 *
	 * if ( $client->error ){
	 * $wh->log("APP failed: error=" . $client->error);
	 * $wh->log( print_r($msg, true) );
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
	
	if ( $wh->data['name'] ) {
		$wh->addResponseElement("answer", "your name is " . $wh->data['name']  );
	}
	else {
		$wh->addResponseElement("answer", "no name " );
		
	}
	$wh->addResponseElement ( 'contents', 'ok' );
}
?>
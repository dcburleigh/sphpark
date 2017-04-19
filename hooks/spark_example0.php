<?php
/*
 * endpoint for handling webhook, typically from Spark
*
*/

// print "begins";
ini_set('include_path',  ini_get('include_path') . PATH_SEPARATOR . './etc');

require_once 'config_inc.php';
require_once 'SparkHookHandler.php';
require_once 'SparkClient.php';

$wh = new SparkHookHandler ( array (
'logfile' => '../logs/hook.log'
) );
$wh->log ( "started " );
// token defined in config_inc
$sp = new SparkClient ( $spark_access_token);
$wh->log ( "got client" );

try {

	processHookRequest ();
} catch ( Exception $e ) {
	// print "got error=" . $e->getMessage() ;
	$wh->log ( "process failed: err=" . $e->getMessage () );
}
$wh->printResponse ();

function processHookRequest() {
	global $wh, $sp;

	$wh->getRequest ();
	if ($wh->error) {
		$wh->log ( "ERROR: " . $wh->error );
		return;
	}

	$wh->log ( "hook name = " . $wh->hook_name );

	if (! $wh->message_id) {
		$wh->error = "no message ID";
		$wh->log ( "no message ID in " . print_r($this->data, true) );
		return;
	}


	/*
	 * get message, extract contents
	*/
	try {

		$wh->log ( "get message: " . $wh->message_id );
		$msg = $sp->getMessage ( $wh->message_id);
	} catch ( Exception $e ) {
		$wh->responseText ();
		$wh->log ( "get failed: " . $e->getMessage () );
		return;
	}

	if ($sp->error) {
		$wh->log ( "spark failed: error=" . $sp->error );
		// $wh->log( $sp->response );
		$wh->log ( print_r ( $msg, true ) );
		return;
	}

	$room_id = $wh->data->data->roomId;
	$text = $msg->{'text'};
	$wh->log( "room=$room_id  text=\n$text");

	/*
	 * handle input:
	 * 
	 * 
	*/

}
?>
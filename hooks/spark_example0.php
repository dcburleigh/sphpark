<?php
/*
 * endpoint for handling webhook, typically from Spark
 *
 */

// print "begins";
require_once 'SparkHookHandler.php';
require_once 'config_inc.php';
require_once 'SparkClient.php';

$wh = new SparkHookHandler ( array (
		'logfile' => '../logs/hook.log' 
) );
$wh->log ( "started " );
$sp = new SparkClient ();
$wh->log ( "got client" );

try {
	
	processHookRequest ();
} catch ( Exception $e ) {
	// print "got error=" . $e->getMessage() ;
	$wh->log ( "process failed: err=" . $e->getMessage () );
}
$wh->printResponse ();

function processHookRequest() {
	
	
	
}
?>
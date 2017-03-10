<?php 
/*
 * endpoint for handling webhook, typically from Spark
 * 
 */

require_once 'HookHandler.php';
require_once 'config_inc.php';

#$wh = new HookHandler( array('logfile' => '/var/log/deldev/hook_box.log'));
$wh = new HookHandler( array('logfile' => '../logs/hook_box.log'));
$wh->log("started ");
$wh->log("got client");

require_once 'BoxClient.php';

$client = new BoxClient( $box_token);

try {

	processHookRequest();

}
catch(Exception $e ) {
	#print "got error=" . $e->getMessage() ;
	$wh->log("process failed: err=" . $e->getMessage() );
}
		$wh->printResponse();

function processHookRequest() {
	global $wh, $client;

	$wh->getRequest();

	if ( $wh->error) {
		$wh->log("ERROR: " . $wh->error);
		return;
	}

	$wh->log( "name = " . $wh->hook_name );


	try {
		/*
		 * TODO
		*   get app-specific parameters from request object, via handler
		*
		* $client->getAppData( $wh);
		*
		*/
		
		// Dummy Date 
		$get_folder_id = '0';
		
		$get_folder_info_id = '0';
		#$get_folder_info_id = '8439918653';
		
	$wh->log( "got request data ");
		
	}
	catch(Exception $e) {
		$wh->responseText();
		$wh->log("get failed: " . $e->getMessage() );
		return;
	}

	/*
	 * TODO
	 *   client specific errors
	 *   
	if ( $client->error  ){
		$wh->log("APP failed: error=" . $client->error);
		$wh->log( print_r($msg, true) );
		return;
	}

	 */
	
	/*
	 * TODO
	 * 
	 * Switch( app-specific arguments)
	 * 
	 */
	if ( isset($get_folder_info_id) ) {
		$client->getFolderInfo( $get_folder_info_id );
		$wh->log("got folder: \n" . $client->response);
		
	}
	else {
		$wh->log("no valid action");
	}
	
	if ($client->error) {
		$wh->log("token=" . $client->getToken() );
		$wh->log("err=" . $client->error);
		return;
	}
	$wh->addResponseElement( 'contents', 'ok');
	
}
?>
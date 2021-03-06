<?php
/*
 * endpoint for handling webhook, typically from Spark
*
*/
ini_set('include_path',  ini_get('include_path') . PATH_SEPARATOR . './etc');
require_once 'config_inc.php';
require_once 'SparkHookHandler.php';
require_once 'SparkClient.php';

if ( ! isset( $log_dir)){
	// default
	$log_dir = './log';
}

$wh = new SparkHookHandler ( array (
'logfile' =>  $log_dir . '/hook.log', 'secret' => $webhook_secret
) );
if ( isset($webhook_secret)){
    $wh->validate_signature = true; // optional 
}
// $wh->validate_signature = true; // optional 

// $wh->log ( "started " );
$sp = new SparkClient ( $spark_access_token);

try {

	processHookRequest ();
} catch ( Exception $e ) {
	// print "got error=" . $e->getMessage() ;
	$wh->log ( "process failed: err=" . $e->getMessage () );
}
$wh->printResponse (); // reply

function processHookRequest() {
	global $wh, $sp;

	$wh->getRequest ();
	// print "got here ";

	if ($wh->error) {
		// print "<p>ERROR: " . $wh->error;
		$wh->log ( "ERROR: " . $wh->error );
		// $wh->printResponse();
		return;
	}

	$wh->log ( "name = " . $wh->hook_name );
	// $wh->log("header: " . $wh->header_info);
	if ( ! $wh->checkSignature()){
	    $wh->log("invalid signature");
	    return;
	}

	if (! $wh->message_id) {
		$wh->error = "no message ID";
		$wh->log ( "no message ID in " . print_r($wh->data, true) );
		return;
	}

	// messages

	/*
	 *
	$id = $wh->data->{'data'}->{'id'};
	$id = $wh->data->data->id;
	// print "get message: $id ";
	$wh->log ( "get message: $id  =" . $wh->message_id );
	return;
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
	$wh->log( implode("\t", array($wh->hook_name,  $msg->{'personEmail'}, $msg->{'roomId'} ) )   );
	#$wh->log( $text );

	//
	// switch
	//
	if ($text == '/whoami') {
		postWhoami ( $msg );
		$wh->addResponseElement("handlerAction", "posted response");
	} elseif (preg_match ( '/^\/repeat(.+)/', $text, $matches )) {
		postBotResponse ( $room_id, "You said: " . $msg->{'text'} );
		$wh->addResponseElement("handlerAction", "posted response");
	} elseif (preg_match ( '/^\/members/', $text, $matches )) {
		postMemberList ( $msg );
		$wh->addResponseElement("handlerAction", "posted response");
	} elseif (preg_match ( '/^\/(\?|help)/', $text, $matches )) {
		postHelpCommands ( $msg );
		$wh->addResponseElement("handlerAction", "posted response");
	} else {
		$wh->log ( "no response defined, text=$text" );
		$wh->addResponseElement("handlerAction", "none, text=$text");
	}
	$wh->log("----------------------\n");
}
function postHelpCommands($msg) {
	$commands = '';
	$commands .= "Commands: \n";
	$commands .= '/whoami ' . "\n";
	$commands .= "/members\n";
	$commands .= "/repeat SOME TEXT \n";

	postBotResponse ( $msg->{'roomId'}, $commands );
}
function postWhoami($msg) {
	global $wh, $sp;

	$person_id = $wh->data->{'data'}->{'personId'};
	$person_id = $msg->{'personId'};
	$wh->log ( "who: $person_id" );
	try {

		$p = $sp->getPerson ( $person_id );
		$wh->log ( "got person=" . print_r ( $p, true ) );
		if ($sp->error) {
		    $wh->error = $sp->errors;
			$wh->log ( "ERROR - get person failed: " . $sp->error );
			return;
		}
	} catch ( Exception $e ) {
		// $wh->log("err=" . $e->getMessage());
		$wh->log ( "err=" );
	}

	$wh->log ( "done" );

	postBotResponse ( $msg->{'roomId'}, "Your name: " . $p->{'displayName'} );
}
function postMemberList($msg) {
	global $wh, $sp;

	$sp->getMemberships ( array (
	'roomId' => $msg->{'roomId'}
	) );
	$list = '';
	if ($sp->num_memberships == 0) {
		$list = "No members!!! ";
		return;
	}
	$n = $sp->num_memberships;
	$list = "$n Members in this room\n";
	;
	foreach ( $sp->memberships as $m ) {
		$p = $sp->getPerson ( $m->{'personId'} );
		$list .= "* " . $p->{'displayName'} . "\n";
	}

	$wh->log ( "list=$list" );
	postBotResponse ( $msg->{'roomId'}, $list );
}
function postBotResponse($room_id, $message) {
	global $wh, $sp;

	if (preg_match ( '/^Bot/i', $message )) {
		$wh->log ( "don't reply to myself" );
		return;
	}

	if (preg_match ( '/--Bot$/i', $message )) {
		$wh->log ( "don't reply to myself" );
		return;
	}
	
	$t = time();
	$message .= "\nTime: $t \n";
	// prefix? suffix? so that we don't get an infinite loop
	// $message = "Bot: ";
	$message .= "\n--Bot";
	
	$msg = $sp->postMessage ( array (
	'roomId' => $room_id,
	'text' => $message
	) );
	if ($sp->error) {
	    $wh->error = $sp->error;
		$wh->log ( "post failed: " . $sp->error );
		return;
	}
	$wh->log ( "posted got=" . $sp->response );
	$o = $sp->response_object;
	$wh->log ( "message: " . $o->{'id'} );
	#$wh->log ( "got m=" . print_r ( $msg, true ) );
}

?>
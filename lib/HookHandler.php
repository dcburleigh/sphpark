<?php

/*
 * PHP handler for Cisco Spark Webhooks
*
*  Usage:
*    require_once 'HookHandler.php';
*
*    $hook_handler = new HookHandler( array('logfile' => '/path/to/file') );
*
*    $hook_handler->getRequest();
*
*    print "item = " . $hook_handler->data->{'item'};


*
*
*
*/
class HookHandler {

	private $body;   # raw text
	public $data;    # JSON Request object

	public $secret;   // token expected for a valid application, configured by app,


	// request parameters expected by DelDev apps
	public $hook_name;
	private $request_secret;  // the token sent with the request
	private $action;

	private $logfile = '/tmp/webhook.log';
	private $logFH;


	private $response = array();  # Response object
	private $response_text = '';  # JSON-encoded response object

	public $error;

	public function __construct( $args = array()  ){

		if ( isset($args['logfile'])){
			$this->logfile = $args['logfile'];
		}
		$this->openLog();

	}


	private function openLog() {
		$d = date('c');
		$this->logFH = fopen( $this->logfile, 'a');
		$this->log("begin");
		#fwrite($this->logFH, "$d got request\n");

	}
	public function log( $text ) {
		$d = date('c');
		fwrite( $this->logFH,  "$d " . $text . "\n");
	}

	private function clearRequest() {
		$this->body = '';
		$this->data = array();
		$this->resource_type = '';

	}
	public function getRequest() {

		$this->clearRequest();

		if (isset($_SERVER['QUERY_STRING'])) {
			#$this->log("query: " . $_SERVER['QUERY_STRING']);
			#parse_str($_SERVER['QUERY_STRING'], $parameters);
			#$this->log("got params=" . print_r($parameters, true));

			$this->log("get = " . print_r($_GET, true));
		}

		$body = file_get_contents("php://input");

		if ( $body == '' ) {

			$this->log("GET contents:");
			foreach ( $_GET as $name => $value ) {
				$this->data[$name] = $value;
			}
			$this->addResponseElement("num_get_params", count($_GET) );

			// $this->error = "no body";
			// fwrite($this->logFH, "body=$body \n");
			$this->log("no POST/PUT body;  n=" . count($_GET) . " GET params");
			return;

		}
		$this->log("POST/PUT contents:");

		fwrite($this->logFH, "body=$body \n");

		$this->data = json_decode($body);
		$this->log( print_r($this->data, true) );

		//?
		$this->hook_name = $this->data->{'name'};
		$this->resource_type = $this->data->{'resource'};

	}

	public function addResponseElement( $name, $value) {
		$this->response[ $name ] = $value;
	}

	public function printResponse() {
		$this->addResponseElement( 'error', $this->error);
		$this->response_text = json_encode( $this->response);
		print $this->response_text;
		//return $this->response_text;
	}
}


?>

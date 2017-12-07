<?php

/*
 * PHP handler for Cisco Spark Webhooks
*
*  Usage:
*    require_once 'HookHandler.php';
*
*    $hook_handler = new HookHandler( array('logfile' => '/path/to/file') );
*
*    // parse the request object,
*    // populate the 'data' attribute
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
	public $header_info;
	public $headers = array();

	public $secret;   // token expected for a valid application, configured by app,


	// request parameters expected by DelDev apps
	//public $hook_name;
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

		$this->logFH = fopen( $this->logfile, 'a');
		$this->log(__METHOD__ . " begin");
	}
	public function log( $text ) {
		$d = date('c');
		fwrite( $this->logFH,  "$d " . $text . "\n");
	}

	private function clearRequest() {
		$this->body = '';
		$this->data = array();
		$this->headers = array();
		$this->resource_type = '';

	}
	public function getRequest() {

		$this->clearRequest();

		if (isset($_SERVER['QUERY_STRING'])) {
			#$this->log("query: " . $_SERVER['QUERY_STRING']);
			#parse_str($_SERVER['QUERY_STRING'], $parameters);
			#$this->log("got params=" . print_r($parameters, true));

			#$this->log("get = " . print_r($_GET, true));
		}
		

		$body = file_get_contents("php://input");
		#$this->getRequestHeaders();
		# $headers = $http_response_header;
		$headers = getallheaders();
		if ( isset($headers)){
		    $this->header_info = print_r($headers, true);
		    $this->headers = $headers;
		}
		else {
		    $this->header_info = 'NULL';
		}
		    
		    
		    
	
		if ( $body == '' ) {

			$this->log("GET contents:");
			foreach ( $_GET as $name => $value ) {
				$this->data[$name] = $value;
			}
			$this->addResponseElement("num_get_params", count($_GET) );

			// $this->error = "no body";
			// fwrite($this->logFH, "body=$body \n");
			#$this->log("no POST/PUT body;  n=" . count($_GET) . " GET params");
			return;

		}
		// $this->log("POST/PUT contents:");

		// fwrite($this->logFH, "body=$body \n"); // raw contents (verbose logging)

		$this->data = json_decode($body);
		//$this->log( print_r($this->data, true) );  // parsed contents

		//?
		$this->hook_name = $this->data->{'name'};
		$this->resource_type = $this->data->{'resource'};

	}
	public function getRequestHeaders(){
	    /*
	     * HTTP_HOST,
HTTP_USER_AGENT,
HTTP_ACCEPT,
HTTP_ACCEPT_LANGUAGE,
HTTP_ACCEPT_ENCODING,
HTTP_REFERER,
HTTP_CONNECTION,
HTTP_UPGRADE_INSECURE_REQUESTS,
HTTP_CACHE_CONTROL,
PATH,
SERVER_SIGNATURE,
SERVER_SOFTWARE,
SERVER_NAME,
SERVER_ADDR,
SERVER_PORT,
REMOTE_ADDR,
DOCUMENT_ROOT,
REQUEST_SCHEME,
	     * CONTEXT_PREFIX,CONTEXT_DOCUMENT_ROOT,
	     * SERVER_ADMIN,
SCRIPT_FILENAME,
REMOTE_PORT,
GATEWAY_INTERFACE,
SERVER_PROTOCOL,
REQUEST_METHOD,
QUERY_STRING,
REQUEST_URI,
SCRIPT_NAME,
PHP_SELF,
REQUEST_TIME_FLOAT,
REQUEST_TIME

	     */
	    #$this->header_info = implode(',' ,  array_keys($_SERVER));
	    $this_header_info = print_r($http_response_header, true);
	    
	    // $this->h
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

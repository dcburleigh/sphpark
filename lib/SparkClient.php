<?php 

/*
 * PHP interface to Cico Spark API
 * 
 * Usage:
 *   edit config_inc.php
 *   
 *   $sp = new SparkClient();
 *   
 *   $sp->getRooms();
 *   
 *   
 */
class SparkClient {
	
	// setup 
	private $token;
	private $token_valid = 0;
			
	private $host = 'https://api.ciscospark.com/v1/';
	
	private $content_type = 'application/json';
	
	private $client;   // the curl object

	public $response;  //(JSON) string contents of API request 
	public $response_object;  //JSON string decoded into Object
	
	private $response_code;   //  HTTP response code from last Spark call
	public $error;
	
	public $url;
	
	// populated by GET requests
	public $rooms;
	public $num_rooms;
	public $memberships;
	public $num_memberships;
	
	public function __construct() {
		global $spark_token;
		
		$this->token = $spark_token;
		$this->initClient();
		
	}
	public function initClient() {


		$this->client = curl_init( );
		
		# ???
		#curl_setopt($handle, CURLOPT_FILE, fopen('php://stdout','w'));   // 'php://output' didn't work for me
		#curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);  // using CURLOPT_FILE sets this to false automatically
		
		curl_setopt($this->client,  CURLOPT_VERBOSE, true);
		curl_setopt($this->client , CURLOPT_RETURNTRANSFER, true); //do not output directly, use variable

// ???		
		#curl_setopt($this->client, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		#curl_setopt($this->client , CURLOPT_HTTPHEADER, array('Accept: application/json',  "Authorization: Bearer ".$this->token));
		
		// POST requires both content-type and Accept headers
		curl_setopt($this->client , CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json',  "Authorization: Bearer ".$this->token));
		
	}
	public function clearRequest() {
		$self->response = '';
		$self->response_code = '';
		$self->error = '';
	}
	/*
	public function getToken() {
		return $this->token;
	}
	*/
	public function setToken( $token) {
		$this->token = $token;
	}
	
	public function getResponse() {
		return $this->response;
	}
	
	public function getUrl( $path, $args,  $allowed = array() ) {

		
		$url = '';
		if ( count($args) ) {
			
		foreach ( $allowed as $name ) {
			if ( isset($args[$name])){
				if ( $url ) {
					$url .= '&';
				}
				else {
					$url .= '?';
				}
				
				$url .= $name  . '=' . curl_escape($this->client,  $args[$name]);
			}
			
		}
		}

		$url = $this->host .  $path . '/' . $url;
		return $url;
	}
	

	public function setUrl( $path, $args,  $allowed = array() ) {
	
		$url = '';
		if ( count($args) ) {
				
			foreach ( $allowed as $name ) {
				if ( isset($args[$name])){
					if ( $url ) {
						$url .= '&';
					}
					else {
						$url .= '?';
					}
	
					$url .= $name  . '=' . curl_escape($this->client,  $args[$name]);
				}
					
			}
		}
	
#		$url = $this->host .  $path . '/' . $url;
		$url = $this->host .  $path . $url;
		curl_setopt($this->client, CURLOPT_URL, $url);
		return $url;
	}
	
	public function get($path, $args,  $allowed = array() ) {
	
		// $this->initClient();
		$url = $this->setUrl($path, $args,  $allowed );
		
		$contents = curl_exec($this->client);
	
		if ( curl_errno($this->client )) {
			$this->error =  "failed: " . curl_error($this->client );
			return;
		}
	
		#		$info = curl_getinfo( $this->client);
		$this->response_code = curl_getinfo( $this->client, CURLINFO_HTTP_CODE);
		if ( $this->response_code != '200') {
			$this->error = "Get failed, url=$url contents=$contents, code=" . $this->response_code;
			return;
		}
	
		$this->response = $contents;
		$this->response_object = json_decode($contents);
	}
	
	public function getMemberships( $args = array() ) {

		// $this->response = '';
		$this->memberships = array();
		$this->num_memberships = 0;
		
		$this->get('memberships/', $args, array('personEmail', 'roomId', 'personId', 'max') );

		$this->memberships = $this->response_object->{'items'};
		$this->num_memberships = count($this->memberships);
		return;
		
	}
	
	public function getPerson( $person_id){
		
		$this->get('people/' . $person_id,  array() );
		if ($this->error) {
			return;
		}
		return $this->response_object;
		
		
	}

	public function getTeam( $team_id){
	
		$this->get('teams/' . $team_id,  array() );
		if ($this->error) {
			return;
		}
		return $this->response_object;
	}

	public function getTeams( $args = array() ){

		$this->teams = array();
		$this->num_teams = 0;
		
		$this->get( 'teams/',  $args,  array( 'max'));
		if ($this->error) {
			return;
		}
		$this->items = $this->response_object->{'items'};
		$this->num_teams = count($this->teams);
		return $this->response_object;
	}
	
	public function getRoom( $room_id ) {

		$this->get('rooms/' . $room_id,  array() );
		if ($this->error) {
			return;
		}
		return $this->response_object;
	}
	public function getRooms( $args = array() ) {

		$this->rooms = array();
		$this->num_rooms = 0;
		
		$this->get( 'rooms/',  $args,  array('showSipAddress', 'max'));
		if ($this->error) {
			return;
		}
		$this->rooms = $this->response_object->{'items'};
		$this->num_rooms = count($this->rooms);
		return $this->response_object;
		
	}
	public function getMessage( $message_id ) {

		$this->get('messages/' . $message_id,  array() );
		if ($this->error) {
			return;
		}
		return $this->response_object;
		
	}

	public function getMessages( $args = array() ) {
	
		$this->messages = array();
		$this->num_messages = 0;
	
		$this->get('messages/', $args, array('before', 'roomId', 'beforeMessage', 'max') );
		if ( $this->error){
			return;
		}
	
		$this->messages = $this->response_object->{'items'};
		$this->num_messages = count($this->messages);
		return;
	
	}
	private function post( $path, $args) {

#		$this->initClient();
		$this->clearRequest();
		
		$url = $this->host . $path .'/';
		curl_setopt($this->client, CURLOPT_URL, $url);
		
		curl_setopt($this->client, CURLOPT_POST, true);
		$data = json_encode($args);
		$contents = curl_setopt($this->client, CURLOPT_POSTFIELDS,  $data);
	
		$contents = curl_exec($this->client);
		
		if ( curl_errno($this->client )) {
			$this->error =  "post=$data url=$url err= " . curl_error($this->client );
			return;
		}
		
		#		$info = curl_getinfo( $this->client);
		$this->response_code = curl_getinfo( $this->client, CURLINFO_HTTP_CODE);
		if ( $this->response_code != '200') {
			$this->error = "post failed, contents=$contents, data=$data,  code=" . $this->response_code;
					return;
		}
		
		$this->response = $contents;
		
		return;
		
	}
	public function postMessage( $args ) {

		# my @valid = 'roomId', 'text', 'files', 'toPersonId', 'toPersonEmail'

		return $this->post( 'messages', $args);
				
	}
	public function postRoom( $args ) {

		return $this->post( 'rooms', $args);
	}

	public function postMembership( $args ) {
	
		return $this->post( 'memberships', $args);
	}
}

?>
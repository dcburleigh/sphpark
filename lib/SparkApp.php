<?php

/*
 * Interface to Cisco Spark App authorization functions
 *
 */

class SparkAppAuth {

	public $user_id;
	private $user_token;

	// required for initial request
	private $state;
	private $client_id;

	private $client_secret;

	// app-specific
	private $scopes = array('spark:messages_read', 'spark:messages_write');
	public $redirect_uri;

	//request access code
	public $response_code;
	public $access_code_url = 'https://api.ciscospark.com/v1/authorize';
	private $acess_code;

	// request token
	public $access_token_url = 'https://api.ciscospark.com/v1/access_token';

	// token storage
#	public $user_token_file = '/usr/local/etc/deldev/spark_user_tokens.tsv';
	public $user_token_file = 'spark_user_tokens.tsv';
	public $user_tokens = array();

	public $error;
	private $logFH;
	protected $log_file;

	public function __construct(){
		global $user_id, $app_state, $spark_client_id, $spark_client_secret;

		if ( isset($user_id)) {
			$this->user_id = $user_id;
		}
		if ( isset($app_state) ) {

			$this->state = $app_state;
		}
		if ( isset($spark_client_id)) {
			$this->client_id = $spark_client_id;
		}


		if ( isset($spark_client_secret)) {
			$this->client_secret = $spark_client_secret;
		}

		$this->initUserTokens();
		$this->lookupUserToken();

		// kludge
		###$this->access_code = '8c0d0e3d419bdd8f7169ce6eaac3cfbcc2a56cdef819d1c5e1c73758cd984d2f';
		#$this->access_code = 'MDgyM2Y2NDEtNTE4Yy00NzAyLTk5YTUtMmVkNDlhZjRiY2E0YjgzZTAwNGUtMmQz';
		#$this->access_code = 'MzJiMzYwMDYtYzU5Yi00NDI2LWJhMjYtZTgzYTYwZjU5OWE2YTllNzU5ZmEtMTYx';


	}

	public function getToken() {
		return $this->user_token;

	}
	public function generateState() {
		// TODO generate a temporary state
		global $app_state;

		// $this->state = $app_state

		// save temporary value

		// in file?
		$file = '/tmp/spark_app_state_' . $this->user_id . '.txt';

		// in session?
		# $_SESSION['app_state'] = $this->state;


	}
	public function readState() {
		// read temp value
		//   from file?
		$file = '/tmp/spark_app_state_' . $this->user_id . '.txt';
		# open/read
		# %this->state = '';
		// from session
		# $this->state = $_SESSION['app_state'];

	}
	public function requestAuthForm() {

		if ( ! isset($this->client_id)) {
			$this->error = "No client ID";
			return;
		}

		if ( ! isset($this->redirect_uri)){
			$this->error = "no URI";
			return;
		}

		if ( ! isset($this->scopes) || count($this->scopes) == 0) {
			$this->error = "no scopes";
			return;
		}

		// TDDO
		$this->generateState();
		if ( ! isset($this->state)){
			$this->error = 'no state';
			return;
		}

		if ( ! isset($this->access_code_url)) {
			$this->error = "no auth url";
			return;
		}

		print "<h4>Request Authorization</h4>";
		print "<form method=get action=" . $this->access_code_url . '> ';
		print '<input type="hidden" name="response_type" value="code" />';
		print '<input type="hidden" name="client_id" value="' . $this->client_id . '" />';
		print '<input type="hidden" name="redirect_uri" value="' . $this->redirect_uri . '" />';
		print '<input type="hidden" name="scope" value="' . join(" ", $this->scopes) . '" />';
		print '<input type="hidden" name="state" value="' . $this->state .'" />';
		print '<input type="submit" name="submit" value="Submit request" />';
		print '</form>';


	}
	//DEP
	public function requestAuthForm1() {

		print "<form method=post action='' ><input type='submit' name='request_auth' value='Request Authorization' /></form>";
	}

	private function argString( $args = array(), $ch = null ) {
		$arg_string = '';
		foreach ( $args as $name => $value) {

			if ( $arg_string ) {
				$arg_string .= '&';
			}


			if ( $ch ) {
				$arg_string .= $name  . '=' . curl_escape($ch, $value);
			}
			else {
				$arg_string .= $name  . '=' .  $value;
			}

		}
			return $arg_string;
	}
	//DEP
	private function get( $url,  $args = array() ) {
		$ch = curl_init( );
		curl_setopt($ch,  CURLOPT_VERBOSE, true);
#		curl_setopt($ch , CURLOPT_RETURNTRANSFER, true); //do not output directly, use variable
		curl_setopt($ch , CURLOPT_RETURNTRANSFER, false); //do not output directly, use variable

		// POST requires both content-type and Accept headers
		// curl_setopt($ch , CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json',  "Authorization: Bearer ".$this->token));

		$arg_string = '';
		foreach ( $args as $name => $value) {

				if ( $arg_string ) {
					$arg_string .= '&';
				}
				else {
					$arg_string.= '?';
				}

				$arg_string .= $name  . '=' . curl_escape($ch, $value);


		}
		$url = $url . $arg_string;
		curl_setopt($ch, CURLOPT_URL, $url);

		print "url=$url";
		#return;

		$contents = curl_exec($ch);
		if ( curl_errno($ch )) {
			$this->error =  "failed: " . curl_error($ch );
			return;
		}
		$this->response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
		print "got: $contents  code=" . $this->response_code;

	}

	// DEP
	public function getAccesCode() {
		$args = array();
		$args['response_type'] = 'code';
		if ( ! isset($this->client_id)) {
			$this->error = "No client ID";
			return;
		}
		$args['client_id'] = $this->client_id;

		if ( ! isset($this->redirect_uri)){
			$this->error = "no URI";
			return;
		}
		$args['redirect_uri'] = $this->redirect_uri;

		if ( ! isset($this->scopes) || count($this->scopes) == 0) {
			$this->error = "no scopes";
			return;
		}
		$args['scope'] = join(" ",  $this->scopes);

		if ( ! isset($this->state)){
			$this->error = 'no state';
			return;
		}
		$args['state'] = $this->state;

		if ( ! isset($this->access_code_url)) {
			$this->error = "no auth url";
			return;
		}
			$this->get( $this->access_code_url,  $args);


	}

	public function handleAuthFormResponse( $args = array() ) {

		// TODO: read temp value for this session
		$this->readState();

		if ( ! isset($args['state'])) {
			$this->error = "no state in response";
			return;
		}
		if ( $args['state'] != $this->state ) {
			$this->error = "invalid state";
			return;
		}

		if ( ! isset($args['code'])){
			$this->error = "no code ";
			return;
		}

		$this->acess_code = $args['code'];


		//store acess code

	}

	private function post($url, $args){

		$ch = curl_init( );
		curl_setopt($ch,  CURLOPT_VERBOSE, true);
		curl_setopt($ch , CURLOPT_RETURNTRANSFER, true); //do not output directly, use variable
		#curl_setopt($ch , CURLOPT_RETURNTRANSFER, false); //do not output directly, use variable

		// POST requires both content-type and Accept headers
		// curl_setopt($ch , CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json',  "Authorization: Bearer ".$this->token));
		// curl_setopt($ch , CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));


		/*
		$contents = '
{
 "access_token":"ZDI3MGEyYzQtNmFlNS00NDNhLWFlNzAtZGVjNjE0MGU1OGZmZWNmZDEwN2ItYTU3",
 "expires_in":1209600,
 "refresh_token":"MDEyMzQ1Njc4OTAxMjM0NTY3ODkwMTIzNDU2Nzg5MDEyMzQ1Njc4OTEyMzQ1Njc4",
 "refresh_token_expires_in":7776000
}';
		print "got: $contents";
		return $contents;

		return;
		*/


		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
#		$contents = curl_setopt($ch, CURLOPT_POSTFIELDS,  $args);
		$arg_string = $this->argString($args, $ch);
		$contents = curl_setopt($ch, CURLOPT_POSTFIELDS,  $arg_string);

		print "url=$url args=$arg_string  ";
		print_r($args);
		#return;

		$contents = curl_exec($ch);
		if ( curl_errno($ch )) {
			$this->error =  "failed: " . curl_error($ch );
			return;
		}
		$this->response_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE);
		if (! $this->response_code ) {
			$this->error = "invalid response code=" . $this->response_code;
			return;
		}
		print " got: $contents  code=" . $this->response_code;

		if ( $this->response_code  != 200) {
			$this->error = "invalid response code=" . $this->response_code;
			return;
		}


		return $contents;
	}
	public function requestAccessToken() {

		global $app_state, $spark_client_id;
		$args = array();

		$args['grant_type'] = 'authorization_code';

		if ( ! isset($this->client_id)) {
			$this->error = "No client ID";
			return;
		}
		$args['client_id'] = $this->client_id;


		if ( ! isset($this->client_secret)) {
			$this->error = "No client secret";
			return;
		}
		$args['client_secret'] = $this->client_secret;

		if ( ! isset($this->access_code)) {
			$this->error = "No code";
			return;
		}
		$args['code'] = $this->access_code;


		if ( ! isset($this->redirect_uri)){
			$this->error = "no URI";
			return;
		}
		$args['redirect_uri'] = $this->redirect_uri;

		$contents = $this->post( $this->access_token_url, $args);
		if ( ! $contents) {
			$this->error = "no contents from post";
			return;
		}

		$token_info = json_decode($contents);
		if ( ! $token_info->{'access_token'} ) {
			$this->error = "post failed message=$contents";
			return;
		}
		print " token=" . $token_info->{'access_token'};
		$this->user_token = $token_info->{'access_token'};
		$this->addUserToken($token_info);
	}
	public function initUserTokens() {
		if ( ! isset($this->user_token_file)){
			return;
		}
		if ( ! is_file($this->user_token_file)) {
			return;
		}

		$this->user_tokens = array();
		#return;

		$fh = fopen($this->user_token_file, 'r');
		if ( ! $fh ) {
			$this->error = 'cannot read token file';
			return;
		}

		$n = 0;
		while ( !feof($fh)) {

			$row = fgets($fh);
			if ( $row == '' ){
				break;
			}
			$n++;

			$items = split("\t", $row);
			/*
			if ( count($items) < 2) {
				$this->error = "invalid row";
				continue;
			}
			*/
			$user_id = $items[0];
			if ( isset($this->user_tokens[$user_id])) {
				$this->error = "multiple rows for user = $user_id";
				continue;
			}

			$this->user_tokens[$user_id] = array();
			$this->user_tokens[$user_id]['access_token'] = $items[1];
			$this->user_tokens[$user_id]['refresh_token'] = $items[2];


		}
		fclose($fh);

		print "N = $n == " . count($this->user_tokens);

	}
	public function addUserToken( $token_info) {
		if ( ! isset($this->user_id)) {
			$this->error = "no user";
			return;
		}

		if ( ! $token_info->{'access_token'}) {
			$this->error = "no token in info";
			return;
		}

		// TODO: change this database (sqllite,mysql), or something else
		if ( ! isset($this->user_token_file)){
			$this->error = "no token file";
			return;
		}

		$fh = fopen($this->user_token_file, 'a');
		if (! $fh) {
			$this->error = 'cannot open token file';
			return;
		}
		if ( isset($this->user_tokens[$this->user_id])) {
			$this->error = "user token already exists: " . $this->user_id;
			return;
		}
		$names = array('access_token', 'refresh_token', 'expires_in', 'refresh_token_expires_in');

		$record = array($this->user_id);
		foreach ( $names as $name ) {
			$record[] = $token_info->{$name};
		}
		fwrite($fh, join("\t", $record) . "\n");


		fclose($fh);
		foreach ( $names as $name ) {
			$this->user_tokens[$this->user_id][$name] = $token_info->{$name};
		}
		print $this->user_id . " add token \n";

	}
	public function setAccessCode( $code ) {
		$this->access_code = $code;
		return;
	}
	public function hasAuthCode() {
		if (! isset($this->user_id)) {
			$this->error = 'no user';
			return;
		}
		print "code = " . $this->access_code;
		if ( isset($this->access_code)) {
			return true;
		}
		return false;
	}
	public function hasUserToken() {
		if ( isset($this->user_token)){
			return true;
		}
		return false;
	}

	public function lookupUserToken( $required = false) {
		if (! isset($this->user_id)) {
			if ( $required ) {
				$this->error = 'no user';
			}
			return;
		}
		if ( ! isset($this->user_tokens[ $this->user_id ] ) ) {
			if ($required) {
				$this->error = $this->user_id . " not found ";
			}
			return;
		}
		$this->user_token = $this->user_tokens[ $this->user_id];
		return $this->user_token;

	}

}
?>

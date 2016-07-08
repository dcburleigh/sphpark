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
class BoxClient {
	
	// setup 
	private $token;
	private $token_valid = 0;
			
	private $host = 'https://api.box.com/2.0/';
	
	private $content_type = 'application/json';
	
	private $client;   // the curl object

	public $response;  //(JSON encoded) string contents of API request 
	public $response_object;  //JSON string decoded into Object
	
	private $response_code;   //  HTTP response code from last Spark call
	public $error;
	
	public $url;
	
	public function __construct( $token = null ) {
	
		if ( $token != null ) {
			$this->setToken($token);
		}
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
		#curl_setopt($this->client , CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json',  "Authorization: Bearer ".$this->token));
		curl_setopt($this->client , CURLOPT_HTTPHEADER, array( 'Accept: application/json',  "Authorization: Bearer ".$this->token));
	}
	public function clearRequest() {
		$self->response = '';
		$self->response_code = '';
		$self->error = '';
	}
	/*
	*/
	public function getToken() {
		return $this->token;
	}
	
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
	
	public function getFile( $args = array() ){

		#curl https://api.box.com/2.0/files/FILE_ID?fields=modified_at,path_collection,name
		#-H "Authorization: Bearer ACCESS_TOKEN"
		
	}
	
	public function getFolderInfo( $folder_id ) {
				
		/*
		 * https://docs.box.com/reference#folder-object-1
		 * 
		 * $vars = array('type', 'id', 'sequence_id', 'etag', 'name', 'created_at', 'description',  'size', 'path_collection'
		 *  , 'created_by', 'modified_by',  'trashed_at', 'purged_at', 'content_created_at', 'owned_by'
		 *  , 'shared_link',  'folder_upload_email', 'parent',  'item_status', 'item_collection', 'sync_state', 
		 *  ,  'has_collaborations',  'permissions', 'tags', 'can_non_owners_invite',  'is_externally_owned'
		 *  , 'allowed_shared_link_access_levels',  'allowed_invite_roles' );
		 */
		$this->get('folders/' . $folder_id );
		return;
	}
	public function getFolderItems( $folder_id, $args = array() ) {
				
		/*
		 * 
		 * curl https://api.box.com/2.0/folders/0?fields=item_collection,name \
-H "Authorization: Bearer ACCESS_TOKEN_OF_THE_ADMIN" \
-H "As-User: 10543463" 

curl https://api.box.com/2.0/folders/FOLDER_ID/items?limit=2&offset=0 \
-H "Authorization: Bearer ACCESS_TOKEN"

		 */
		$this->get('folders/' .  $folder_id . '/items', $args,  array('fields', 'limit', 'offset'));
		return;
	}
	
	public function getAuth( $args = array() ) {
		#curl https://api.box.com/oauth2/token \
		#-d 'grant_type=authorization_code&code=YOUR_AUTH_CODE&client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET' \
		#-X POST
		
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
	
}

?>
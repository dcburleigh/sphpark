<?php

// require_once 'SparkClient.php';
require_once 'HookHandler.php';

class SparkHookHandler extends HookHandler
{

    public $hook_name;

    // Spark-specific?
    public $resource_type;

    public $room_id;

    public $message_id;

    public $signature;
    
    public $validate_signature = false;


    function getRequest()
    {
        parent::getRequest();
        
        // $this->log("data " . print_r($this->data, true) );
        if (! $this->data) {
            $this->log("WARNING: request data is empty");
            return;
        }
        $this->hook_name = $this->data->{'name'};
        $this->resource_type = $this->data->{'resource'};
        
        // ##$this->message_id = $this->data->{'id'};
        
        $this->message_id = $this->data->data->id;
        
        $this->signature = '';
        if (isset($this->headers['X-Spark-Signature'])) {
            $this->signature = $this->headers['X-Spark-Signature'];
            // TODO: decrypt
            //$this->secret = $this->headers['X-Spark-Signature'];
        }
    }
    function checkSignature( $secret = null ){
        if ( ! $this->validate_signature){
            return true;
        }
        if ( ! $secret){
            $secret = $this->secret;
        }
        if ( ! isset($this->signature)){
            $this->error = "No signature found";
            $this->log("No signature found");
            return false;
        }
        if ( ! $secret){
            $this->error = "Secret is not specified";
            $this->log("Secret is not specified");
            return false;
        }
        // $this->log("using secret: $secret \n" . $this->body);
        $signature = hash_hmac('sha1', $this->body, $secret);
        ###$this->log("compare: '$signature' <> " . $this->signature);
        if ( $signature == $this->signature){
            return true;
        }
        $this->error = "invalid signature: '$signature' <> " . $this->signature;
        $this->log("invalid signature: '$signature' <> " . $this->signature);
        $this->log("len=" . strlen( $this->body) );
        return false;
        
    }
}
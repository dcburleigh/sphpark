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

    public $secret_encrypted;

    public $secretc;

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
        
        $this->secret = '';
        if (isset($this->headers['X-Spark-Signature'])) {
            $this->secret_encrypted = $this->headers['X-Spark-Signature'];
            // TODO: decrype
            $this->secret = $this->headers['X-Spark-Signature'];
        }
    }
}
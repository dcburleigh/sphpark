<?php

#require_once 'SparkClient.php';
require_once 'HookHandler.php';

class SparkHookHandler extends HookHandler {

	public $hook_name;

	// Spark-specific?
	public $resource_type;
	public $room_id;
	public $message_id;


	function getRequest() {
		parent::getRequest();


		$this->hook_name = $this->data->{'name'};
		$this->resource_type = $this->data->{'resource'};

		###$this->message_id = $this->data->{'id'};

		$this->message_id = $this->data->data->id;
	}
}
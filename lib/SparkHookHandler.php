<?php

class SparkHookHandler extends HookHandler {
	

	// Spark-specific?
	public $resource_type;

	
	function getRequest() {
		parent::getRequest();
		

		$this->hook_name = $this->data->{'name'};
		$this->resource_type = $this->data->{'resource'};
	}
}
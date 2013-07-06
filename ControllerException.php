<?php
namespace Coxis\Core;

class ControllerException extends \Exception {
	public $response;
	function __construct($msg='', $response=null) {
		$this->message = $msg;
		$this->response = $response;
	}
}
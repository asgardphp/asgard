<?php
namespace Coxis\Core;

class ControllerException extends \Coxis\Core\PSRException {
	public $response;

	function __construct($msg='', $response=null, $severity=null) {
		$this->response = $response;
		parent::__construct($msg, $severity);
	}

	public function getResponse() {
		if($this->response)	
			return $this->response;
		else
			return new \Coxis\Core\Responde(500);
	}

	public function setResponse($response) {
		$this->response = $response;
	}
}
<?php
namespace Asgard\Http;

class ControllerException extends \Asgard\Core\PSRException {
	public $response;

	public function __construct($msg='', $response=null, $severity=null) {
		$this->response = $response;
		parent::__construct($msg, $severity);
	}

	public function getResponse() {
		if($this->response)	
			return $this->response;
		else
			return new \Asgard\Core\Response(500);
	}

	public function setResponse($response) {
		$this->response = $response;
	}
}
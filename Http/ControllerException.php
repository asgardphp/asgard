<?php
namespace Asgard\Http;

class ControllerException extends \Asgard\Debug\PSRException {
	protected $code;
	protected $response;

	public function __construct($code=500, $msg='', $severity=null, $response=null) {
		$this->code = $code;
		$this->response = $response;
		parent::__construct($msg, $severity);
	}

	public function getResponse() {
		if($this->response)	
			return $this->response;
		else
			return new \Asgard\Core\Response($this->code);
	}

	public function setResponse($response) {
		$this->response = $response;
	}

	public function getCode() {
		return $this->code;
	}

	public function setCode($code) {
		$this->code = $code;
	}
}
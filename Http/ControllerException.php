<?php
namespace Asgard\Http;

/**
 * Controller exception.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ControllerException extends \Asgard\Debug\PSRException {
	/**
	 * HTTP code.
	 * @var integer
	 */
	protected $code;
	/**
	 * Response instance.
	 * @var Response
	 */
	protected $response;

	/**
	 * Constructor.
	 * @param integer   $code
	 * @param string    $msg
	 * @param integer   $severity
	 * @param Response  $response
	 */
	public function __construct($code=500, $msg='', $severity=null, $response=null) {
		$this->code = $code;
		$this->response = $response;
		parent::__construct($msg, $severity);
	}

	/**
	 * Get the exception response.
	 * @return Response
	 */
	public function getResponse() {
		if($this->response)
			return $this->response;
		else
			return new \Asgard\Http\Response($this->code);
	}

	/**
	 * Set the response.
	 * @param Response $response
	 */
	public function setResponse($response) {
		$this->response = $response;
	}

	/**
	 * Get the HTTP code.
	 * @return integer
	 */
	public function getHTTPCode() {
		return $this->code;
	}

	/**
	 * Set the HTTP code.
	 * @param integer $code
	 */
	public function setCode($code) {
		$this->code = $code;
	}
}
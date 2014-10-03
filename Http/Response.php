<?php
namespace Asgard\Http;

/**
 * Response.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Response {
	/**
	 * Request.
	 * @var Request
	 */
	protected $request;
	/**
	 * Response content.
	 * @var string
	 */
	protected $content;
	/**
	 * Response HTTP code.
	 * @var integer
	 */
	protected $code = 200;
	/**
	 * Response headers.
	 * @var array
	 */
	protected $headers = [];
	/**
	 * Codes title.
	 * @var array
	 */
	protected static $codes = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		118 => 'Connection timed out',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		210 => 'Content Different',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Moved Temporarily',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		310 => 'Too many Redirects',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested range unsatisfiable',
		417 => 'Expectation failed',
		418 => 'I\'m a teapot',
		422 => 'Unprocessable entity',
		423 => 'Locked',
		424 => 'Method failure',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		449 => 'Retry With',
		450 => 'Blocked by Windows Parental Controls',
		456 => 'Unrecoverable Error',
		499 => 'client has closed connection',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway ou Proxy Error',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
		506 => 'Variant also negociate',
		507 => 'Insufficient storage',
		508 => 'Loop detected',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not extended',
		520 => 'Web server is returning an unknown error',
	];

	/**
	 * Constructor.
	 * @param integer $code
	 */
	public function __construct($code = 200) {
		$this->code = $code;
	}

	/**
	 * Set Request.
	 * @param Request $request
	 */
	public function setRequest(Request $request) {
		$this->request = $request;
		return $this;
	}

	/**
	 * Set HTTP code.
	 * @param integer $code
	 */
	public function setCode($code) {
		$this->code = $code;
		return $this;
	}

	/**
	 * Get HTTP code.
	 * @return integer
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Get Request.
	 * @return Request
	 */
	public function getRequest() {
		return $this->request;
	}

	/**
	 * Set a header.
	 * @param string    $header
	 * @param string    $value
	 * @return Response $this
	 */
	public function setHeader($header, $value) {
		$this->headers[strtolower($header)] = $value;
		return $this;
	}

	/**
	 * Get a header.
	 * @param  string $header string
	 * @return string
	 */
	public function getHeader($header) {
		if(!isset($this->headers[strtolower($header)]))
			return;
		return $this->headers[strtolower($header)];
	}

	/**
	 * Get headers.
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}

	/**
	 * Set the content.
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	/**
	 * Get the content.
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Send headers.
	 */
	public function sendHeaders() {
		if(headers_sent())
			return;

		$headers = [];
		if(array_key_exists($this->code, static::$codes))
			$headers[] = 'HTTP/1.1 '.$this->code.' '.static::$codes[$this->code];
		else
			$headers[] = 'HTTP/1.1 200 '.static::$codes[200];
		foreach($this->headers as $k=>$v)
			$headers[] = $k.': '.$v;

		foreach($headers as $h)
			header($h);
	}

	/**
	 * Send the response.
	 */
	public function send() {
		$this->sendHeaders();
		echo $this->content;
	}

	/**
	 * Redirect the user to a given url.
	 * @param  string $url
	 * @return Response
	 */
	public function redirect($url='') {
		if(!preg_match('/^http:\/\//', $url))
			$url = $this->request->url->to($url);
		$this->headers['Location'] = $url;
		return $this;
	}

	/**
	 * __toString magic method.
	 * @return string
	 */
	public function __toString() {
		$r = 'Code: '.$this->getCode()."\n\n".
			'Headers: '."\n";
		foreach($this->headers as $header=>$value)
			$r .= $header.': '.$value;
		$r .= "\n\n".'Content: '."\n".$this->content;
		return $r;
	}

	/**
	 * Check if response code is ok.
	 * @return boolean
	 */
	public function isOK() {
		return $this->getCode() >= 200 && $this->getCode() < 300;
	}
}
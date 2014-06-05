<?php
namespace Asgard\Http;

class Response {
	public $content;
	public $code = 200;
	public $headers = array();
	protected static $codes = array(
		200 => 'OK',
		201 => 'Created',
		204 => 'No Content',
		
		301 => 'Moved Permanently',
		
		400 => 'Bad Request',
		401 => 'Unauthorized',
		404 => 'Not Found',
		
		500 => 'Internal Server Error',
	);

	public function __construct($code = 200) {
		$this->code = $code;
	}

	public function setCode($code) {
		$this->code = $code;
		return $this;
	} 

	public function getCode() { 
		return $this->code;
	} 

	public function setHeader($header, $value) {
		$this->headers[strtolower($header)] = $value;
		return $this;
	}

	public function getHeader($header) {
		if(!isset($this->headers[strtolower($header)]))
			return;
		return $this->headers[strtolower($header)];
	}

	public function getHeaders() {
		return $this->headers;
	}

	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	public function getContent() {
		return $this->content;
	}

	public function sendHeaders() {
		if(headers_sent())
			return;
	
		$headers = array();
		if(array_key_exists($this->code, static::$codes))
			$headers[] = 'HTTP/1.1 '.$this->code.' '.static::$codes[$this->code];
		else
			$headers[] = 'HTTP/1.1 200 '.static::$codes[200];
		foreach($this->headers as $k=>$v)
			$headers[] = $k.': '.$v;
			
		foreach($headers as $h)
			header($h);
	}

	public function send() {
		$this->sendHeaders();
		echo $this->content;
	}
	
	public function redirect($url='') {
		if(!preg_match('/^http:\/\//', $url))
			$url = \Asgard\Core\App::instance()['request']->url->to($url);
		$this->headers['Location'] = $url;
		return $this;
	}

	public function __toString() {
		$r = 'Code: '.$this->getCode()."\n\n".
			'Headers: '."\n";
		foreach($this->headers as $header=>$value)
			$r .= $header.': '.$value;
		$r .= "\n\n".'Content: '."\n".$this->content;
		return $r;
	}

	public function isOK() {
		return $this->getCode() < 300;
	}
}
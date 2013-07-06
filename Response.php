<?php
namespace Coxis\Core;

class Response {
	protected $instance;#todo what for ?
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
		return $this->headers[strtolower($header)];
	}

	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	public function getContent() {
		return $this->content;
	}

	public function sendHeaders($headers=null) {
		if(headers_sent())
			return;
			
		try {
			while(ob_end_clean()){}
		} catch(\Exception $e) {}
	
		if(!$headers) {
			$headers = array();
			if(array_key_exists($this->code, static::$codes))
				$headers[] = 'HTTP/1.1 '.$this->code.' '.static::$codes[$this->code];
			else
				$headers[] = 'HTTP/1.1 200 '.static::$codes[200];
			foreach($this->headers as $k=>$v)
				$headers[] = $k.': '.$v;
		}
			
		foreach($headers as $h)
			header($h);
	}

	public function send($kill=true) {
		\Hook::trigger('output');
		
		$headers = array();
		if(array_key_exists($this->code, static::$codes))
			$headers[] = 'HTTP/1.1 '.$this->code.' '.static::$codes[$this->code];
		else
			$headers[] = 'HTTP/1.1 200 '.static::$codes[200];
		foreach($this->headers as $k=>$v)
			$headers[] = $k.': '.$v;

		$this->doSend($headers, $this->content, $kill);
	}

	protected function doSend($headers, $content, $kill) {
		\Hook::trigger('end');
		\Coxis\Core\Response::sendHeaders($headers);
		echo $content;
		Profiler::checkpoint('Sending the response');
		if($kill)
			exit();
	}

	public function back() {
		return $this->redirect(Server::get('HTTP_REFERER'), false);
	}
	
	public function redirect($url='', $relative=true) {
		if($relative && !preg_match('/http:\/\//', $url))
			$this->headers['Location'] = \URL::to($url);
		else
			$this->headers['Location'] = $url;
		return $this;
	}

	public function __toString() {
		return $this->content;
	}
}
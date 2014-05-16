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
		\Asgard\Core\App::get('hook')->trigger('output');

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
		while(ob_get_level())
			ob_end_clean();
		\Asgard\Core\App::get('hook')->trigger('end');
		\Asgard\Core\App::get('response')->sendHeaders($headers);
		echo $content;
        if(\Asgard\Core\App::get('config')->get('profiler'))
                \Asgard\Utils\Profiler::report();
		if($kill)
			exit();
	}

	public function back() {
		return $this->redirect(\Asgard\Core\App::get('server')->get('HTTP_REFERER'), false);
	}
	
	public function redirect($url='', $relative=true) {
		if($relative && !preg_match('/^http:\/\//', $url))
			$this->headers['Location'] = \Asgard\Core\App::get('url')->to($url);
		else
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
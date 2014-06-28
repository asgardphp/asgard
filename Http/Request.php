<?php
namespace Asgard\Http;

class Request implements \ArrayAccess {
	static protected $instance;
	public $get;
	public $post;
	public $file;
	public $server;
	public $cookie;
	public $header;
	public $session;
	public $body = '';
	public $url;

	public $isInitial = false;

	public $params = [
		'format'	=>	'html',
	];

	public static function singleton() {
		if(!static::$instance)
			static::$instance = static::createFromGlobals();
		return static::$instance;
	}

	public static function setInstance($instance) {
		static::$instance = $instance;
	}

	public function __construct() {
		$this->url = new \Asgard\Http\URL($this);
		$this->get = new \Asgard\Common\Bag;
		$this->post = new \Asgard\Common\Bag;
		$this->file = new \Asgard\Common\Bag;
		$this->server = new \Asgard\Common\Bag;
		$this->cookie = new \Asgard\Common\Bag;
		$this->header = new \Asgard\Common\Bag;
		$this->session = new \Asgard\Common\Bag;
	}

	public static function createFromGlobals() {
		$request = new static;
		$request->get->setAll($_GET);
		$request->post->setAll($_POST);
		$request->file->setAll($_FILES);
		$request->header->setAll(static::getAllHeaders());
		$request->server->setAll($_SERVER);
		$request->cookie = new CookieManager;
		$request->session = new SessionManager;
		$request->body = file_get_contents('php://input');

		$server = trim($request->server['SERVER_NAME'], '/');
		if(isset($request->server['SERVER_PORT']) && $request->server['SERVER_PORT'] != '80')
			$server .= ':'.$request->server['SERVER_PORT'];
		if($request->server->has('ORIG_SCRIPT_NAME'))
			$root = dirname($request->server['ORIG_SCRIPT_NAME']);
		else
			$root = dirname($request->server['SCRIPT_NAME']);

		if($request->server->has('PATH_INFO'))
			$url = $request->server['PATH_INFO'];
		elseif($request->server->has('ORIG_PATH_INFO'))
			$url = $request->server['ORIG_PATH_INFO'];
		elseif($request->server->has('REDIRECT_URL'))
			$url = $request->server['REDIRECT_URL'];
		else
			$url = '';
		$url = preg_replace('/^'.preg_quote($root, '/').'/', '', $url);
		$url = trim($url, '/');
		$root = trim($root, '/');

		$request->setURL($server, $root, $url);

		return $request;
	}

	public function getJSON() {
		try {
			return json_decode($this->body, true);
		} catch(\Exception $e) {}
	}

	public function setJSON($data) {
		$this->body = json_encode($data);
	}

	public function setURL($server, $root, $url) {
		$this->url = new \Asgard\Http\URL($this, $server, $root, $url);

		preg_match('/\.([a-zA-Z0-9]{1,5})$/', $this->url->get(), $matches);
		if(isset($matches[1]))
			$this->setParam('format', $matches[1]);
		return $this;
	}

	public function getParam($name) {
		return $this->params[$name];
	}

	public function setParam($name, $value=null) {
		if(is_array($name)) {
			foreach($name as $k=>$v)
				$this->setParam($k, $v);
			return $this;
		}
		$this->params[$name] = $value;
		return $this;
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset))
			$this->params[] = $value;
		else
			$this->params[$offset] = $value;
	}

	public function offsetExists($offset) {
		return isset($this->params[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->params[$offset]);
	}
	
	public function offsetGet($offset) {
		return isset($this->params[$offset]) ? $this->params[$offset] : null;
	}

	public function format() {
		preg_match('/\.([^\.]+)$/', $this->url->get(), $matches);
		if(isset($matches[1]))
			return $matches[1];
		return 'html';
	}

	public function method() {
		return isset($this->server['REQUEST_METHOD']) ? strtoupper($this->server['REQUEST_METHOD']):'GET';
	}

	public function setMethod($value) {
		$this->server['REQUEST_METHOD'] = $value;
		return $this;
	}

	public function ip() {
		return $this->server['REMOTE_ADDR'];
	}

	public function setIP($value) {
		$this->server['REMOTE_ADDR'] = $value;
		return $this;
	}

	public function referer() {
		return $this->server['HTTP_REFERER'];
	}

	public function setReferer($value) {
		$this->server['HTTP_REFERER'] = $value;
		return $this;
	}

	public function getBody() {
		return $this->body;
	}

	public function setBody($value) {
		$this->body = $value;
		return $this;
	}

	protected static function getAllHeaders() {
		$headers = [];
		foreach($_SERVER as $name => $value) {
			if(substr($name, 0, 5) == 'HTTP_')
				$headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
		}
		return $headers; 
	}
}
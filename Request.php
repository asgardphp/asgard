<?php
namespace Asgard\Http;

class Request implements \ArrayAccess {
	public $get;
	public $post;
	public $file;
	public $server;
	public $cookie;
	public $session;
	public $data = '';
	public $url;

	public $isInitial = false;

	public $params = array(
		'format'	=>	'html',
	);

	public function __construct() {
		$this->url = new \Asgard\Http\URL($this);
		$this->get = new \Asgard\Http\Inputs\GET;
		$this->post = new \Asgard\Http\Inputs\POST;
		$this->file = new \Asgard\Http\Inputs\File;
		$this->server = new \Asgard\Http\Inputs\Server;
		$this->cookie = new \Asgard\Http\Inputs\Cookie;
		$this->header = new \Asgard\Http\Inputs\Header;
		$this->session = new \Asgard\Http\Inputs\Session;
	}

	public static function createFromGlobals() {
		$request = new static;
		$request->get->_setAll($_GET);
		$request->post->_setAll($_POST);
		$request->file->_setAll($_FILES);
		$request->cookie->_setAll($_COOKIE);
		$request->header->_setAll(\Asgard\Utils\Tools::getallheaders());
		$request->server->_setAll($_SERVER);
		try {
			$request->sessionStart();
			$request->session->_setAll(isset($_SESSION) ? $_SESSION:array());
		} catch(\ErrorException $e) {}
		$request->body = file_get_contents('php://input');

		$server = trim($request->server->get('SERVER_NAME'), '/');
		if($request->server->has('ORIG_SCRIPT_NAME'))
			$root = dirname($request->server->get('ORIG_SCRIPT_NAME'));
		else
			$root = dirname($request->server->get('SCRIPT_NAME'));
		if($request->server->has('PATH_INFO'))
			$url = $request->server->get('PATH_INFO');
		elseif($request->server->has('ORIG_PATH_INFO'))
			$url = $request->server->get('ORIG_PATH_INFO');
		elseif($request->server->has('REDIRECT_URL'))
			$url = $request->server->get('REDIRECT_URL');
		else
			$url = '';
		$url = preg_replace('/^\//', '', $url);

		$request->setURL($server, $root, $url);

		return $request;
	}

	public function getJSON() {
		try {
			return json_decode($request->body);
		} catch(\Exception $e) {}
	}

	public function setJSON($data) {
		$request->body = json_encode($data);
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

	protected function sessionStart() {
		if(!headers_sent()) {
			if(isset($this->get['PHPSESSID']))
				session_id($this->get['PHPSESSID']);
			elseif(isset($this->post['PHPSESSID']))
				session_id($this->post['PHPSESSID']);
			session_start();
		}
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

	public function body() {
		return $this->body;
	}

	public function setbody($value) {
		$this->body = $value;
		return $this;
	}
}
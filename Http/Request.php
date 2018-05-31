<?php
namespace Asgard\Http;

/**
 * Request class.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Request implements \ArrayAccess {
	/**
	 * Default instance.
	 * @var Request
	 */
	static protected $instance;
	/**
	 * GET input.
	 * @var \Asgard\Common\BagInterface
	 */
	public $get;
	/**
	 * POST input.
	 * @var \Asgard\Common\BagInterface
	 */
	public $post;
	/**
	 * FILE input.
	 * @var \Asgard\Common\BagInterface
	 */
	public $file;
	/**
	 * SERVER input.
	 * @var \Asgard\Common\BagInterface
	 */
	public $server;
	/**
	 * COOKIE input.
	 * @var \Asgard\Common\BagInterface
	 */
	public $cookie;
	/**
	 * Headers input.
	 * @var \Asgard\Common\BagInterface
	 */
	public $header;
	/**
	 * Request body.
	 * @var string
	 */
	public $body = '';
	/**
	 * Request url.
	 * @var URLInterface
	 */
	public $url;
	/**
	 * Boolean to check if it is the initial request.
	 * @var boolean
	 */
	public $isInitial = false;
	/**
	 * Request parameters.
	 * @var array
	 */
	protected $params = [
		'format'	=>	'html',
	];
	/**
	 * Route.
	 * @var Route
	 */
	protected $route;

	public function __clone() {
		$this->get = clone $this->get;
		$this->post = clone $this->post;
		$this->file = clone $this->file;
		$this->server = clone $this->server;
		$this->cookie = clone $this->cookie;
		$this->header = clone $this->header;
		$this->url = clone $this->url;
	}

	/**
	 * Return the default instance.
	 * @return Request
	 */
	public static function singleton() {
		if(!static::$instance)
			static::$instance = static::createFromGlobals();
		return static::$instance;
	}

	/**
	 * Set the default instance.
	 * @param Request $instance
	 */
	public static function setInstance(Request $instance) {
		static::$instance = $instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->url = new \Asgard\Http\URL($this);
		$this->get = new \Asgard\Common\Bag;
		$this->post = new \Asgard\Common\Bag;
		$this->file = new \Asgard\Common\Bag;
		$this->server = new \Asgard\Common\Bag;
		$this->cookie = new \Asgard\Common\Bag;
		$this->header = new \Asgard\Common\Bag;
	}

	/**
	 * Create a Request from the global inputs.
	 * @return Request
	 */
	public static function createFromGlobals() {
		$request = new static;
		$request->get->setAll($_GET);
		$request->post->setAll($_POST);
		$request->setFiles($_FILES);
		$request->header->setAll(static::getAllHeaders());
		$request->server->setAll($_SERVER);
		$request->cookie->setAll($_COOKIE);
		$request->body = file_get_contents('php://input');

		$server = trim($request->server['SERVER_NAME'], '/');
		if(isset($request->server['SERVER_PORT']) && $request->server['SERVER_PORT'] != '80')
			$server .= ':'.$request->server['SERVER_PORT'];
		if($request->server->has('ORIG_SCRIPT_NAME'))
			$root = dirname($request->server['ORIG_SCRIPT_NAME']);
		else
			$root = dirname($request->server['SCRIPT_NAME']);
		$root = trim($root, '/');

		if($request->header->has('X_ORIGINAL_URL'))
			$url = $request->header['X_ORIGINAL_URL'];
		if($request->header->has('X_REWRITE_URL'))
			$url = $request->header['X_REWRITE_URL'];
		elseif($request->server->has('IIS_WasUrlRewritten'))
			$url = $request->server['IIS_WasUrlRewritten'];
		elseif($request->server->has('REQUEST_URI'))
			$url = $request->server['REQUEST_URI'];
		elseif($request->server->has('ORIG_PATH_INFO'))
			$url = $request->server['ORIG_PATH_INFO'];
		else
			$url = '';
		$url = ltrim($url, '/');
		$parse = parse_url($url);
		if(isset($parse['path']))
			$url = $parse['path'];
		else
			$url = '';
		$root = trim($root, '/');
		$url = preg_replace('/^'.preg_quote($root, '/').'/', '', $url);

		$protocol = $request->server['REQUEST_SCHEME'].'://';

		$request->setURL($server, $root, $url, $protocol);

		return $request;
	}

	/**
	 * Set the files.
	 * @param array $files
	 */
	public function setFiles($files) {
		$files = $this->parseFiles($files);
		$this->file->setAll($files);
	}

	/**
	 * Parse the JSON body.
	 * @return mixed
	 */
	public function getJSON() {
		#attempt to decode json
		try {
			return json_decode($this->body, true);
		}
		catch(\Exception $e) {}
		catch(\Throwable $e) {}
	}

	/**
	 * Set the JSON body.
	 * @param mixed $data
	 */
	public function setJSON($data) {
		$this->body = json_encode($data);
	}

	/**
	 * Set the url.
	 * @param string $server
	 * @param string $root
	 * @param string $url
	 */
	public function setURL($server, $root, $url, $protocol=null) {
		$this->url = new \Asgard\Http\URL($this, $server, $root, $url, $protocol);

		preg_match('/\.([a-zA-Z0-9]{1,5})$/', $this->url->get(), $matches);
		if(isset($matches[1]))
			$this->setParam('format', $matches[1]);
		return $this;
	}

	/**
	 * Get a parameter.
	 * @param  string $name
	 * @return mixed
	 */
	public function getParam($name) {
		return $this->params[$name];
	}

	/**
	 * Set a parameter.
	 * @param string   $name
	 * @param mixed    $value
	 * @return Request $this
	 */
	public function setParam($name, $value=null) {
		if(is_array($name)) {
			foreach($name as $k=>$v)
				$this->setParam($k, $v);
			return $this;
		}
		$this->params[$name] = $value;
		return $this;
	}

	/**
	 * Array set implementation.
	 * @param  string $offset
	 * @param  mixed $value
	 */
	public function offsetSet($offset, $value) {
		if (is_null($offset))
			$this->params[] = $value;
		else
			$this->params[$offset] = $value;
	}

	/**
	 * Array exists implementation.
	 * @param  string $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return isset($this->params[$offset]);
	}

	/**
	 * Array unset implementation.
	 * @param  string $offset
	 */
	public function offsetUnset($offset) {
		unset($this->params[$offset]);
	}

	/**
	 * Array get implementation.
	 * @param  string $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return isset($this->params[$offset]) ? $this->params[$offset] : null;
	}

	/**
	 * Get the request format.
	 * @return string
	 */
	public function format() {
		preg_match('/\.([^\.]+)$/', $this->url->get(), $matches);
		if(isset($matches[1]))
			return $matches[1];
		return 'html';
	}

	/**
	 * Get the request method.
	 * @return string
	 */
	public function method() {
		return isset($this->server['REQUEST_METHOD']) ? strtoupper($this->server['REQUEST_METHOD']):'GET';
	}

	/**
	 * Set the request method.
	 * @param string $value
	 */
	public function setMethod($value) {
		$this->server['REQUEST_METHOD'] = $value;
		return $this;
	}

	/**
	 * Get the IP address.
	 * @return string
	 */
	public function ip() {
		return $this->server['REMOTE_ADDR'];
	}

	/**
	 * Set the IP address.
	 * @param string $value
	 */
	public function setIP($value) {
		$this->server['REMOTE_ADDR'] = $value;
		return $this;
	}

	/**
	 * Get the referer.
	 * @return string
	 */
	public function referer() {
		return $this->server['HTTP_REFERER'];
	}

	/**
	 * Set the referer.
	 * @param string $value
	 */
	public function setReferer($value) {
		$this->server['HTTP_REFERER'] = $value;
		return $this;
	}

	/**
	 * Get the body.
	 * @return string
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * Set the body.
	 * @param string $value
	 */
	public function setBody($value) {
		$this->body = $value;
		return $this;
	}

	/**
	 * Fetch headers.
	 * @return array
	 */
	protected static function getAllHeaders() {
		$headers = [];
		foreach($_SERVER as $name => $value) {
			if(substr($name, 0, 5) == 'HTTP_')
				$headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
		}
		return $headers;
	}

	/**
	 * Parse FILE input and return HttpFile objects.
	 * @param  array  $raw
	 * @return array
	 */
	protected function parseFiles(array $raw) {
		if(isset($raw['name']) && isset($raw['type']) && isset($raw['tmp_name']) && isset($raw['error']) && isset($raw['size'])) {
			if(is_array($raw['name'])) {
				$name = $this->convertTo('name', $raw['name']);
				$type = $this->convertTo('type', $raw['type']);
				$tmp_name = $this->convertTo('tmp_name', $raw['tmp_name']);
				$error = $this->convertTo('error', $raw['error']);
				$size = $this->convertTo('size', $raw['size']);

				$files = $this->mergeAll($name, $type, $tmp_name, $error, $size);

				foreach($files as $k=>$v)
					$files[$k] = \Asgard\Http\HttpFile::createFromArray($v);
			}
			else
				$files = \Asgard\Http\HttpFile::createFromArray($raw);
		}
		else {
			foreach($raw as $k=>$v)
				$raw[$k] = $this->parseFiles($v);
			$files = $raw;
		}

		return $files;
	}

	/**
	 * Used by parseFiles.
	 * @param  string $type
	 * @param  array  $files
	 * @return array
	 */
	protected function convertTo($type, array $files) {
		$res = [];
		foreach($files as $name=>$file) {
			if(is_array($file))
				$res[$name] = $this->convertTo($type, $file);
			else
				$res[$name][$type] = $file;
		}

		return $res;
	}

	/**
	 * Used by parseFiles.
	 * @param  array  $name
	 * @param  array  $type
	 * @param  array  $tmp_name
	 * @param  array  $error
	 * @param  array  $size
	 * @return array
	 */
	protected function mergeAll(array $name, array $type, array $tmp_name, array $error, array $size) {
		foreach($name as $k=>$v) {
			if(isset($v['name']) && !is_array($v['name']))
				$name[$k] = array_merge($v, $type[$k], $tmp_name[$k], $error[$k], $size[$k]);
			else
				$name[$k] = $this->mergeAll($name[$k], $type[$k], $tmp_name[$k], $error[$k], $size[$k]);
		}

		return $name;
	}

	public function setRoute(Route $route) {
		$this->route = $route;
		return $this;
	}

	public function getRoute() {
		return $this->route;
	}
}
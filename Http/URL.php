<?php
namespace Asgard\Http;

/**
 * URL class.
 */
class URL {
	/**
	 * Request instance.
	 * @var Request
	 */
	public $request;
	/**
	 * Host address.
	 * @var string
	 */
	public $host;
	/**
	 * Root path.
	 * @var string
	 */
	public $root;
	/**
	 * Url.
	 * @var string
	 */
	public $url;

	/**
	 * Constructor.
	 * @param Request $request
	 * @param string  $host
	 * @param string  $root
	 * @param string  $url
	 */
	public function __construct(Request $request, $host=null, $root=null, $url=null) {
		$this->request = $request;
		$this->host = $host;
		$this->root = $root;
		$this->url = $url;
	}

	/**
	 * Return the url.
	 * @return string
	 */
	public function get() {
		return $this->url;
	}
	
	/**
	 * Set the url.
	 * @param string $url
	 */
	public function setURL($url) {
		return $this->url = $url;
	}
	
	/**
	 * Set the host address.
	 * @param string $host
	 */
	public function setHost($host) {
		return $this->host = $host;
	}
	
	/**
	 * Set the root path.
	 * @param string $root
	 */
	public function setRoot($root) {
		return $this->root = $root;
	}
	
	/**
	 * Return the current url.
	 * @return string
	 */
	public function current() {
		return $this->base().$this->get();
	}

	/**
	 * Return the url parameters.
	 * @param  array $params To override existing parameters.
	 * @return string
	 */
	public function getParams(array $params=[]) {
		if($params = array_merge($this->request->get->all(), $params))
			return '?'.http_build_query($params);
	}
	
	/**
	 * Return the full url.
	 * @param  arrray $params To override existing parameters.
	 * @return string
	 */
	public function full(array $params=[]) {
		$r = $this->current();
		$r .= $this->getParams($params);
		return $r;
	}
	
	/**
	 * Return the base url.
	 * @return string
	 */
	public function base() {
		$res = $this->protocol().$this->host().'/';
		if($this->root())
			$res .= $this->root().'/';
		return $res;
	}
	
	/**
	 * Set the base url.
	 * @param string $base
	 */
	public function setBase($base) {
		$parse = parse_url($base);
		if(!isset($parse['path']))
			$parse['path'] = '/';
		$this->setHost($parse['host']);
		$this->setRoot($parse['path']);
	}
	
	/**
	 * Create the absolute url to a relative one.
	 * @param  string $url relative url
	 * @return string
	 */
	public function to($url) {
		return $this->base().$url;
	}
	
	/**
	 * Return the root path.
	 * @return string
	 */
	public function root() {
		$result = $this->root;
		
		$result = str_replace('\\', '/', $result);
		$result = trim($result, '/');
		$result = str_replace('//', '/', $result);
		
		return $result;
	}
	
	/**
	 * Return the host address.
	 * @return string
	 */
	public function host() {
		if($this->host !== null)
			return $this->host;
		else
			return '';
	}

	/**
	 * Return the protocol.
	 * @return string
	 */
	public function protocol() {
		#only http supported as for now.
		return 'http://';
	}

	/**
	 * Check if the url starts with a given string.
	 * @param  string $what
	 * @return boolean
	 */
	public function startsWith($what) {
		return strpos($this->get(), $what) === 0;
	}
}

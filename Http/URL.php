<?php
namespace Asgard\Http;

/**
 * URL class.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class URL implements URLInterface {
	/**
	 * Request instance.
	 * @var Request
	 */
	protected $request;
	/**
	 * Host address.
	 * @var string
	 */
	protected $host;
	/**
	 * Root path.
	 * @var string
	 */
	protected $root;
	/**
	 * Url.
	 * @var string
	 */
	protected $url;
	/**
	 * Protocol.
	 * @var string
	 */
	protected $protocol;

	/**
	 * Constructor.
	 * @param Request $request
	 * @param string  $host
	 * @param string  $root
	 * @param string  $url
	 */
	public function __construct(Request $request, $host=null, $root=null, $url=null, $protocol=null) {
		$this->request = $request;
		$this->host = $host;
		$this->root = $root;
		$this->url = $url;
		$this->protocol = $protocol;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get() {
		return $this->url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setURL($url) {
		return $this->url = $url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setHost($host) {
		return $this->host = $host;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setRoot($root) {
		return $this->root = $root;
	}

	/**
	 * {@inheritDoc}
	 */
	public function current() {
		return $this->base().$this->get();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParams(array $params=[]) {
		if($params = array_merge($this->request->get->all(), $params))
			return '?'.http_build_query($params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function full(array $params=[]) {
		$r = $this->current();
		$r .= $this->getParams($params);
		return $r;
	}

	/**
	 * {@inheritDoc}
	 */
	public function base() {
		$res = $this->protocol().$this->host().'/';
		if($this->root())
			$res .= $this->root().'/';
		return $res;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setBase($base) {
		$parse = parse_url($base);
		if(!isset($parse['path']))
			$parse['path'] = '/';
		$this->setHost($parse['host']);
		$this->setRoot($parse['path']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function to($url) {
		return $this->base().$url;
	}

	/**
	 * {@inheritDoc}
	 */
	public function root() {
		$result = $this->root;

		$result = str_replace('\\', '/', $result);
		$result = trim($result, '/');
		$result = str_replace('//', '/', $result);

		return $result;
	}

	/**
	 * {@inheritDoc}
	 */
	public function host() {
		if($this->host !== null)
			return $this->host;
		else
			return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function protocol() {
		if(!$this->protocol)
			return 'http://';
		else
			return $this->protocol;
	}

	/**
	 * {@inheritDoc}
	 */
	public function startsWith($what) {
		return strpos($this->get(), $what) === 0;
	}
}

<?php
namespace Asgard\Http\Browser;

/**
 * Browser.
 */
class Browser {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Cookies bag.
	 * @var \Asgard\Common\Bag
	 */
	protected $cookies;
	/**
	 * Session bag.
	 * @var \Asgard\Common\Bag
	 */
	protected $session;
	/**
	 * Last response.
	 * @var Response
	 */
	protected $last;
	/**
	 * CatchException parameter.
	 * @var boolean
	 */
	protected $catchException = false;

	/**
	 * Constructor.
	 * @param \Asgard\Container\Container $container
	 */
	public function __construct(\Asgard\Container\Container $container) {
		$this->container = $container;
		$this->cookies = new \Asgard\Common\Bag;
		$this->session = new \Asgard\Common\Bag;
	}

	/**
	 * Return cookies.
	 * @return \Asgard\Common\Bag
	 */
	public function getCookies() {
		return $this->cookies;
	}

	/**
	 * Return session.
	 * @return \Asgard\Common\Bag
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * Get last response.
	 * @return Response
	 */
	public function getLast() {
		return $this->last;
	}

	/**
	 * Execute a GET request.
	 * @param  string $url
	 * @param  string $body
	 * @param  array  $headers
	 * @return Response
	 */
	public function get($url='', $body='', array $headers=[]) {
		return $this->req($url, 'GET', [], [], $body, $headers);
	}

	/**
	 * Execute a POST request.
	 * @param  string $url
	 * @param  array  $post
	 * @param  arra   $files
	 * @param  string $body
	 * @param  array  $headers
	 * @return Response
	 */
	public function post($url='', array $post=[], array $files=[], $body='', array $headers=[]) {
		return $this->req($url, 'POST', $post, $files, $body, $headers);
	}

	/**
	 * Execute a PUT request.
	 * @param  string $url
	 * @param  array  $post
	 * @param  arra   $files
	 * @param  string $body
	 * @param  array  $headers
	 * @return Response
	 */
	public function put($url='', array $post=[], array $files=[], $body='', array $headers=[]) {
		return $this->req($url, 'PUT', $post, $files, $body, $headers);
	}

	/**
	 * Execute a DELETE request.
	 * @param  string $url
	 * @param  string $body
	 * @param  array  $headers
	 * @return Response
	 */
	public function delete($url='', $body='', array $headers=[]) {
		return $this->req($url, 'DELETE', [], [], $body, $headers);
	}

	/**
	 * Execute a request.
	 * @param  string $url
	 * @param  string $method
	 * @param  array  $post
	 * @param  arra   $file
	 * @param  string $body
	 * @param  array  $headers
	 * @param  array  $server
	 * @return Response
	 */
	public function req(
			$url='',
			$method='GET',
			array $post=[],
			array $file=[],
			$body='',
			array $headers=[],
			array $server=[]
		) {
		if(defined('_TESTING_'))
			file_put_contents(_TESTING_, $url."\n", FILE_APPEND);

		#build request
		$get = [];
		$url = ltrim($url, '/');
		$infos = parse_url($url);
		if(isset($infos['query'])) {
			parse_str($infos['query'], $get);
			$url = preg_replace('/(\?.*)$/', '', $url);
		}
		$request = new \Asgard\Http\Request;
		$request->get->setAll($get);
		$request->post->setAll($post);
		$request->setFiles($file);
		$this->createTemporaryFiles($request->file->all());
		$request->header->setAll($headers);
		$request->server->setAll($server);
		$request->setMethod($method);
		$request->cookie = $this->cookies;
		$request->session = $this->session;
		if(count($post))
			$request->body = http_build_query($post);
		else
			$request->body = $body;

		$request->url->setURL($url);
		$request->url->setHost('localhost');
		$request->url->setRoot('');

		$httpKernel = $this->container['httpKernel'];
		$res = $httpKernel->process($request, $this->catchException);

		$this->last = $res;
		$this->cookies = $request->cookie;
		$this->session = $request->session;

		return $res;
	}

	/**
	 * Set the catchException parameter.
	 * @param  boolean $catchException
	 */
	public function catchException($catchException) {
		$this->catchException = $catchException;
	}

	/**
	 * Submit a form.
	 * @param  string $xpath   path to submit button.
	 * @param  string $to      destination url.
	 * @param  array $override override post attributes.
	 * @return Response
	 */
	public function submit($xpath='//form', $to=null, array $override=[]) {
		if($this->last === null)
			throw new \Exception('No page to submit from.');
		libxml_use_internal_errors(true);

		$parser = new FormParser;
		$parser->parse($this->last->content, $xpath);
		$res = $parser->values();
		$this->merge($res, $override);

		return $this->post($to, $res);
	}

	/**
	 * Merge 2 arrays.
	 * @param  array  $arr1
	 * @param  array  $arr2
	 * @return array
	 */
	protected function merge(array &$arr1, array &$arr2) {
		foreach($arr2 as $k=>$v) {
			if(is_array($arr1[$k]) && is_array($arr2[$k]))
				$this->merge($arr1[$k], $arr2[$k]);
			elseif($arr1[$k] !== $arr2[$k])
				$arr1[$k] = $arr2[$k];
		}
	}

	/**
	 * Create temporary files.
	 * @param  array $files
	 */
	protected function createTemporaryFiles($files) {
		foreach($files as $file) {
			if(is_array($file))
				$this->createTemporaryFiles($file);
			else {
				do {
					$dst = sys_get_temp_dir().uniqid().'.tmp';
					if(!file_exists($dst))
						break;
				} while(true);
				copy($file->src(), $dst);
				$file->setSrc($dst);
			}
		}
	}
}
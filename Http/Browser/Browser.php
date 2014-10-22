<?php
namespace Asgard\Http\Browser;

/**
 * Browser.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Browser implements BrowserInterface {
	/**
	 * Cookies bag.
	 * @var \Asgard\Common\BagInterface
	 */
	protected $cookies;
	/**
	 * Session bag.
	 * @var \Asgard\Common\BagInterface
	 */
	protected $session;
	/**
	 * Last response.
	 * @var \Asgard\Http\Response
	 */
	protected $last;
	/**
	 * CatchException parameter.
	 * @var boolean
	 */
	protected $catchException = false;
	/**
	 * Http Kernel dependency.
	 * @var \Asgard\Http\HttpKernelInterface
	 */
	protected $httpKernel;

	/**
	 * Constructor.
	 * @param \Asgard\Http\HttpKernelInterface $httpKernel
	 */
	public function __construct(\Asgard\Http\HttpKernelInterface $httpKernel) {
		$this->httpKernel = $httpKernel;
		$this->cookies = new \Asgard\Common\Bag;
		$this->session = new \Asgard\Common\Bag;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCookies() {
		return $this->cookies;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLast() {
		return $this->last;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($url='', $body='', array $headers=[]) {
		return $this->req($url, 'GET', [], [], $body, $headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function post($url='', array $post=[], array $files=[], $body='', array $headers=[]) {
		return $this->req($url, 'POST', $post, $files, $body, $headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function put($url='', array $post=[], array $files=[], $body='', array $headers=[]) {
		return $this->req($url, 'PUT', $post, $files, $body, $headers);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete($url='', $body='', array $headers=[]) {
		return $this->req($url, 'DELETE', [], [], $body, $headers);
	}

	/**
	 * {@inheritDoc}
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

		$res = $this->httpKernel->process($request, $this->catchException);

		$this->last = $res;
		$this->cookies = $request->cookie;
		$this->session = $request->session;

		return $res;
	}

	/**
	 * {@inheritDoc}
	 */
	public function catchException($catchException) {
		$this->catchException = $catchException;
	}

	/**
	 * {@inheritDoc}
	 */
	public function submit($xpath='//form', $to=null, array $override=[]) {
		if($this->last === null)
			throw new \Exception('No page to submit from.');
		libxml_use_internal_errors(true);

		$parser = new FormParser;
		$parser->parse($this->last->getContent(), $xpath);
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
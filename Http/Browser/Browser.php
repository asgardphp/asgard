<?php
namespace Asgard\Http\Browser;

class Browser {
	protected $cookies;
	protected $session;
	protected $last;
	protected $app;
	protected $catchException = false;

	public function __construct(\Asgard\Core\App $app) {
		$this->app = $app;
		$this->cookies = new CookieManager;
		$this->session = new SessionManager;
	}

	public function getCookies() {
		return $this->cookies;
	}

	public function getSession() {
		return $this->session;
	}

	public function getLast() {
		return $this->last;
	}

	public function get($url='', $body='', array $headers=array()) {
		return $this->req($url, 'GET', array(), array(), $body, $headers);
	}

	public function post($url='', array $post=array(), array $files=array(), $body='', array $headers=array()) {
		return $this->req($url, 'POST', $post, $files, $body, $headers);
	}

	public function put($url='', array $post=array(), array $files=array(), $body='', array $headers=array()) {
		return $this->req($url, 'PUT', $post, $files, $body, $headers);
	}

	public function delete($url='', $body='', array $headers=array()) {
		return $this->req($url, 'DELETE', array(), array(), $body, $headers);
	}

	public function req(
			$url='',
			$method='GET',
			array $post=array(),
			array $file=array(),
			$body='',
			array $headers=array()
		) {
		#build request
		$get = array();
		$infos = parse_url($url);
		if(isset($infos['query'])) {
			parse_str($infos['query'], $get);
			$url = preg_replace('/(\?.*)$/', '', $url);
		}
		$request = new \Asgard\Http\Request;
		$request->setMethod($method);
		$request->get->setAll($get);
		$request->post->setAll($post);
		$request->file->setAll($file);
		$request->header->setAll($headers);
		$request->cookie = $this->cookies;
		$request->session = $this->session;
		if(count($post))
			$request->body = http_build_query($post);
		else
			$request->body = $body;

		$request->url->setURL($url);
		$request->url->setHost('localhost');
		$request->url->setRoot('');

		// $httpKernel = new \Asgard\Http\HttpKernel($this->app);
		$httpKernel = $this->app['httpKernel'];
		$res = $httpKernel->process($request, $this->catchException);

		$this->last = $res;
		$this->cookies = $request->cookie;
		$this->session = $request->session;

		return $res;
	}

	public function catchException($catchException) {
		$this->catchException = $catchException;
	}

	public function submit($item=0, $to=null, array $override=array()) {
		if($this->last === null)
			throw new \Exception('No page to submit from.');
		libxml_use_internal_errors(true);
		$orig = new \DOMDocument();
		$orig->loadHTML($this->last->content);
		$node = $orig->getElementsByTagName('form')->item($item);

		$parser = new FormParser;
		$parser->parse($node);
		$parser->clickOn('send');
		$res = $parser->values();
		$this->merge($res, $override);

		return $this->post($to, $res);
	}

	protected function merge(array &$arr1, array &$arr2) {
		foreach($arr2 as $k=>$v) {
			if(is_array($arr1[$k]) && is_array($arr2[$k]))
				$this->merge($arr1[$k], $arr2[$k]);
			elseif($arr1[$k] !== $arr2[$k])
				$arr1[$k] = $arr2[$k];
		}
	}
}
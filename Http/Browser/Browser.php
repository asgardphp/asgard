<?php
namespace Asgard\Http\Browser;

class Browser {
	use \Asgard\Container\ContainerAware;

	protected $cookies;
	protected $session;
	protected $last;
	protected $catchException = false;

	public function __construct(\Asgard\Container\Container $container) {
		$this->container = $container;
		$this->cookies = new \Asgard\Common\Bag;
		$this->session = new \Asgard\Common\Bag;
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

	public function get($url='', $body='', array $headers=[]) {
		return $this->req($url, 'GET', [], [], $body, $headers);
	}

	public function post($url='', array $post=[], array $files=[], $body='', array $headers=[]) {
		return $this->req($url, 'POST', $post, $files, $body, $headers);
	}

	public function put($url='', array $post=[], array $files=[], $body='', array $headers=[]) {
		return $this->req($url, 'PUT', $post, $files, $body, $headers);
	}

	public function delete($url='', $body='', array $headers=[]) {
		return $this->req($url, 'DELETE', [], [], $body, $headers);
	}

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

	public function catchException($catchException) {
		$this->catchException = $catchException;
	}

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

	protected function merge(array &$arr1, array &$arr2) {
		foreach($arr2 as $k=>$v) {
			if(is_array($arr1[$k]) && is_array($arr2[$k]))
				$this->merge($arr1[$k], $arr2[$k]);
			elseif($arr1[$k] !== $arr2[$k])
				$arr1[$k] = $arr2[$k];
		}
	}

	protected function createTemporaryFiles($files) {
		foreach($files as $file) {
			if(is_array($file))
				$this->createTemporaryFiles($file);
			else {
				while(true) {
					$dst = sys_get_temp_dir().uniqid().'.tmp';
					if(!file_exists($dst))
						break;
				}
				copy($file->src(), $dst);
				$file->setSrc($dst);
			}
		}
	}
}
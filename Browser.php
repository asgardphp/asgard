<?php
namespace Coxis\Utils;

class Browser {
	public $session = array();
	public $cookies = array();
	public $last;

	public function get($url='', $body='') {
		return $this->req($url, 'GET', array(), array(), $body);
	}

	public function post($url='', $post=array(), $files=array(), $body='') {
		return $this->req($url, 'POST', $post, $files, $body);
	}

	public function put($url='', $post=array(), $files=array(), $body='') {
		return $this->req($url, 'PUT', $post, $files, $body);
	}

	public function delete($url='', $body='') {
		return $this->req($url, 'DELETE', array(), array(), $body);
	}

	public function req(
			$url='',
			$method='GET',
			$post=array(),
			$file=array(),
			$body=''
		) {
		#build request
		$get = array();
		$infos = parse_url($url);
		if(isset($infos['query'])) {
			parse_str($infos['query'], $get);
			$url = preg_replace('/(\?.*)$/', '', $url);
		}
		$request = new \Coxis\Core\Request;
		$request->setMethod($method);
		$request->get->setAll($get);
		$request->post->setAll($post);
		$request->file->setAll($file);
		$request->cookie->setAll($this->cookies);
		$request->session->setAll($this->session);
		if(sizeof($post))
			$request->body = http_build_query($post);
		else
			$request->body = $body;

		App::instance()->request = $request;

		$request->url->setURL($url);
		$request->url->setServer('localhost');
		$request->url->setRoot('');

		$res = \Coxis\Core\HttpKernel::process(\Request::inst());

		$this->last = $res;
		$this->cookies = $request->cookie->all();
		$this->session = $request->session->all();

		return $res;
	}

	public function submit($item=0, $to=null, $override=array()) {
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

	protected function merge(&$arr1, &$arr2) {
		foreach($arr2 as $k=>$v) {
			if(is_array($arr1[$k]) && is_array($arr2[$k])) {
				$this->merge($arr1[$k], $arr2[$k]);
			}
			elseif($arr1[$k] !== $arr2[$k]) {
				$arr1[$k] = $arr2[$k];
			}
		}
	}
}
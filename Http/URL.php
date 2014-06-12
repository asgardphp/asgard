<?php
namespace Asgard\Http;

class URL {
	public $request;
	public $host;
	public $root;
	public $url;

	public function __construct(\Asgard\Http\Request $request, $host=null, $root=null, $url=null) {
		$this->request = $request;
		$this->host = $host;
		$this->root = $root;
		$this->url = $url;
	}

	public function get() {
		return $this->url;
	}
	
	public function setURL($url) {
		return $this->url = $url;
	}
	
	public function setHost($host) {
		return $this->host = $host;
	}
	
	public function setRoot($root) {
		return $this->root = $root;
	}
	
	public function current() {
		return $this->base().$this->get();
	}
	
	public function full(array $params=[]) {
		$r = $this->current();
		if($params = array_merge($this->request->get->all(), $params))
			$r .= '?'.http_build_query($params);
		return $r;
	}
	
	public function base() {
		$res = $this->host().'/';
		if($this->root())
			$res .= $this->root().'/';
		return $res;
	}
	
	public function setBase($base) {
		$parse = parse_url($base);
		if(!isset($parse['path']))
			$parse['path'] = '/';
		$this->setHost($parse['host']);
		$this->setRoot($parse['path']);
	}
	
	public function to($url) {
		return $this->base().$url;
	}
	
	public function root() {
		$result = $this->root;
		
		$result = str_replace('\\', '/', $result);
		$result = trim($result, '/');
		$result = str_replace('//', '/', $result);
		
		return $result;
	}
	
	public function host() {
		if($this->host !== null)
			return 'http://'.$this->host;
		else
			return '';
	}

	public function startsWith($what) {
		return !!preg_match('/^'.preg_quote($what, '/').'/', $this->get());
	}
}

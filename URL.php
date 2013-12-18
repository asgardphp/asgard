<?php
namespace Coxis\Core;

class URL {
	public $request;
	public $server;
	public $root;
	public $url;

	function __construct($request, $server=null, $root=null, $url=null) {
		$this->request = $request;
		$this->server = $server;
		$this->root = $root;
		$this->url = $url;
	}

	public function get() {
		return $this->url;
	}
	
	public function setURL($url) {
		return $this->url = $url;
	}
	
	public function setServer($server) {
		return $this->server = $server;
	}
	
	public function setRoot($root) {
		return $this->root = $root;
	}
	
	public function current() {
		return $this->base().$this->get();
	}
	
	public function full($params=array()) {
		$r = $this->current();
		if($params = array_merge($this->request->get->all(), $params))
			$r .= '?'.http_build_query($params);
		return $r;
	}
	
	public function base() {
		$res = $this->server().'/';
		if($this->root())
			$res .= $this->root().'/';
		return $res;
	}
	
	public function setBase($base) {
		$parse = parse_url($base);
		if(!isset($parse['path']))
			$parse['path'] = '/';
		$this->setServer($parse['host']);
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
	
	public function server() {
		if($this->host() !== null)
			return 'http://'.$this->host();
		else
			return '';
	}
	
	public function host() {
		if($this->server !== null)
			return $this->server;
		else
			return '';
	}

	public function url_for($what, $params=array(), $relative=true) {
		#controller/action
		if(is_array($what)) {
			$controller = strtolower($what[0]);
			$action = strtolower($what[1]);
			foreach(\Resolver::getRoutes() as $route_params) {
				$route = $route_params->getRoute();
				if(strtolower($route_params->getController()) == $controller && strtolower($route_params->getAction()) == $action) {
					if($route_params->get('host'))
						return 'http://'.$route_params->get('host').'/'.\Resolver::buildRoute($route, $params);
					elseif($relative)
						return \Resolver::buildRoute($route, $params);
					else
						return $this->to(\Resolver::buildRoute($route, $params));
				}
			}
		}
		#route
		else {
			$what = strtolower($what);
			foreach(\Resolver::getRoutes() as $route_params) {
				$route = $route_params->getRoute();
				if($route_params->get('name') != null && strtolower($route_params->get('name')) == $what) {
					if($route_params->get('host'))
						return 'http://'.$route_params->get('host').'/'.\Resolver::buildRoute($route, $params);
					elseif($relative)
						return \Resolver::buildRoute($route, $params);
					else
						return $this->to(\Resolver::buildRoute($route, $params));
				}
			}
		}
					
		throw new \Exception('Route not found.');
	}

	public function startsWith($what) {
		return preg_match('/^'.preg_quote($what, '/').'/', $this->get());
	}
}

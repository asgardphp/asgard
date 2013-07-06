<?php
namespace Coxis\Core;

class Router {
	protected $routes = array();

	public static function formatControllerName($controller) {
		return preg_replace('/Controller$/i', '', $controller);
	}

	public static function formatActionName($action) {
		return preg_replace('/Action$/i', '', $action);
	}

	public function setRoutes($routes) {
		$this->routes = $routes;
	}

	public function addRoute($route) {
		$this->routes[] = $route;
	}

	public function dispatch($request, $response=null) {
		$route = $this->parseRoutes($request);
		if(method_exists($route['controller'].'Controller', $route['action'].'Action'))
			return Controller::run($route['controller'], $route['action'], $request, $response);
		else
			throw new NotFoundException('Page not found');
	}

	//todo ADD root /
	public static function formatRoute($route) {
		return '/'.trim($route, '/');
	}
	
	public static function matchWith($route, $with, $requirements=array(), $request=null, $method=null) {
		if($method) {
			if(is_array($method)) {
				$good = false;
				foreach($method as $v)
					if(strtolower($server_method) == $v)
						$good = true;
				if(!$good)
					return false;
			}
			elseif($request && strtolower($method) != $request->method())
				return false;
		}
				
		$regex = static::getRegexFromRoute($route, $requirements);
		$matches = array();
		$res = preg_match_all('/^'.$regex.'(?:\.[a-zA-Z0-9]{1,5})?\/?$/', $with, $matches);
		
		if($res == 0)
			return false;
		else {
			$results = array();
			/* EXTRACTS VARIABLES */
			preg_match_all('/:([a-zA-Z0-9_]+)/', $route, $keys);
			for($i=0; $i<sizeof($keys[1]); $i++)
				$results[$keys[1][$i]] = $matches[$i+1][0];
			
			return $results;
		}
	}
	
	public static function match($request, $route, $requirements=array(), $method=null) {
		$with = static::formatRoute($request->url->get());
		return static::matchWith($route, $with, $requirements, $request, $method);
	}
	
	public static function getRegexFromRoute($route, $requirements) {
		preg_match_all('/:([a-zA-Z0-9_]+)/', $route, $symbols);
		$regex = preg_quote($route, '/');
			
		/* REPLACE EACH SYMBOL WITH ITS REGEX */
		foreach($symbols[1] as $symbol) {
			if(is_array($requirements) && array_key_exists($symbol, $requirements)) {
				$requirement = $requirements[$symbol];
				switch($requirement['type']) {
					case 'regex':
						$replacement = $requirement['regex']; break;
					case 'integer':
						$replacement = '[0-9]+'; break;
					//todo int, etc.
				}
			}
			else
				$replacement = '[^\/]+';
			
			$regex = preg_replace('/\\\:'.$symbol.'/', '('.$replacement.')', $regex);
		}
		
		return $regex;
	}

	public function parseRoutes($request) {
		$request_key = md5(serialize(array($request->method(), $request->url->get())));
		#todo complete key

		$routes = $this->routes;
		$results = \Coxis\Utils\Cache::get('Router/requests/'.$request_key, function() use($routes, $request) {
			\Coxis\Core\Router::sortRoutes($routes);
			/* PARSE ALL ROUTES */
			foreach($routes as $params) {
				$route = $params['route'];
				$requirements = $params['requirements'];
				$method = $params['method'];

				/* IF THE ROUTE MATCHES */
				if(($results = \Coxis\Core\Router::match($request, $route, $requirements, $method)) !== false)
					return array('params' => $results, 'route' => $params);
			}
		});

		if(!$results)
			throw new \Coxis\Core\Exceptions\NotFoundException;

		$request->setParam($results['params']);

		return $results['route'];
	}

	public static function sortRoutes(&$routes) {
		usort($routes, function($r1, $r2) {
			$route1 = $r1['route'];
			$route2 = $r2['route'];
			
			if($route1 == $route2) {
				if(isset($r1['host']) && $r1['host'])
					return -1;
				else
					return 1;
			}
			
			while(true) {
				if(!$route1)
					return 1;
				if(!$route2)
					return -1;
				$c1 = substr($route1, 0, 1);
				$c2 = substr($route2, 0, 1);
				if($c1 == ':' && $c2 != ':')
					return 1;
				elseif($c1 != ':' && $c2 == ':')
					return -1;
				elseif($c1 != ':' && $c2 != ':') {
					$route1 = substr($route1, 1);
					$route2 = substr($route2, 1);
				}
				elseif($c1 == ':' && $c2 == ':') {
					$route1 = preg_replace('/^:[a-zA-Z0-9_]+/', '', $route1);
					$route2 = preg_replace('/^:[a-zA-Z0-9_]+/', '', $route2);
				}
			}
		});
	}
	
	public static function buildRoute($route, $params=array()) {
		foreach($params as $symbol=>$param) {
			$count = 0;
			$route = str_replace(':'.$symbol, $param, $route, $count);
			if($count)
				unset($params[$symbol]);
		}
		if($params)
			$route .= '?';
		$i=0;
		foreach($params as $symbol=>$param) {
			if($i > 0)
				$route .= '&;';
			$route .= urlencode($symbol).'='.urlencode($param);
			$i++;
		}
			
		if(preg_match('/:([a-zA-Z0-9_]+)/', $route))
			throw new \Exception('Missing parameter for route: '.$route);
			
		return trim($route, '/');
	}
	
	public function getRouteFor($what) {
		foreach($this->routes as $route)
			if($route['controller'] == $what[0] && $route['action'] == $what[1])
				return $route['route'];
	}

	public function getRoutes() {
		return $this->routes;
	}
}

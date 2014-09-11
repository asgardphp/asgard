<?php
namespace Asgard\Http;

/**
 * Resolve routes to actions.
 */
class Resolver {
	/**
	 * Routes
	 * @var array
	 */
	protected $routes = [];
	/**
	 * Http kernel object.
	 * @var HttpKernel
	 */
	protected $httpKernel;
	/**
	 * Cache instance.
	 * @var \Asgard\Cache\Cache
	 */
	protected $cache;
	/**
	 * Cached results.
	 * @var array
	 */
	protected $results = [];

	/**
	 * Constructor.
	 * @param \Asgard\Cache\Cache $cache
	 */
	public function __construct($cache) {
		$this->cache = $cache;
	}

	/**
	 * Set the routes.
	 * @param array $routes
	 */
	public function setRoutes(array $routes) {
		$this->routes = $routes;
	}

	/**
	 * Add routes.
	 * @param array $routes
	 */
	public function addRoutes(array $routes) {
		foreach($routes as $route)
			$this->addRoute($route);
	}

	/**
	 * Add one route.
	 * @param Route $route
	 */
	public function addRoute($route) {
		$this->routes[] = $route;
	}
	
	/**
	 * Check if a request matches a route.
	 * @param  Request     $request
	 * @param  string      $route
	 * @param  array|null  $requirements
	 * @param  string|null $method
	 * @return boolean
	 */
	public static function match(Request $request, $route, $requirements=null, $method=null) {
		$with = trim($request->url->get(), '/');
		return static::matchWith($route, $with, $requirements, $request, $method);
	}
	
	/**
	 * Actually check if request and route match.
	 * @param  string       $route
	 * @param  string       $with
	 * @param  array|null   $requirements
	 * @param  Request      $request
	 * @param  string|null  $method
	 * @return boolean
	 */
	public static function matchWith($route, $with, $requirements=null, $request=null, $method=null) {
		if($method !== null) {
			if(is_array($method)) {
				$good = false;
				foreach($method as $v) {
					if(strtoupper($v) == $request->method())
						$good = true;
				}
				if(!$good)
					return false;
			}
			elseif($request && strtoupper($method) != $request->method())
				return false;
		}
				
		$regex = static::getRegexFromRoute($route, $requirements);
		$matches = [];
		$res = preg_match_all('/^'.$regex.'\/?$/', $with, $matches);
		
		if($res == 0)
			return false;
		else {
			$results = [];
			/* EXTRACTS VARIABLES */
			preg_match_all('/:([a-zA-Z0-9_]+)/', $route, $keys);
			for($i=0; $i<count($keys[1]); $i++)
				$results[$keys[1][$i]] = $matches[$i+1][0];
			
			return $results;
		}
	}
	
	/**
	 * Get a regex from a route.
	 * @param  string     $route
	 * @param  array|null $requirements
	 * @return string
	 */
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
				}
			}
			if(!isset($replacement))
				$replacement = '[^\/]+';
			
			$regex = preg_replace('/\\\:'.$symbol.'/', '('.$replacement.')', $regex);
		}
		
		return $regex;
	}

	/**
	 * Get the route matching a request.
	 * @param  Request     $request
	 * @return array|null  null if not found.
	 */
	public function getRoute(Request $request) {
		$request_key = sha1(serialize([$request->method(), $request->url->get()]));

		$results = $this->cache->fetch('Router/requests/'.$request_key, function() use($request) {
			/* PARSE ALL ROUTES */
			foreach($this->routes as $r) {
				$route = $r->getRoute();
				$requirements = $r->get('requirements');
				$method = $r->get('method');

				/* IF THE ROUTE MATCHES */
				if(($results = static::match($request, $route, $requirements, $method)) !== false)
					return ['route' => $r, 'params' => $results];
			}
		});

		if(!$results)
			return null;

		$request->setParam($results['params']);

		return $results['route'];
	}

	/**
	 * Sort the routes by order of coverage. Routes covering less first.
	 */
	public function sortRoutes() {
		usort($this->routes, function($r1, $r2) {
			$route1 = $r1->getRoute();
			$route2 = $r2->getRoute();
			
			if($route1 == $route2) {
				if($r1->get('host'))
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
					if($c1 !== $c2)
						return $c1 > $c2;
					$route1 = substr($route1, 1);
					$route2 = substr($route2, 1);
				}
				elseif($c1 == ':' && $c2 == ':') {
					$route1 = preg_replace('/^:[a-zA-Z0-9_]+/', '', $route1);
					$route2 = preg_replace('/^:[a-zA-Z0-9_]+/', '', $route2);
				}
			}
		});

		return $this;
	}
	
	/**
	 * [buildRoute description]
	 * @param  string     $route
	 * @param  array      $params
	 * @throws \Exception If a parameter is missing for the route.
	 * @return string
	 */
	public static function buildRoute($route, array $params=[]) {
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

	/**
	 * Return the routes.
	 * @return array
	 */
	public function getRoutes() {
		return $this->routes;
	}

	/**
	 * Return the url for a route or a controller/action couple.
	 * @param  array|string  $what
	 * @param  array         $params
	 * @throws \Exception    If route not found.
	 * @return string
	 */
	public function url_for($what, array $params=[]) {
		#controller/action
		if(is_array($what)) {
			$controller = strtolower($what[0]);
			$action = strtolower($what[1]);
			foreach($this->getRoutes() as $routeObj) {
				$route = $routeObj->getRoute();
				if(strtolower($routeObj->getController()) == $controller && strtolower($routeObj->getAction()) == $action) {
					if($routeObj->get('host'))
						return 'http://'.$routeObj->get('host').'/'.static::buildRoute($route, $params);
					else
						return $this->getUrl()->to(static::buildRoute($route, $params));
				}
			}
		}
		#route
		else {
			$what = strtolower($what);
			foreach($this->getRoutes() as $routeObj) {
				$route = $routeObj->getRoute();
				if($routeObj->get('name') !== null && strtolower($routeObj->get('name')) == $what) {
					if($routeObj->get('host'))
						return 'http://'.$routeObj->get('host').'/'.static::buildRoute($route, $params);
					else
						return $this->getUrl()->to(static::buildRoute($route, $params));
				}
			}
		}
					
		throw new \Exception('Route not found.');
	}

	/**
	 * Set the HttpKernel dependency.
	 * @param HttpKernel $httpKernel
	 */
	public function setHttpKernel(HttpKernel $httpKernel) {
		$this->httpKernel = $httpKernel;
	}

	/**
	 * Get the url instance.
	 * @return URL
	 */
	public function getUrl() {
		return $this->httpKernel->getLastRequest()->url;
	}
}

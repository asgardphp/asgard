<?php
namespace Asgard\Http;

/**
 * Resolve routes to actions.
 */
class Resolver implements ResolverInterface {
	/**
	 * Routes
	 * @var array
	 */
	protected $routes = [];
	/**
	 * Http kernel object.
	 * @var HttpKernelInterface
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
	public function __construct(\Asgard\Cache\Cache $cache=null) {
		if(!$cache)
			$cache = new \Asgard\Cache\Cache(new \Asgard\Cache\NullCache);
		$this->cache = $cache;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setRoutes(array $routes) {
		$this->routes = $routes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addRoutes(array $routes) {
		foreach($routes as $route)
			$this->addRoute($route);
	}

	/**
	 * {@inheritDoc}
	 */
	public function addRoute($route) {
		$this->routes[] = $route;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function match(Request $request, $route, $requirements=null, $method=null) {
		$with = trim($request->url->get(), '/');
		return static::matchWith($route, $with, $requirements, $request, $method);
	}

	/**
	 * {@inheritDoc}
	 */
	public static function matchWith($route, $with, $requirements=null, $request=null, $method=null) {
		if($method !== null && $request!==null) {
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
	 * {@inheritDoc}
	 */
	public static function getRegexFromRoute($route, $requirements=null) {
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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function getRoutes() {
		return $this->routes;
	}

	/**
	 * {@inheritDoc}
	 */
	public function url($what, array $params=[]) {
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
	 * {@inheritDoc}
	 */
	public function setHttpKernel(HttpKernelInterface $httpKernel) {
		$this->httpKernel = $httpKernel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getUrl() {
		return $this->httpKernel->getRequest()->url;
	}
}

<?php
namespace Asgard\Http;

/**
 * Resolve routes to actions.
 */
interface ResolverInterface {
	/**
	 * Set the routes.
	 * @param array $routes
	 */
	public function setRoutes(array $routes);

	/**
	 * Add routes.
	 * @param array $routes
	 */
	public function addRoutes(array $routes);

	/**
	 * Add one route.
	 * @param Route $route
	 */
	public function addRoute($route);
	
	/**
	 * Check if a request matches a route.
	 * @param  Request $request
	 * @param  string  $route
	 * @param  array   $requirements
	 * @param  string  $method
	 * @return boolean
	 */
	public static function match(Request $request, $route, $requirements=null, $method=null);
	
	/**
	 * Actually check if url and route match.
	 * @param  string  $route
	 * @param  string  $with
	 * @param  array   $requirements
	 * @param  Request $request
	 * @param  string  $method
	 * @return boolean
	 */
	public static function matchWith($route, $with, $requirements=null, $request=null, $method=null);
	
	/**
	 * Get a regex from a route.
	 * @param  string     $route
	 * @param  array|null $requirements
	 * @return string
	 */
	public static function getRegexFromRoute($route, $requirements);

	/**
	 * Get the route matching a request.
	 * @param  Request   $request
	 * @return Route     null if not found.
	 */
	public function getRoute(Request $request);

	/**
	 * Sort the routes by order of coverage. Routes covering less first.
	 */
	public function sortRoutes();
	
	/**
	 * Build an URL from a route.
	 * @param  string     $route
	 * @param  array      $params
	 * @throws \Exception If a parameter is missing for the route.
	 * @return string
	 */
	public static function buildRoute($route, array $params=[]);

	/**
	 * Return the routes.
	 * @return array
	 */
	public function getRoutes();

	/**
	 * Return the url for a route or a controller/action couple.
	 * @param  array|string  $what
	 * @param  array         $params
	 * @throws \Exception    If route not found.
	 * @return string
	 */
	public function url($what, array $params=[]);

	/**
	 * Set the HttpKernel dependency.
	 * @param HttpKernelInterface $httpKernel
	 */
	public function setHttpKernel(HttpKernelInterface $httpKernel);

	/**
	 * Get the url instance.
	 * @return URLInterface
	 */
	public function getUrl();
}

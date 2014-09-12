<?php
namespace Asgard\Http;

#For doctrine, which does not autoload classes...
require_once __DIR__.'/Annotations/Prefix.php';
require_once __DIR__.'/Annotations/Route.php';

/**
 * Annotations reader.
 * @author Michel Hognerud <michel@hognerud.net>
*/
class AnnotationsReader {
	/**
	 * Cache instance.
	 * @var \Doctrine\Cache\Cache
	 */
	protected $cache;
	/**
	 * Activate debugging.
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * Return the routes of a controller.
	 * @return array
	*/
	public function fetchRoutes($class) {
		$routes = [];

		$reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
		$reader->addNamespace('Asgard\Http\Annotations');
		if($this->cache) {
			$reader = new \Doctrine\Common\Annotations\CachedReader(
				$reader,
				$this->cache,
				$this->debug
			);
		}

		$reflection = new \ReflectionClass($class);
		$prefix = $reader->getClassAnnotation($reflection, 'Asgard\Http\Annotations\Prefix');
		$prefix = $prefix !== null ? $prefix->value:'';

		foreach($reflection->getMethods() as $method) {
			if(!preg_match('/Action$/i', $method->getName()))
				continue;
			$routeAnnot = $reader->getMethodAnnotation($method, 'Asgard\Http\Annotations\Route');
			if($routeAnnot !== null) {
				$route = trim($prefix.'/'.$routeAnnot->value, '/');
				$routes[] = new Route(
					$route,
					$class,
					preg_replace('/Action$/i', '', $method->getName()),
					[
						'host' => $routeAnnot->host,
						'requirements' => $routeAnnot->requirements,
						'method' => $routeAnnot->method,
						'name'	=>	$routeAnnot->name
					]
				);
			}
		}

		return $routes;
	}

	/**
	 * Set the cache dependency.
	 * @param \Doctrine\Cache\Cache $cache
	 */
	public function setCache(\Doctrine\Cache\Cache $cache) {
		$this->cache = $cache;
		return $this;
	}

	/**
	 * Set the debug parameter.
	 * @param boolean $debug true to debug, false otherwise
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
		return $this;
	}
}
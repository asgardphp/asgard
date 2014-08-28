<?php
namespace Asgard\Http;

#For doctrine, which does not autoload classes...
require_once __DIR__.'/Annotations/Prefix.php';
require_once __DIR__.'/Annotations/Route.php';

abstract class Controller {
	use \Asgard\Hook\HookableTrait;
	use \Asgard\Templating\ViewableTrait;
	use \Asgard\Container\ContainerAwareTrait;
	
	public $request;
	public $response;
	protected $action;
	protected $beforeFilters = [];
	protected $afterFilters = [];

	/* ANNOTATIONS */
	public static function fetchRoutes() {
		$routes = [];
		$class = get_called_class();

		$reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
		$reader->addNamespace('Asgard\Http\Annotations');
		$reader = new \Doctrine\Common\Annotations\CachedReader(
			$reader,
			\Asgard\Container\Container::singleton()['cache'],
			\Asgard\Container\Container::singleton()->has('config') ? \Asgard\Container\Container::singleton()['config']['debug']:false
		);

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

	public static function routeFor($action) {
		$routes = static::routesFor($action);
		if(!isset($routes[0]))
			return;
		return $routes[0];
	}

	public static function routesFor($action) {
		$routes = [];
		$class = get_called_class();

		$reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
		$reader->addNamespace('Asgard\Http\Annotations');
		$reader = new \Doctrine\Common\Annotations\CachedReader(
			$reader,
			\Asgard\Container\Container::singleton()['cache'],
			\Asgard\Container\Container::singleton()->has('config') ? \Asgard\Container\Container::singleton()['config']['debug']:false
		);

		$reflection = new \ReflectionClass($class);
		$prefix = $reader->getClassAnnotation($reflection, 'Asgard\Http\Annotations\Prefix');
		$prefix = $prefix !== null ? $prefix->value:'';

		foreach($reflection->getMethods() as $method) {
			if(!preg_match('/Action$/i', $method->getName()) || $method->getName() !== $action.'Action')
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

	/* FILTERS */
	public function addFilter($filter) {
		$filter->setController($this);
		$this->addBeforeFilter([$filter, 'before']);
		$this->addAfterFilter([$filter, 'after']);
	}

	public function addBeforeFilter($filter) {
		$this->beforeFilters[] = $filter;
	}

	public function addAfterFilter($filter) {
		$this->afterFilters[] = $filter;
	}

	public function run($action, $request=null) {
		$this->action = $action;
		$this->view = $action;

		if($request === null)
			$request = new Request;
		$this->request = $request;
		$this->response = new Response;

		#before filters
		$result = null;
		foreach($this->beforeFilters as $filter) {
			if(($result = $filter($this, $request)) !== null)
				break;
		}

		if($result === null) {
			if(($result = $this->before($request)) === null)
				$result = $this->doRun($action, [$request]);
		}

		$this->after($request, $result);

		#after filters
		foreach($this->afterFilters as $filter)
			$filter($this, $request, $result);

		if($result instanceof Response)
			return $result;
		elseif(is_string($result))
			return $this->response->setContent($result);

		return $this->response;
	}

	protected function doRun($method, array $args=[]) {
		$method .= 'Action';
		return $this->runTemplate($method, $args);
	}

	public function before(\Asgard\Http\Request $request) {
	}

	public function after(\Asgard\Http\Request $request, &$result) {
	}

	public function getAction() {
		return $this->action;
	}

	/* UTILS */
	public function getFlash() {
		return new \Asgard\Http\Utils\Flash($this->request->session);
	}

	public function back() {
		return $this->response->redirect($this->request->server['HTTP_REFERER']);
	}

	public function notFound($msg=null) {
		throw new Exceptions\NotFoundException($msg);
	}
	
	public function url_for($action, $params=[]) {
		return $this->container['resolver']->url_for([get_called_class(), $action], $params);
	}
}
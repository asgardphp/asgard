<?php
namespace Asgard\Http;

#For doctrine, which does not autoload classes...
require_once __DIR__.'/Annotations/Prefix.php';
require_once __DIR__.'/Annotations/Route.php';

abstract class Controller extends \Asgard\Hook\Hookable {
	protected $_view;
	public $request;
	public $response;
	protected $app;

	/* ANNOTATIONS */
	public static function fetchRoutes() {
		$routes = array();
		$class = get_called_class();

		$reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
		$reader->addNamespace('Asgard\Http\Annotations');
		$reader = new \Doctrine\Common\Annotations\CachedReader(
			$reader,
			\Asgard\Core\App::instance()['cache'],
			\Asgard\Core\App::instance()['config']['debug']
		);

		$reflection = new \ReflectionClass($class);
		$prefix = $reader->getClassAnnotation($reflection, 'Asgard\Http\Annotations\Prefix');
		$prefix = $prefix !== null ? $prefix->value:'';

		foreach($reflection->getMethods() as $method) {
			if(!preg_match('/Action$/i', $method->getName()))
				continue;
			$routeAnnot = $reader->getMethodAnnotation($method, 'Asgard\Http\Annotations\Route');
			if($routeAnnot !== null) {
				$route = Resolver::formatRoute($prefix.'/'.$routeAnnot->value);

				$routes[] = new ControllerRoute(
					$route,
					$class,
					Resolver::formatActionName($method->getName()),
					array(
						'host' => $routeAnnot->host,
						'requirements' => $routeAnnot->requirements,
						'method' => $routeAnnot->method,
						'name'	=>	$routeAnnot->name
					)
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
		$routes = array();
		$class = get_called_class();

		$reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
		$reader->addNamespace('Asgard\Http\Annotations');
		$reader = new \Doctrine\Common\Annotations\CachedReader(
			$reader,
			\Asgard\Core\App::instance()['cache'],
			\Asgard\Core\App::instance()['config']['debug']
		);

		$reflection = new \ReflectionClass($class);
		$prefix = $reader->getClassAnnotation($reflection, 'Asgard\Http\Annotations\Prefix');
		$prefix = $prefix !== null ? $prefix->value:'';

		foreach($reflection->getMethods() as $method) {
			if(!preg_match('/Action$/i', $method->getName()))
				continue;
			$routeAnnot = $reader->getMethodAnnotation($method, 'Asgard\Http\Annotations\Route');
			if($routeAnnot !== null) {
				$route = Resolver::formatRoute($prefix.'/'.$routeAnnot->value);

				$routes[] = new ControllerRoute(
					$route,
					$class,
					Resolver::formatActionName($method->getName()),
					array(
						'host' => $routeAnnot->host,
						'requirements' => $routeAnnot->requirements,
						'method' => $routeAnnot->method,
						'name'	=>	$routeAnnot->name
					)
				);
			}
		}

		return $routes;
	}

	/* APP */
	public function setApp($app) {
		$this->app = $app;
	}

	public function getApp() {
		return $this->app;
	}

	/* FILTERS */
	public function addFilter($filter) {
		$filter->setController($this);
		if(method_exists($filter, 'before')) 
			$this->hook('before', array($filter, 'before'), $filter->getBeforePriority());
		if(method_exists($filter, 'after'))
			$this->hook('after', array($filter, 'after'), $filter->getAfterPriority());
	}

	/* EXECUTION */
	public static function staticRun($controllerClassName, $actionShortName, \Asgard\Core\App $app, $request=null, $response=null) {
		$controller = new $controllerClassName();
		$controller->setApp($app);
		return $controller->run($actionShortName, $app, $request, $response);
	}

	public function run($actionShortname, $app, $request=null, $response=null) {
		if($request === null)
			$request = new Request;
		if($response === null)
			$response = new Response;
		$this->app = $app;

		$actionName = $actionShortname.'Action';

		$this->request = $request;
		$this->response = $response;

		$app['hooks']->trigger('Asgard.Http.Controller', array($this));

		if(method_exists($this, 'before')) {
			$this->hook('before', function($chain, $this, $request) {
				return call_user_func_array(array($this, 'before'), array($request));
			});
		}
		if(method_exists($this, 'after')) {
			$this->hook('after', function($chain, $this, &$result) {
				return call_user_func_array(array($this, 'after'), array(&$result));
			});
		}

		if(!$result = $this->trigger('before', array($this, $request))) {
			$result = $this->doRun($actionName, array($request));
			$this->trigger('after', array($this, &$result));
		}

		if($result !== null) {
			if(is_string($result))
				return $this->response->setContent($result);
			elseif($result instanceof Response)
				return $result;
			else
				throw new \Exception('Controller response is invalid.');
		}
		else
			return $this->response;
	}

	protected function doRun($method, array $params=array()) {
		$this->_view = null;

		ob_start();
		$result = call_user_func_array(array($this, $method), $params);
		$controllerBuffer =  ob_get_clean();

		if($result !== null)
			return $result;
		if($controllerBuffer)
			return $controllerBuffer;
		elseif($this->_view !== false) {
			if($this->_view instanceof View)
				return $this->_view->render();
			else {
				$method = preg_replace('/Action$/', '', $method);
				if($this->_view === null && !$this->setRelativeView($method.'.php'))
					return null;
				return $this->renderView($this->_view, (array)$this);
			}
		}
		return null;
	}

	/* VIEW */
	public static function widget($class, $method, array $params=array()) {
		$controller = new $class;
		return $controller->doRun($method, $params);
	}
	
	protected function renderView($_view, array $_args=array()) {
		foreach($_args as $_key=>$_value)
			$$_key = $_value;

		ob_start();
		include($_view);
		return ob_get_clean();
	}

	public function noView() {
		$this->_view = false;
	}
	
	public function setView($view) {
		$this->_view = $view;
	}
	
	public function setRelativeView($view) {
		$reflection = new \ReflectionObject($this);
		$dir = dirname($reflection->getFileName());
		if(!file_exists($dir.'/../views/'.strtolower(preg_replace('/Controller$/i', '', static::basename(get_class($this)))).'/'.$view))
			return false;
		$this->setView($dir.'/../views/'.strtolower(preg_replace('/Controller$/i', '', static::basename(get_class($this)))).'/'.$view);
		return true;
	}

	/* UTILS */
	public function getFlash() {
		return new \Asgard\Http\Utils\Flash($this->request);
	}

	public function back() {
		return $this->response->redirect($this->request->server['HTTP_REFERER']);
	}

	public function notFound($msg=null) {
		throw new Exception\NotFoundException($msg);
	}
	
	public function url_for($action, $params=array()) {
		return $this->app['resolver']->url_for(array(get_called_class(), $action), $params);
	}

	private static function basename($ns) {
		return basename(str_replace('\\', DIRECTORY_SEPARATOR, $ns));
	}
}
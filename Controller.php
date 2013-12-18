<?php
namespace Coxis\Core;

class Controller extends Viewable {
	public $request;
	public $response;

	public static function fetchRoutes() {
		$routes = array();
		$class = get_called_class();
		$reflection = new \Addendum\ReflectionAnnotatedClass($class);
		
		if($reflection->getAnnotation('Prefix'))
			$prefix = Resolver::formatRoute($reflection->getAnnotation('Prefix')->value);
		else
			$prefix = '';
		
		$methods = get_class_methods($class);
		foreach($methods as $method) {
			if(!preg_match('/Action$/i', $method))
				continue;
			$method_reflection = new \Addendum\ReflectionAnnotatedMethod($class, $method);
			if($method != 'publishAction' && $method != 'promoteAction' && $method != 'demoteAction')

			if($method_reflection->getAllAnnotations('Route')) {
				foreach($method_reflection->getAllAnnotations('Route') as $annotation) {
					$route = Resolver::formatRoute($prefix.'/'.$annotation->value);

					$routes[] = new ControllerRoute(
						$route,
						$class,
						Resolver::formatActionName($method),
						array(
							'host' => $method_reflection->getAnnotation('Route')->host,
							'requirements' => $method_reflection->getAnnotation('Route')->requirements,
							'method' => $method_reflection->getAnnotation('Route')->method,
							'name'	=>	isset($method_reflection->getAnnotation('Route')->name) ? $method_reflection->getAnnotation('Route')->name:null
						)
					);
				}
			}
		}

		return $routes;
	}
	
	public function getRouteFor($what) {
		foreach(App::get('resolver')->getRoutes() as $route) {
			if($route instanceof ControllerRoute && $route->getController() == $what[0] && $route->getAction() == $what[1])
				return $route->getRoute();
		}
	}

	public function notfound($msg=null) {
		throw new Exception\NotFoundException($msg);
	}

	public function addFilter($filter) {
		$filter->setController($this);
		if(method_exists($filter, 'before')) 
			$this->hook('before', array($filter, 'before'), $filter->getBeforePriority());
		if(method_exists($filter, 'after'))
			$this->hook('after', array($filter, 'after'), $filter->getAfterPriority());
	}
	
	public static function url_for($action, $params=array(), $relative=false) {
		return \URL::url_for(array(get_called_class(), $action), $params, $relative);
	}

	public static function run($controllerClassName, $actionShortname, $request=null, $response=null) {
		if($request === null)
			$request = new Request;
		if($response === null)
			$response = new Response;

		$actionName = $actionShortname.'Action';
		$controller = new $controllerClassName();

		$request->route = array('controller'=>$controllerClassName, 'action'=>$actionShortname);
		$controller->request = $request;
		$controller->response = $response;

		App::get('hook')->trigger('controller_configure', array($controller));

		if(method_exists($controller, 'configure'))
			if($res = $controller->doRun('configure', array($request), false))
				return $res;

		if(method_exists($controller, 'before'))
			$controller->hook('before', function($chain, $controller) {
				return call_user_func_array(array($controller, 'before'), array($chain));
			});
		if(method_exists($controller, 'after'))
			$controller->hook('after', function($chain, $controller, &$result) {
				return call_user_func_array(array($controller, 'after'), array($chain, &$result));
			});

		if(!$result = $controller->trigger('before', array($controller))) {
			$result = $controller->doRun($actionName, array($request));
			$controller->trigger('after', array($controller, &$result));
		}

		if($result !== null) {
			if(is_string($result))
				return $controller->response->setContent($result);
			elseif($result instanceof Response)
				return $result;
			else
				throw new \Exception('Controller response is invalid.');
		}
		else
			return $controller->response;
	}
}
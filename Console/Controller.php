<?php
namespace Asgard\Console;

abstract class Controller {
	public static function fetchRoutes() {
		$routes = array();
		$reflection = new \Addendum\ReflectionAnnotatedClass(get_called_class());

		foreach(get_class_methods(get_called_class()) as $method) {
			if(!preg_match('/Action$/i', $method))
				continue;
			$method_reflection = new \Addendum\ReflectionAnnotatedMethod(get_called_class(), $method);

			if($v = $method_reflection->getAnnotation('Shortcut')) {
				$usage = $description = '';
				if($u = $method_reflection->getAnnotation('Usage'))
					$usage = $u->value;
				if($d = $method_reflection->getAnnotation('Description'))
					$description = $d->value;
				$routes[] = array(
					'shortcut'	=>	$v->value,
					'controller'	=>	get_called_class(),
					'action'	=>	static::formatActionName($method),
					'usage'		=>	$usage,
					'description'		=>	$description,
				);
			}
		}

		return $routes;
	}

	public static function formatActionName($action) {
		return preg_replace('/Action$/i', '', $action);
	}

	public function run($action, $params=array(), $showView=false) {
		$this->view = $action.'.php';
		if(($actionName=$action) != 'configure')
			$actionName = $action.'Action';
		
		if(!method_exists($this, $actionName))
			FrontController::usage();
		$result = $this->$actionName($params);
	}
	
	//OVERRIDE
	public function configure($request){}
}
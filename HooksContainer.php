<?php
namespace Coxis\Hook;

class HooksContainer extends \Coxis\Core\Viewable {
	function __construct() {
		$this->noView();
	}

	public static function fetchHooks() {
		$hooks = array();
		$class = get_called_class();

		$reflection = new \Addendum\ReflectionAnnotatedClass($class);
		
		$methods = get_class_methods($class);
		foreach($methods as $method) {
			$method_reflection = new \Addendum\ReflectionAnnotatedMethod($class, $method);
		
			if($method_reflection->getAnnotation('Hook')) {
				$hook = $method_reflection->getAnnotation('Hook')->value;
				$controller = $class;
				$action = \Coxis\Core\Resolver::formatActionName($method); #todo remove
				$hooks[$hook][] = array($class, $method);
			}
		}

		return $hooks;
	}

	// public static function getHooks($directory = false) {
	// 	$hooks = array();

	// 	$controllers = get_declared_classes();
	// 	$controllers = array_filter($controllers, function($controller) {
	// 		return is_subclass_of($controller, 'Coxis\Hook\HooksContainer');
	// 	});
	// 	foreach($controllers as $classname) {
	// 		$r = new \ReflectionClass($classname);
	// 		if(!$r->isInstantiable())
	// 			continue;
	// 		if($directory) {
	// 			if(strpos($r->getFileName(), realpath($directory)) !== 0)
	// 				continue;
	// 		}

	// 		$hooks = array_merge_recursive($hooks, $classname::fetchHooks());
	// 	}

	// 	return $hooks;
	// }


	public static function run($hook, $args=array()) {
		if(!is_array($args))
			$args = array($args);
		if(is_array($hook)) {
			$hookscontainer = $hook[0];
			$method = $hook[1];
			$hookscontainer = new $hookscontainer;
			return $hookscontainer->doRun($method, $args);
		}
		else
			return call_user_func_array($hook, $args);
	}

	public static function addHooks($hooks) {
		foreach($hooks as $name=>$hooks)
			foreach($hooks as $hook)
				static::addHook($name, $hook);
	}

	public static function addHook($hookName, $hook) {
		\Hook::hookOn($hookName, function($chain, $arg1=null, $arg2=null, $arg3=null, $arg4=null,
			$arg5=null, $arg6=null, $arg7=null, $arg8=null, $arg9=null, $arg10=null) use($hook) {
			$args = array(&$arg1, &$arg2, &$arg3, &$arg4, &$arg5, &$arg6, &$arg7, &$arg8, &$arg9, &$arg10);
			return HooksContainer::run($hook, $args);
		});
	}
}
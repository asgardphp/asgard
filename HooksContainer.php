<?php
namespace Coxis\Hook;

class HooksContainer extends Viewable {
	function __construct() {
		$this->noView();
	}

	public static function fetchHooks() {
		$hooks = array();
		$class = get_called_class();
		$reflection = new \ReflectionAnnotatedClass($class);
		
		$methods = get_class_methods($class);
		foreach($methods as $method) {
			$method_reflection = new \ReflectionAnnotatedMethod($class, $method);
		
			if($method_reflection->getAnnotation('Hook')) {
				$hook = $method_reflection->getAnnotation('Hook')->value;
				$controller = Router::formatControllerName($class);
				$action = Router::formatActionName($method);
				$hooks[$hook][] = array($class, $method);
			}
		}

		return $hooks;
	}

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
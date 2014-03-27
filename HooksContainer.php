<?php
namespace Asgard\Hook;

class HooksContainer {
	public function __construct() {
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
				$hooks[$hook][] = array($class, $method);
			}
		}

		return $hooks;
	}
}
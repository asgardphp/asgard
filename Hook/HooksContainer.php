<?php
namespace Asgard\Hook;

#For doctrine, which does not autoload classes...
require_once __DIR__.'/Annotations/Hook.php';

/**
 * Hooks container.
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
class HooksContainer {
	/**
	 * Retuns the hooks of the current class.
	 * 
	 * @return array
	 * 
	 * @api 
	*/
	public static function fetchHooks() {
		$hooks = [];
		$class = get_called_class();

		$reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
		$reader->addNamespace('Asgard\Hook\Annotations');
		if(\Asgard\Container\Container::instance()['cache']) {
			$reader = new \Doctrine\Common\Annotations\CachedReader(
				$reader,
				\Asgard\Container\Container::instance()['cache'],
				$debug=\Asgard\Container\Container::instance()['config']['debug']
			);
		}

		$reflection = new \ReflectionClass($class);
		foreach($reflection->getMethods() as $method) {
			$hookAnnot = $reader->getMethodAnnotation($method, 'Asgard\Hook\Annotations\Hook');
			if($hookAnnot !== null) {
				$hook = $hookAnnot->value;
				$hooks[$hook][] = [$class, $method->getName()];
			}
		}

		return $hooks;
	}
}
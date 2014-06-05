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
		$hooks = array();
		$class = get_called_class();

		$reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
		$reader->addNamespace('Asgard\Hook\Annotations');
		$reader = new \Doctrine\Common\Annotations\CachedReader(
			$reader,
			// new \Doctrine\Common\Cache\ApcCache(),
			\Asgard\Core\App::instance()['cache'], #todo avec le cache doctrine
			$debug=true
			// \Asgard\Core\App::instance()['config']['debug'] #virer instance()..
		);

		$reflection = new \ReflectionClass($class);
		foreach($reflection->getMethods() as $method) {
			$hookAnnot = $reader->getMethodAnnotation($method, 'Asgard\Hook\Annotations\Hook');
			if($hookAnnot !== null) {
				$hook = $hookAnnot->value;
				$hooks[$hook][] = array($class, $method->getName());
			}
		}

		return $hooks;
	}
}
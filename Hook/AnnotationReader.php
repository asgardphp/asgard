<?php
namespace Asgard\Hook;

#For doctrine, which does not autoload classes...
require_once __DIR__.'/Annotations/Hook.php';

/**
 * Annotations reader.
 * @author Michel Hognerud <michel@hognerud.net>
*/
class AnnotationReader {
	/**
	 * Cache instance.
	 * @var \Doctrine\Common\Cache\Cache
	 */
	protected $cache;
	/**
	 * Activate debugging.
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * Return the hooks of a container.
	 * @param string $class HookContainer class.
	 * @return array
	*/
	public function fetchHooks($class) {
		$hooks = [];

		$reader = new \Doctrine\Common\Annotations\SimpleAnnotationReader();
		$reader->addNamespace('Asgard\Hook\Annotations');
		if($this->cache) {
			$reader = new \Doctrine\Common\Annotations\CachedReader(
				$reader,
				$this->cache,
				$this->debug
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

	/**
	 * Set the cache dependency.
	 * @param \Doctrine\Common\Cache\Cache $cache
	 */
	public function setCache(\Doctrine\Common\Cache\Cache $cache) {
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
<?php
namespace Asgard\Hook;

#For doctrine, which does not autoload classes...
require_once __DIR__.'/Annotations/Hook.php';

/**
 * Annotations reader.
 * @author Michel Hognerud <michel@hognerud.net>
*/
class AnnotationsReader {
	/**
	 * Cache instance.
	 * @var \Doctrine\Cache\Cache
	 */
	protected $cache;
	/**
	 * Activate debugging.
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * Return the hooks of a container.
	 * @param string $class HooksContainer class.
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
	 * @param \Doctrine\Cache\Cache $cache
	 */
	public function setCache(\Doctrine\Cache\Cache $cache) {
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
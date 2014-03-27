<?php
namespace Asgard\Core;

class BundlesManager {
	// protected static $instance = null;
	protected $bundles = array();
	protected $loaded = false;

	// public static function instance() {
	// 	if(!static::$instance)
	// 		static::$instance = new static;
	// 	return static::$instance;
	// }

	public function __construct() {
		$this->bundles[] = new Bundle;
	}

	public function loadEntityFixtures($bundle_path) {
		if(file_exists($bundle_path.'/data')) {
			foreach(glob($bundle_path.'/data/*.entities.yml') as $file)
				\Asgard\Orm\Libs\ORMManager::loadEntityFixtures($file);
		}
	}

	// public function loadEntityFixturesAll() {
	// 	foreach($this->getBundlesPath() as $bundle)
	// 		$this->loadEntityFixtures($bundle);
	// }

	public function addBundles($_bundles) {
		if(!is_array($_bundles))
			$_bundles = array();
		$count = sizeof($_bundles);
		$bundles = array();
		foreach($_bundles as $k=>$v) {
			if($v instanceof BundleLoader) {
				$bundles[] = $v;
				$count--;
			}
			elseif(is_string($v)) {
				$bundle = realpath($v);
				if($bundle === false)
					$bundle = realpath(_DIR_.$v);
				if(!$bundle)
					throw new \Exception('Bundle '.$v.' does not exist.');
				$bundles[$bundle] = null;

				if(file_exists($bundle.'/Bundle.php'))
					require_once $bundle.'/Bundle.php';
			}
			else
				throw new \Exception('Invalid bundle');
		}
		if($count > 0) {
			foreach(get_declared_classes() as $class) {
				if(!is_subclass_of($class, 'Asgard\Core\BundleLoader'))
					continue;
				$reflector = new \Addendum\ReflectionAnnotatedClass($class);
				$dir = dirname($reflector->getFileName());
				if(array_key_exists($dir, $bundles) && $bundles[$dir] === null) {
					unset($bundles[$dir]);
					$bundles[] = new $class;
				}
			}
		}
		foreach($bundles as $bundle=>$obj) {
			if($obj === null) {
				$obj = new \Asgard\Core\BundleLoader;
				$obj->setBundle($bundle);
			}
			foreach($this->bundles as $b) {
				if($b->getBundle() === $obj->getBundle())
					continue 2;
			}
			$this->bundles[] = $obj;
		}
	}

	public function loadBundles($_bundles) {
		if(!$this->loaded) {
			$this->addBundles($_bundles);

			for($i=0; $i < sizeof($this->bundles); $i++)
				$this->bundles[$i]->load($this);

			$this->loaded = true;
		}

		foreach($this->bundles as $b)
			$b->run();
	}

	public function addBundle($bundle) {
		$this->addBundles(array($bundle));
	}
	
	public function getBundles() {
		return $this->bundles;
	}
	
	public function getBundlesPath() {
		$r = array();
		foreach($this->getBundles() as $bundle)
			$r[] = $bundle->getBundle();
		return $r;
	}
}
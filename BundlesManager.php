<?php
namespace Coxis\Core;

class BundlesManager {
	protected static $instance = null;
	protected $bundles = array();

	public static function instance() {
		if(!static::$instance)
			static::$instance = new static;
		return static::$instance;
	}

	public static function loadModelFixtures($bundle_path) {
		if(file_exists($bundle_path.'/data')) {
			foreach(glob($bundle_path.'/data/*.models.yml') as $file)
				ORMManager::loadModelFixtures($file);
		}
	}

	public static function loadModelFixturesAll() {
		foreach(static::inst()->getBundlesPath() as $bundle)
			static::loadModelFixtures($bundle);
	}

	public function loadBundles($bundles) {
		foreach($bundles as $bundle)
			$this->addBundle($bundle);

		$i = 0;
		while($i < sizeof($this->bundles)) {
			$b = $this->bundles[$i++];
			if(is_subclass_of($b, 'Coxis\Core\BundleLoader'))
				$b->load($this);
		}

		foreach($this->bundles as $b)
			$b->run();
	}

	public function addBundle($bundle) {
		if(file_exists($bundle.'/Bundle.php') && ($b=include $bundle.'/Bundle.php') && is_subclass_of($b, 'Coxis\Core\BundleLoader'))
			$this->bundles[] = $b;
		else {
			$b = new \Coxis\Core\BundleLoader;
			$b->setBundle($bundle);
			$this->bundles[] = $b;
		}
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
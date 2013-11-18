<?php
namespace Coxis\Utils;

class Bundle extends BundleLoader {
	public function load($queue) {
		$preload = \Coxis\Utils\Cache::get('bundles/'.$this->getBundle().'/preload', function() {
			$bundle = $this->getBundle();
			$preload = array();
			Autoloader::preloadDir(dirname(__FILE__));
			return $preload;
		});
		Autoloader::addPreloadedClasses($preload);
		parent::load($queue);
	}
}
<?php
namespace Coxis\Core;

class Bundle extends BundleLoader {
	public function load($queue) {
		$preload = \Coxis\Utils\Cache::get('bundles/'.$this->getBundle().'/preload', function() {
			$bundle = $this->getBundle();
			$preload = array();
			Autoloader::preloadDir(dirname(__FILE__));
			Autoloader::preloadDir(_COXIS_DIR_.'/db');
			Autoloader::preloadDir(_COXIS_DIR_.'/auth');
			return $preload;
		});
		Autoloader::addPreloadedClasses($preload);

		\Coxis\Core\Facades::inst()->register('DB', 'Coxis\DB\Facades\DB');
		parent::load($queue);
	}
}
return new Bundle;
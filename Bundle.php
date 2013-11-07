<?php
namespace Coxis\Core;

class Bundle extends BundleLoader {
	public function load($queue) {
		Autoloader::preloadDir(dirname(__FILE__));

		Autoloader::preloadDir(_COXIS_DIR_.'/db');
		Autoloader::preloadDir(_COXIS_DIR_.'/auth');

		\Coxis\Core\Facades::inst()->register('DB', 'Coxis\DB\Facades\DB');
	}
}
return new Bundle;
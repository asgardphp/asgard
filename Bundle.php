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

		\Coxis\Core\Facades::inst()->register('Importer', 'Coxis\Core\Facades\Importer');
		\Coxis\Core\Facades::inst()->register('Hook', '\Coxis\Core\Facades\Hook');
		\Coxis\Core\Facades::inst()->register('Config', 'Coxis\Core\Facades\Config');
		
		\Coxis\Core\Facades::inst()->register('Request', '\Coxis\Core\Facades\Request');
		\Coxis\Core\Facades::inst()->register('Response', '\Coxis\Core\Facades\Response');
		\Coxis\Core\Facades::inst()->register('URL', '\Coxis\Core\Facades\URL');
		\Coxis\Core\Facades::inst()->register('Router', '\Coxis\Core\Facades\Router');
		\Coxis\Core\Facades::inst()->register('Memory', '\Coxis\Core\Facades\Memory');
		\Coxis\Core\Facades::inst()->register('Flash', '\Coxis\Core\Facades\Flash');
		\Coxis\Core\Facades::inst()->register('Validation', '\Coxis\Core\Facades\Validation');
		\Coxis\Core\Facades::inst()->register('ModelsManager', '\Coxis\Core\Facades\ModelsManager');
		\Coxis\Core\Facades::inst()->register('Locale', '\Coxis\Core\Facades\Locale');
		\Coxis\Core\Facades::inst()->register('Session', '\Coxis\Core\Facades\Session');
		\Coxis\Core\Facades::inst()->register('Get', '\Coxis\Core\Facades\Get');
		\Coxis\Core\Facades::inst()->register('Post', '\Coxis\Core\Facades\Post');
		\Coxis\Core\Facades::inst()->register('File', '\Coxis\Core\Facades\File');
		\Coxis\Core\Facades::inst()->register('Cookie', '\Coxis\Core\Facades\Cookie');
		\Coxis\Core\Facades::inst()->register('Server', '\Coxis\Core\Facades\Server');

		\Coxis\Core\Facades::inst()->register('CLIRouter', 'Coxis\Cli\Facades\CLIRouter');
		\Coxis\Core\Facades::inst()->register('HTML', 'Coxis\Utils\Facades\HTML');
		\Coxis\Core\Facades::inst()->register('DB', 'Coxis\DB\Facades\DB');

		parent::load($queue);
	}
}
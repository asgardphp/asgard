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

		App::get('facades')->register('Importer', 'Coxis\Core\Facades\Importer');
		App::get('facades')->register('Hook', '\Coxis\Core\Facades\Hook');
		App::get('facades')->register('Config', 'Coxis\Core\Facades\Config');
		
		App::get('facades')->register('Request', '\Coxis\Core\Facades\Request');
		App::get('facades')->register('Response', '\Coxis\Core\Facades\Response');
		App::get('facades')->register('URL', '\Coxis\Core\Facades\URL');
		App::get('facades')->register('Resolver', '\Coxis\Core\Facades\Resolver');
		App::get('facades')->register('Memory', '\Coxis\Core\Facades\Memory');
		App::get('facades')->register('Flash', '\Coxis\Core\Facades\Flash');
		App::get('facades')->register('Validation', '\Coxis\Core\Facades\Validation');
		App::get('facades')->register('EntitiesManager', '\Coxis\Core\Facades\EntitiesManager');
		App::get('facades')->register('Locale', '\Coxis\Core\Facades\Locale');
		App::get('facades')->register('Session', '\Coxis\Core\Facades\Session');
		App::get('facades')->register('Get', '\Coxis\Core\Facades\Get');
		App::get('facades')->register('Post', '\Coxis\Core\Facades\Post');
		App::get('facades')->register('File', '\Coxis\Core\Facades\File');
		App::get('facades')->register('Cookie', '\Coxis\Core\Facades\Cookie');
		App::get('facades')->register('Server', '\Coxis\Core\Facades\Server');

		App::get('facades')->register('CLIRouter', 'Coxis\Cli\Facades\CLIRouter');
		App::get('facades')->register('HTML', 'Coxis\Utils\Facades\HTML');
		App::get('facades')->register('DB', 'Coxis\DB\Facades\DB');

		parent::load($queue);
	}
}
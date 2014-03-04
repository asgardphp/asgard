<?php
namespace Coxis\Core;

class Bundle extends BundleLoader {
	public function load($queue) {
	/*	$preload = \Coxis\Utils\Cache::get('bundles/'.$this->getBundle().'/preload', function() {
			$bundle = $this->getBundle();
			$preload = array();
			Autoloader::preloadDir(dirname(__FILE__));
			Autoloader::preloadDir(_COXIS_DIR_.'/db');
			Autoloader::preloadDir(_COXIS_DIR_.'/auth');
			return $preload;
		});
		Autoloader::addPreloadedClasses($preload);*/


		Autoloader::preloadDir(dirname(__FILE__));
		Autoloader::preloadDir(_COXIS_DIR_.'/db');
		Autoloader::preloadDir(_COXIS_DIR_.'/auth');


		\Coxis\Core\App::instance()->register('importer', function() { return new \Coxis\Core\Importer; } );
		\Coxis\Core\App::instance()->register('hook', function() { return new \Coxis\Hook\Hook; } );
		\Coxis\Core\App::instance()->register('config', function() { return new \Coxis\Core\Config; } );
		\Coxis\Core\App::instance()->register('request', function() { return \Coxis\Core\Request::createFromGlobals(); } );
		\Coxis\Core\App::instance()->register('response', function() { return new \Coxis\Core\Response; } );
		\Coxis\Core\App::instance()->register('url', function() { return \Coxis\Core\App::instance()->get('request')->url; } );
		\Coxis\Core\App::instance()->register('resolver', function() { return new \Coxis\Core\Resolver; } );
		\Coxis\Core\App::instance()->register('memory', function() { return new \Coxis\Core\Memory; } );
		\Coxis\Core\App::instance()->register('flash', function() { return new \Coxis\Utils\Flash; } );
		\Coxis\Core\App::instance()->register('validation', function() { return new \Coxis\Validation\Validation; } );
		\Coxis\Core\App::instance()->register('entitiesmanager', function() { return new \Coxis\Core\EntitiesManager; } );
		\Coxis\Core\App::instance()->register('locale', function() { return new \Coxis\Utils\Locale; } );
		\Coxis\Core\App::instance()->register('session', function() { return \Coxis\Core\App::instance()->get('request')->session; } );
		\Coxis\Core\App::instance()->register('get', function() { return \Coxis\Core\App::instance()->get('request')->get; } );
		\Coxis\Core\App::instance()->register('post', function() { return \Coxis\Core\App::instance()->get('request')->post; } );
		\Coxis\Core\App::instance()->register('file', function() { return \Coxis\Core\App::instance()->get('request')->file; } );
		\Coxis\Core\App::instance()->register('cookie', function() { return \Coxis\Core\App::instance()->get('request')->cookie; } );
		\Coxis\Core\App::instance()->register('server', function() { return \Coxis\Core\App::instance()->get('request')->server; } );
		\Coxis\Core\App::instance()->register('clirouter', function() { return new \Coxis\Cli\Router; } );
		\Coxis\Core\App::instance()->register('html', function() { return new \Coxis\Utils\HTML; });
		\Coxis\Core\App::instance()->register('db', function() { return new \Coxis\DB\DB(Coxis\Core\App::get('config')->get('database')); } );

		parent::load($queue);
	}
}
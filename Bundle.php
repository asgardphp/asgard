<?php
namespace Asgard\Core;

class Bundle extends BundleLoader {
	public function load($queue) {
	/*	$preload = \Asgard\Utils\Cache::get('bundles/'.$this->getBundle().'/preload', function() {
			$bundle = $this->getBundle();
			$preload = array();
			Autoloader::preloadDir(dirname(__FILE__));
			Autoloader::preloadDir(_ASGARD_DIR_.'/db');
			Autoloader::preloadDir(_ASGARD_DIR_.'/auth');
			return $preload;
		});
		Autoloader::addPreloadedClasses($preload);*/


		Autoloader::preloadDir(dirname(__FILE__));
		Autoloader::preloadDir(_ASGARD_DIR_.'/db');
		Autoloader::preloadDir(_ASGARD_DIR_.'/auth');


		\Asgard\Core\App::instance()->register('importer', function() { return new \Asgard\Core\Importer; } );
		\Asgard\Core\App::instance()->register('hook', function() { return new \Asgard\Hook\Hook; } );
		\Asgard\Core\App::instance()->register('config', function() { return new \Asgard\Core\Config; } );
		\Asgard\Core\App::instance()->register('request', function() { return \Asgard\Core\Request::createFromGlobals(); } );
		\Asgard\Core\App::instance()->register('response', function() { return new \Asgard\Core\Response; } );
		\Asgard\Core\App::instance()->register('url', function() { return \Asgard\Core\App::instance()->get('request')->url; } );
		\Asgard\Core\App::instance()->register('resolver', function() { return new \Asgard\Core\Resolver; } );
		\Asgard\Core\App::instance()->register('memory', function() { return new \Asgard\Core\Memory; } );
		\Asgard\Core\App::instance()->register('flash', function() { return new \Asgard\Utils\Flash; } );
		\Asgard\Core\App::instance()->register('validation', function() { return new \Asgard\Validation\Validation; } );
		\Asgard\Core\App::instance()->register('entitiesmanager', function() { return new \Asgard\Core\EntitiesManager; } );
		\Asgard\Core\App::instance()->register('locale', function() { return new \Asgard\Utils\Locale; } );
		\Asgard\Core\App::instance()->register('session', function() { return \Asgard\Core\App::instance()->get('request')->session; } );
		\Asgard\Core\App::instance()->register('get', function() { return \Asgard\Core\App::instance()->get('request')->get; } );
		\Asgard\Core\App::instance()->register('post', function() { return \Asgard\Core\App::instance()->get('request')->post; } );
		\Asgard\Core\App::instance()->register('file', function() { return \Asgard\Core\App::instance()->get('request')->file; } );
		\Asgard\Core\App::instance()->register('cookie', function() { return \Asgard\Core\App::instance()->get('request')->cookie; } );
		\Asgard\Core\App::instance()->register('server', function() { return \Asgard\Core\App::instance()->get('request')->server; } );
		\Asgard\Core\App::instance()->register('clirouter', function() { return new \Asgard\Cli\Router; } );
		\Asgard\Core\App::instance()->register('html', function() { return new \Asgard\Utils\HTML; });
		\Asgard\Core\App::instance()->register('db', function() { return new \Asgard\DB\DB(Asgard\Core\App::get('config')->get('database')); } );

		parent::load($queue);
	}
}
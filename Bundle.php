<?php
namespace Asgard\Core;

class Bundle extends BundleLoader {
	public function load($queue) {
		$queue->addBundle(new \Asgard\Utils\Bundle);

		if(\Asgard\Core\App::has('autoloader'))
			\Asgard\Core\App::get('autoloader')->preloadDir(__dir__);

		#Entities
		\Asgard\Core\App::instance()->register('entitiesmanager', function() { return new \Asgard\Core\EntitiesManager; } );
		#Http
		\Asgard\Core\App::instance()->register('request', function() { return \Asgard\Core\Request::createFromGlobals(); } );
		\Asgard\Core\App::instance()->register('response', function() { return new \Asgard\Core\Response; } );
		\Asgard\Core\App::instance()->register('url', function() { return \Asgard\Core\App::instance()->get('request')->url; }, 0 );
		\Asgard\Core\App::instance()->register('resolver', function() { return new \Asgard\Core\Resolver; } );
		\Asgard\Core\App::instance()->register('session', function() { return \Asgard\Core\App::instance()->get('request')->session; }, 0 );
		\Asgard\Core\App::instance()->register('get', function() { return \Asgard\Core\App::instance()->get('request')->get; }, 0 );
		\Asgard\Core\App::instance()->register('post', function() { return \Asgard\Core\App::instance()->get('request')->post; }, 0 );
		\Asgard\Core\App::instance()->register('file', function() { return \Asgard\Core\App::instance()->get('request')->file; }, 0 );
		\Asgard\Core\App::instance()->register('cookie', function() { return \Asgard\Core\App::instance()->get('request')->cookie; }, 0 );
		\Asgard\Core\App::instance()->register('server', function() { return \Asgard\Core\App::instance()->get('request')->server; }, 0 );

		#Cli
		\Asgard\Core\App::instance()->register('clirouter', function() { return new \Asgard\Core\Cli\Router; } );

		parent::load($queue);
	}
}
<?php
namespace Asgard\Http;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		\Asgard\Core\App::instance()->register('resolver', function() { return new \Asgard\Http\Resolver; } );
		\Asgard\Core\App::instance()->register('response', function() { return new \Asgard\Http\Response; } );
		\Asgard\Core\App::instance()->register('cookieManager', function() { return new \Asgard\Http\CookieManager; } );

		#Request
		\Asgard\Core\App::instance()->register('request', function() { return \Asgard\Http\Request::createFromGlobals(); } );
		\Asgard\Core\App::instance()->register('url', function() { return \Asgard\Core\App::instance()->get('request')->url; }, 0 );
		\Asgard\Core\App::instance()->register('session', function() { return \Asgard\Core\App::instance()->get('request')->session; }, 0 );
		\Asgard\Core\App::instance()->register('get', function() { return \Asgard\Core\App::instance()->get('request')->get; }, 0 );
		\Asgard\Core\App::instance()->register('post', function() { return \Asgard\Core\App::instance()->get('request')->post; }, 0 );
		\Asgard\Core\App::instance()->register('file', function() { return \Asgard\Core\App::instance()->get('request')->file; }, 0 );
		\Asgard\Core\App::instance()->register('cookie', function() { return \Asgard\Core\App::instance()->get('request')->cookie; }, 0 );
		\Asgard\Core\App::instance()->register('server', function() { return \Asgard\Core\App::instance()->get('request')->server; }, 0 );

		parent::load($bundlesManager);
	}
}
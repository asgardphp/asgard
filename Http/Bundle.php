<?php
namespace Asgard\Http;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$this->app->register('httpKernel', function($app) { return new \Asgard\Http\HttpKernel($app); } );
		$this->app->instance()->register('resolver', function($app) { return new \Asgard\Http\Resolver($app['cache']); } );
		$this->app->instance()->register('response', function() { return new \Asgard\Http\Response; } );
		$this->app->instance()->register('cookieManager', function() { return new \Asgard\Http\CookieManager; } );
		$this->app['request'] = new \Asgard\Http\Request;

		parent::load($bundlesManager);
	}
}
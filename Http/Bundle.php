<?php
namespace Asgard\Http;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		$app->register('httpKernel', function($app) { return new HttpKernel($app); } );
		$app->register('resolver', function($app) {
			$resolver = new Resolver($app['cache']);
			$resolver->setHttpKernel($app['httpKernel']);
			return $resolver;
		});
		$app->register('response', function() { return new Response; } );
		$app->register('cookieManager', function() { return new CookieManager; } );
		$app->register('html', function($app) { return new Utils\HTML($app['request']); });
		$app->register('url', function($app) { return $app['request']->url; });
	}
}
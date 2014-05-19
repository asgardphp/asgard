<?php
namespace Asgard\Cache;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$this->app->register('Asgard\Cache\FileCache', function() {
			return new \Asgard\Cache\FileCache('storage/cache/');
		});
		$this->app->register('Asgard\Cache\APCCache', function($app) {
			return new \Asgard\Cache\APCCache($app['config']->get('key'));
		});
		$this->app->register('Asgard\Cache\NullCache', function() {
			return new \Asgard\Cache\NullCache;
		});
		$this->app->register('cache', function($app) {
			$driver = $app['config']->get('cache_driver');
			if(!$driver)
				$driver = 'Asgard\Cache\NullCache';
			return $app->make($driver);
		});

		parent::load($bundlesManager);
	}
}
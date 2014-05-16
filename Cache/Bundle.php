<?php
namespace Asgard\Cache;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $queue) {
		\Asgard\Core\App::instance()->register('Asgard\Cache\FileCache', function() {
			return new \Asgard\Cache\FileCache('storage/cache/');
		});
		\Asgard\Core\App::instance()->register('Asgard\Cache\APCCache', function() {
			return new \Asgard\Cache\APCCache(\Asgard\Core\App::get('config')->get('key'));
		});
		\Asgard\Core\App::instance()->register('Asgard\Cache\NullCache', function() {
			return new \Asgard\Cache\NullCache;
		});

		parent::load($queue);
	}
}
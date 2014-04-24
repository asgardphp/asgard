<?php
namespace Asgard\Cache;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load($queue) {
		\Asgard\Core\App::instance()->register('Asgard\Cache\FileCache', function() {
			return new \Asgard\Cache\FileCache('storage/cache/');
		});
		\Asgard\Core\App::instance()->register('Asgard\Cache\APCCache', function() {
			return new \Asgard\Cache\FileCache(\Asgard\Core\App::get('key'));
		});

		parent::load($queue);
	}
}
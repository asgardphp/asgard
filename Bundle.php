<?php
namespace Asgard\Utils;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load($queue) {
		if(\Asgard\Core\App::has('autoloader'))
			\Asgard\Core\App::get('autoloader')->preloadDir(__dir__);
		\Asgard\Core\App::instance()->register('flash', function() { return new \Asgard\Utils\Flash; } );
		\Asgard\Core\App::instance()->register('memory', function() { return new \Asgard\Core\Memory; } );
		\Asgard\Core\App::instance()->register('html', function() { return new \Asgard\Utils\HTML; });
		// \Asgard\Core\App::instance()->register('locale', function() { return new \Asgard\Utils\Locale; } );
		
		parent::load($queue);
	}

	public function run() {
		\Asgard\Core\App::get('locale')->importLocales('locales');
	}
}
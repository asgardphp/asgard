<?php
namespace Asgard\Utils;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load($queue) {
		\Asgard\Core\Autoloader::preloadDir(dirname(__FILE__));
		
		parent::load($queue);
	}

	public function run() {
		\Asgard\Core\App::get('locale')->importLocales('locales');
	}
}
<?php
namespace Asgard\Files;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		parent::load($bundlesManager);
	}

	public function run() {
		$this->app['rulesregistry']->registerNamespace('Asgard\Files\Rules');
		
		parent::run();
	}
}
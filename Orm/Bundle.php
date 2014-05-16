<?php
namespace Asgard\Orm;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$bundlesManager->addBundle(new \Asgard\Db\Bundle);
		
		parent::load($bundlesManager);
	}

	public function run() {
		\Asgard\Core\App::get('rulesregistry')->registerNamespace('Asgard\Orm\Validation');
		parent::run();
	}
}
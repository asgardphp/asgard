<?php
namespace Asgard\Core;

class Bundle extends BundleLoader {
	public function load(BundlesManager $bundlesManager) {
		$bundlesManager->addBundle(new \Asgard\Utils\Bundle);

		#Cli
		\Asgard\Core\App::instance()->register('clirouter', function() { return new \Asgard\Core\Cli\Router; } );

		parent::load($bundlesManager);
	}
}
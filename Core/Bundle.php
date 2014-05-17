<?php
namespace Asgard\Core;

class Bundle extends BundleLoader {
	public function load(BundlesManager $bundlesManager) {
		$bundlesManager->addBundle(new \Asgard\Utils\Bundle);

		parent::load($bundlesManager);
	}
}
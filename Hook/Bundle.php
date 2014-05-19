<?php
namespace Asgard\Hook;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$this->app->instance()->register('hook', function($app) { return new \Asgard\Hook\Hook($app); } );
		parent::load($bundlesManager);
	}
}
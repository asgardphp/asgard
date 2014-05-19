<?php
namespace Asgard\Console;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		#Cli
		$this->app->register('clirouter', function() { return new \Asgard\Console\Router; } );

		parent::load($bundlesManager);
	}
}
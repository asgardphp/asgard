<?php
namespace Asgard\Validation;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$this->app->register('validator', function() { return new \Asgard\Validation\Validator; } );
		$this->app->register('rulesregistry', function() { return \Asgard\Validation\RulesRegistry::getInstance(); } );

		parent::load($bundlesManager);
	}
}
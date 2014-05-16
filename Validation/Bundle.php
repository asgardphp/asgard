<?php
namespace Asgard\Validation;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		\Asgard\Core\App::instance()->register('validator', function() { return new \Asgard\Validation\Validator; } );
		\Asgard\Core\App::instance()->register('rulesregistry', function() { return \Asgard\Validation\RulesRegistry::getInstance(); } );

		parent::load($bundlesManager);
	}
}
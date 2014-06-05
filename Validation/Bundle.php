<?php
namespace Asgard\Validation;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		$app->register('validator', function() { return new \Asgard\Validation\Validator; } );
		$app->register('rulesregistry', function() { return \Asgard\Validation\RulesRegistry::getInstance(); } );
	}
}
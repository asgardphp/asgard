<?php
namespace Asgard\Files;

class Bundle extends \Asgard\Core\BundleLoader {
	public function run($app) {
		$app['rulesregistry']->registerNamespace('Asgard\Files\Rules');
	}
}
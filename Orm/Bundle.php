<?php
namespace Asgard\Orm;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		$app->register('migrationsManager', function($app) {
			return new MigrationsManager($app['db'], $app['bundlesManager']);
		});
	}

	public function run($app) {
		parent::run($app);
		$app['rulesregistry']->registerNamespace('Asgard\Orm\Validation');
	}
}
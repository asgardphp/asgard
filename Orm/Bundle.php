<?php
namespace Asgard\Orm;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$bundlesManager->addBundle(new \Asgard\Db\Bundle);
		
		parent::load($bundlesManager);
	}

	public function run() {
		$this->app['rulesregistry']->registerNamespace('Asgard\Orm\Validation');
		$this->app->register('migrationsManager', function($app) {
			return new MigrationsManager($app['db'], $app['bundlesManager']);
		});
		parent::run();
	}
}
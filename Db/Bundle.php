<?php
namespace Asgard\Db;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		$this->app->register('schema', function($app) { return new \Asgard\Db\Schema($app['db']); } );
		$this->app->register('db', function($app) { return new \Asgard\Db\DB($app['config']->get('database')); } );

		parent::load($bundlesManager);
	}
}
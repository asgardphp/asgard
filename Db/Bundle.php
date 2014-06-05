<?php
namespace Asgard\Db;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		$app->register('schema', function($app) { return new \Asgard\Db\Schema($app['db']); } );
		$app->register('db', function($app) { return new \Asgard\Db\DB($app['config']['database']); } );
	}
}
<?php
namespace Asgard\Db;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $queue) {
		\Asgard\Core\App::instance()->register('schema', function() { return new \Asgard\Db\Schema(\Asgard\Core\App::get('db')); } );
		\Asgard\Core\App::instance()->register('db', function() { return new \Asgard\Db\DB(\Asgard\Core\App::get('config')->get('database')); } );

		parent::load($queue);
	}
}
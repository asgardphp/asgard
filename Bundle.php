<?php
namespace Asgard\Data;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $queue) {
		\Asgard\Core\App::instance()->register('data', function() { return new Data(\Asgard\Core\App::get('db')); } );
		parent::load($queue);
	}
}
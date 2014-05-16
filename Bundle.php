<?php
namespace Asgard\Core;

class Bundle extends BundleLoader {
	public function load(BundlesManager $queue) {
		$queue->addBundle(new \Asgard\Utils\Bundle);

		#Cli
		\Asgard\Core\App::instance()->register('clirouter', function() { return new \Asgard\Core\Cli\Router; } );

		parent::load($queue);
	}
}
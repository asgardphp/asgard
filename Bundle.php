<?php
namespace Asgard\Entity;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $queue) {
		#Entities
		\Asgard\Core\App::instance()->register('entitiesmanager', function() { return new \Asgard\Entity\EntitiesManager; } );

		parent::load($queue);
	}
}
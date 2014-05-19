<?php
namespace Asgard\Entity;

class Bundle extends \Asgard\Core\BundleLoader {
	public function load(\Asgard\Core\BundlesManager $bundlesManager) {
		#Entities
		$this->app->register('entitiesmanager', function($app) { return new \Asgard\Entity\EntitiesManager($app); } );
		Entity::setApp($this->app);

		parent::load($bundlesManager);
	}
}
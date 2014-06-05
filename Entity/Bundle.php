<?php
namespace Asgard\Entity;

class Bundle extends \Asgard\Core\BundleLoader {
	public function buildApp($app) {
		$app->register('entitiesmanager', function($app) { return new \Asgard\Entity\EntitiesManager($app); } );
	}

	public function run($app) {
		Entity::setApp($app);
	}
}
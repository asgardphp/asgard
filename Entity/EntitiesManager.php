<?php
namespace Asgard\Entity;

class EntitiesManager {
	protected $entities = [];
	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}

	public function get($entityClass) {
		if(!$this->has($entityClass))
			$this->make($entityClass);
		
		return $this->entities[$entityClass];
	}

	public function has($entityClass) {
		return isset($this->entities[$entityClass]);
	}

	public function make($entityClass) {
		if($this->has($entityClass))
			return $this->entities[$entityClass];
		
		$app = $this->app;
		$definition = $this->app['cache']->fetch('entitiesmanager/'.$entityClass.'/definition', function() use($entityClass, $app) {
			$definition = new EntityDefinition($entityClass, $app);
			return $definition;
		});
		$definition->setApp($this->app);
		$this->entities[$entityClass] = $definition;
		return $definition;
	}
}
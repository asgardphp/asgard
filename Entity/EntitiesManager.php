<?php
namespace Asgard\Entity;

class EntitiesManager {
	protected $entities = [];
	protected $definitions = [];
	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}

	public function addEntity($entityClass) {
		$this->entities[] = $entityClass;

		return $this;
	}

	public function getEntities() {
		return $this->entities;
	}

	public function get($entityClass) {
		if(!$this->has($entityClass))
			$this->make($entityClass);
		
		return $this->definitions[$entityClass];
	}

	public function has($entityClass) {
		return isset($this->definitions[$entityClass]);
	}

	public function make($entityClass) {
		if($this->has($entityClass))
			return $this->definitions[$entityClass];
		
		$app = $this->app;
		$definition = $this->app['cache']->fetch('entitiesmanager/'.$entityClass.'/definition', function() use($entityClass, $app) {
			$definition = new EntityDefinition($entityClass, $app);
			return $definition;
		});
		$definition->setApp($this->app);
		$this->definitions[$entityClass] = $definition;
		return $definition;
	}
}
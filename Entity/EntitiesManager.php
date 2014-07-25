<?php
namespace Asgard\Entity;

class EntitiesManager {
	use \Asgard\Container\ContainerAware;

	protected $entities = [];
	protected $definitions = [];

	public function __construct($container) {
		$this->container = $container;
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
		
		$container = $this->container;
		$definition = $this->container['cache']->fetch('entitiesmanager/'.$entityClass.'/definition', function() use($entityClass, $container) {
			$definition = new EntityDefinition($entityClass, $container);
			return $definition;
		});
		$definition->setContainer($this->container);
		$this->definitions[$entityClass] = $definition;
		return $definition;
	}
}
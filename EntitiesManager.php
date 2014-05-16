<?php
namespace Asgard\Entity;

class EntitiesManager {
	protected $entities = array();

	public function get($entityClass) {
		if(!$this->has($entityClass))
			$this->make($entityClass);
		
		return $this->entities[$entityClass];
	}

	public function has($entityClass) {
		return isset($this->entities[$entityClass]);
	}

	public function make($entityClass) {
		\Asgard\Utils\Profiler::checkpoint('start make');
		if($this->has($entityClass))
			return $this->entities[$entityClass];
		
		$definition = \Asgard\Core\App::get('cache')->get('entitiesmanager/'.$entityClass.'/definition', function() use($entityClass) {
			$definition = new EntityDefinition($entityClass);
			return $definition;
		});
		$this->entities[$entityClass] = $definition;
		\Asgard\Utils\Profiler::checkpoint('end make');
		return $definition;
	}
}

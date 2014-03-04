<?php
namespace Asgard\Core;

class EntitiesManager {
	protected $entities = array();

	public function get($entityClass) {
		if(!isset($this->entities[$entityClass])) {
			$md = \Asgard\Utils\Cache::get('entitiesmanager/'.$entityClass.'/definition', function() use($entityClass) {
				return new EntityDefinition($entityClass);
			});
			$this->entities[$entityClass] = $md;
		}
		
		return $this->entities[$entityClass];
	}
}

<?php
namespace Asgard\Files;

class FilesBehavior extends \Asgard\Entity\Behavior {
	protected $entityClass;

	public function load(\Asgard\Entity\EntityDefinition $definition) {
		$this->entityClass = $entityClass = $definition->getClass();

		$definition->hook('save', function($chain, $entity) {
			foreach($entity->files() as $file)
				$file->save();
		});

		$definition->hook('destroy', function($chain, $entity) {
			foreach($entity->files() as $file)
				$file->delete();
		});
	}

	#$article->hasFile('image')
	public function call_hasFile(\Asgard\Entity\Entity $entity, $file) {
		return $entity::hasProperty($file) && $entity::property($file) instanceof Libs\FileProperty;
	}

	#Article::fileProperties()
	public function static_fileProperties() {
		$entityClass = $this->entityClass;
		$res = array();
		foreach($entityClass::properties() as $name=>$property) {
			if($property instanceof Libs\FileProperty)
				$res[$name] = $property;
		}
		return $res;
	}

	#$article->files()
	public function call_files(\Asgard\Entity\Entity $entity) {
		$res = array();
		foreach($entity->toArrayRaw() as $name=>$value) {
			if($entity->hasFile($name))
				$res[$name] = $value;
		}
		return $res;
	}
}
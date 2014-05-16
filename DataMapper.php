<?php
namespace Asgard\Orm;

class EntityException extends \Exception implements \Asgard\Entity\EntityExceptionInterface {
	protected $errors = array();

	public function __construct($msg, array $errors) {
		parent::__construct($msg);
		$this->errors = $errors;
	}

	public function getErrors() {
		return $this->errors;
	}
}

class DataMapper {
	protected $db;

	public function __construct(\Asgard\Db\DB $db) {
		$this->db = $db;
	}

	public function isNew(\Asgard\Entity\Entity $entity) {
		return $entity->id === null;
	}

	public function isOld(\Asgard\Entity\Entity $entity) {
		return !static::isNew($entity);
	}

	public function load($entityClass, $id) {
		if(!ctype_digit($id) && !is_int($id))
			return;

		$entity = new $entityClass;
		$res = $this->orm($entityClass)->where(array('id' => $id))->getDAL()->first();
		if($res)
			static::unserializeSet($entity, $res);

		if($this->isNew($entity))
			return null;
		return $entity;
	}
	
	public function orm($entityClass) {
		$orm = new ORM($entityClass);
		return $orm;
	}
	
	protected function entityORM(\Asgard\Entity\Entity $entity) {
		if($this->isNew($entity))
			return $this->orm(get_class($entity));
		else
			return $this->orm(get_class($entity))->where(array('id' => $entity->id));
	}
	
	public function getTranslationTable($entityClass) {
		return $this->getTable($entityClass).'_translation';
	}

	public function getTable($entityClass) {
		if(isset($entityClass::getDefinition()->table) && $entityClass::getDefinition()->table)
			return \Asgard\Core\App::get('config')->get('database/prefix').$entityClass::getDefinition()->table;
		else
			return \Asgard\Core\App::get('config')->get('database/prefix').$entityClass::getShortName();
	}
	
	public function destroyAll($entityClass) {
		foreach($this->all($entityClass) as $entity)
			$this->destroy($entity);
	}
	
	public function destroyOne($entityClass, $id) {
		if($entity = $this->load($entityClass, $id)) {
			$this->destroy($entity);
			return true;
		}
		return false;
	}
	
	public function getI18N(\Asgard\Entity\Entity $entity, $name, $lang=null) {
		if(!$entity::hasProperty($name))
			return;
		if($entity::property($name)->i18n) {
			if(isset($entity->data['properties'][$name][$lang]))
				return $entity->data['properties'][$name][$lang];
			$dal = new \Asgard\Db\DAL($this->db, static::getTranslationTable($entity));
			$res = $dal->where(array('id' => $entity->id))->where(array('locale'=>$lang))->first();
			if(!$res)
				return;
			unset($res['id']);
			unset($res['locale']);

			static::unserializeSet($entity, $res, $lang);
				
			if(isset($entity->data['properties'][$name][$lang]))
				return $entity->data['properties'][$name][$lang];
		}
	}

	protected function getRelation(\Asgard\Entity\Entity $entity, $name) {
		return $entity::getDefinition()->relation($name);
	}

	public function relation(\Asgard\Entity\Entity $entity, $name) {
		$rel = $this->getRelation($entity, $name);
		$relation_type = $rel->type();
		$relEntity = $rel['entity'];
		
		switch($relation_type) {
			case 'hasOne':
			case 'belongsTo':
				$link = $rel->getLink();
				if($rel['polymorphic']) {
					$relEntity = $entity->get($rel['link_type']);
					if(!$relEntity)
						return;
				}
				return $relEntity::where(array('id' => $entity->get($link)));
			case 'hasMany':
			case 'HMABT':
				return new \Asgard\Orm\CollectionORM($entity, $name);
			default:	
				throw new \Exception('Relation '.$relation_type.' does not exist.');
		}
	}

	protected static function unserializeSet(\Asgard\Entity\Entity $entity, array $data, $lang=null) {
		foreach($data as $k=>$v) {
			if($entity::hasProperty($k))
				$data[$k] = $entity->property($k)->unserialize($v, $entity);
			else
				unset($data[$k]);
		}

		return $entity->_set($data, $lang);
	}

	public function destroy(\Asgard\Entity\Entity $entity) {
		$orms = array();
		foreach($entity::getDefinition()->relations() as $name=>$relation) {
			if(isset($relation['cascade']['delete']) && $relation['cascade']['delete']) {
				$orm = $entity->$name();
				if(!is_object($orm))
					continue;
				$orm->getDAL()->rsc();
				$orms[] = $orm;
			}
		}

		if($entity::isI18N())
			$r = static::entityORM($entity)->getDAL()->delete(array($this->getTable($entity), $this->getTranslationTable($entity)));
		else
			$r = static::entityORM($entity)->getDAL()->delete();

		$entity::trigger('destroy', array($entity));

		foreach($orms as $orm)
			$orm->delete();

		return $r;
	}

	public function create($entityClass, $values=null, $force=false) {
		$m = new $entityClass;
		return $m->save($values, $force);
	}
	
	public function valid(\Asgard\Entity\Entity $entity) {
		$data = $entity->toArrayRaw();
		$validator = $entity->getValidator();
		foreach($entity->getDefinition()->relations() as $name=>$relation) {
			$data[$name] = $entity->relation($name);
			$validator->attribute($name, $relation->getRules());
		}
		return $this->trigger('validation', array($entity, $validator, &$data), function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}

	public function errors(\Asgard\Entity\Entity $entity) {
		$data = $entity->toArrayRaw();
		$validator = $entity->getValidator();
		foreach($entity->getDefinition()->relations() as $name=>$relation) {
			$data[$name] = $entity->relation($name);
			$validator->attribute($name, $relation->getRules());
		}
		$errors = $entity::trigger('validation', array($entity, $validator, &$data), function($chain, $entity, $validator, &$data) {
			return $validator->errors($data);
		});

		$e = array();
		foreach($data as $property=>$value) {
			if($propertyErrors = $errors->attribute($property)->errors())
				$e[$property] = $propertyErrors;
		}

		return $e;
	}

	public function save(\Asgard\Entity\Entity $entity, $values=null, $force=false) {
		#set $values if any
		if($values)
			$entity->set($values);
		
		if(!$force && $errors = $this->errors($entity)) {
			$msg = implode("\n", \Asgard\Utils\Tools::flateArray($errors));
			throw new EntityException($msg, $errors);
		}

		$entity::trigger('save', array($entity));

		#apply filters before saving
		foreach($entity->propertyNames() as $name) {
			if(isset($entity->data['properties'][$name]))
				$value = $entity->data['properties'][$name];
			else
				$value = null;
			
			if($entity::property($name)->i18n) {
				foreach($value as $k=>$v)
					$vars[$name][$k] = $entity::property($name)->serialize($v);
			}
			else
				$vars[$name] = $entity::property($name)->serialize($value);
		}

		//Persist local id field
		foreach($entity::getDefinition()->relations as $relation => $params) {
			if(!isset($entity->data[$relation]))
				continue;
			$rel = $entity::getDefinition()->relations[$relation];
			$type = $rel['type'];
			if($type == 'belongsTo' || $type == 'hasOne') {
				$link = $rel->getLink();
				if(is_object($entity->data[$relation]))
					$vars[$link] = $entity->data[$relation]->id;
				else
					$vars[$link] = $entity->data[$relation];
			}
		}
		
		//Persist i18n
		$values = array();
		$i18n = array();
		foreach($vars as $p => $v) {
			if($entity::property($p)->i18n)
				foreach($v as $lang=>$lang_value)
					$i18n[$lang][$p] = $lang_value;
			else
				$values[$p] = $v;
		}

		//Persist
		$orm = $this->orm(get_class($entity));
		//new
		if(!isset($entity->id) || !$entity->id)
			$entity->id = $orm->getDAL()->insert($values);
		//existing
		elseif(count($vars) > 0) {
			if(!$orm->reset()->where(array('id'=>$entity->id))->getDAL()->update($values))
				$entity->id = $orm->getDAL()->insert($values);
		}		
		
		//Persist i18n
		foreach($i18n as $lang=>$values) {
			$dal = new \Asgard\Db\DAL($this->db, static::getTranslationTable($entity));
			if(!$dal->where(array('id'=>$entity->id, 'locale'=>$lang))->update($values))
				$dal->insert(
					array_merge(
						$values, 
						array(
							'locale'=>$lang,
							'id'=>$entity->id,
						)
					)
				);
		}
	
		//Persist relations
		foreach($entity::getDefinition()->relations as $relation => $params) {
			if(!isset($entity->data[$relation]))
				continue;
			$rel = static::getRelation($entity, $relation);
			$reverse_rel = $rel->reverse();
			$type = $rel['type'];

			if($type == 'hasOne') {
				$relation_entity = $rel['entity'];
				$link = $reverse_rel->getLink();
				$relation_entity::where(array($link => $entity->id))->getDAL()->update(array($link => 0));
				$relation_entity::where(array('id' => $entity->data[$relation]))->getDAL()->update(array($link => $entity->id));
			}
			elseif($type == 'hasMany' || $type == 'HMABT')
				$entity->$relation()->sync($entity->data[$relation]);
		}

		return $entity;
	}
}
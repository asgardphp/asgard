<?php
namespace Asgard\Orm;

class EntityException extends \Exception implements \Asgard\Entity\EntityExceptionInterface {
	protected $errors = [];

	public function __construct($msg, array $errors) {
		parent::__construct($msg);
		$this->errors = $errors;
	}

	public function getErrors() {
		return $this->errors;
	}
}

class DataMapper {
	use \Asgard\Container\ContainerAwareTrait;

	protected $db;
	protected $locale;
	protected $prefix;

	public function __construct(\Asgard\Db\DB $db, $locale=null, $prefix=null, $container=null) {
		$this->db = $db;
		$this->locale = $locale;
		$this->prefix = $prefix;
		$this->container = $container;
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
		$res = $this->orm($entityClass)->where(['id' => $id])->getDAL()->first();
		if($res)
			$entity->_set(static::unserialize($entity, $res));

		if($this->isNew($entity))
			return null;
		return $entity;
	}
	
	public function orm($entityClass) {
		$orm = new ORM($entityClass, $this->db, $this->locale, $this->prefix, $this->container, $this);
		return $orm;
	}
	
	protected function entityORM(\Asgard\Entity\Entity $entity) {
		if($this->isNew($entity))
			return $this->orm(get_class($entity));
		else
			return $this->orm(get_class($entity))->where(['id' => $entity->id]);
	}
	
	public function getTranslationTable($entityClass) {
		return $this->getTable($entityClass).'_translation';
	}

	public function getTable($entityClass) {
		if(isset($entityClass::getStaticDefinition()->table) && $entityClass::getStaticDefinition()->table)
			return $this->prefix.$entityClass::getStaticDefinition()->table;
		else
			return $this->prefix.$entityClass::getShortName();
	}

	public function all($entityClass) {
		return $this->orm($entityClass)->all();
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
	
	public function getI18N(\Asgard\Entity\Entity $entity, $locale=null) {
		$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable($entity));
		$res = $dal->where(['id' => $entity->id])->where(['locale'=>$locale])->first();
		if(!$res)
			return;
		unset($res['id']);
		unset($res['locale']);

		return static::unserialize($entity, $res);
	}

	public function getRelation(\Asgard\Entity\EntityDefinition $definition, $name) {
		return $definition->relation($name);
	}

	public function relation(\Asgard\Entity\Entity $entity, $name) {
		$rel = $this->getRelation($entity::getStaticDefinition(), $name);
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
				return $relEntity::where(['id' => $entity->get($link)]);
			case 'hasMany':
			case 'HMABT':
				return new \Asgard\Orm\CollectionORM($entity, $name, $this->db, $this->locale, $this->prefix, $this->container, $this);
			default:	
				throw new \Exception('Relation '.$relation_type.' does not exist.');
		}
	}

	protected static function unserialize(\Asgard\Entity\Entity $entity, array $data) {
		foreach($data as $k=>$v) {
			if($entity::getStaticDefinition()->hasProperty($k))
				$data[$k] = $entity::getStaticDefinition()->property($k)->unserialize($v, $entity, $k);
			else
				unset($data[$k]);
		}

		return $data;
	}

	public function destroy(\Asgard\Entity\Entity $entity) {
		return $entity::trigger('destroy', [$entity], function($chain, $entity) {
			$orms = [];

			foreach($entity::getStaticDefinition()->relations() as $name=>$relation) {
				if(isset($relation['cascade']['delete']) && $relation['cascade']['delete']) {
					$orm = $entity->$name();
					if(!is_object($orm))
						continue;
					$orm->getDAL()->rsc();
					$orms[] = $orm;
				}
			}

			if($entity::isI18N())
				$r = static::entityORM($entity)->getDAL()->delete([$this->getTable($entity), $this->getTranslationTable($entity)]);
			else
				$r = static::entityORM($entity)->getDAL()->delete();

			#Files
			foreach($entity::getStaticDefinition()->properties() as $name=>$prop) {
				if($prop instanceof \Asgard\Entity\Properties\FileProperty) {
					if($prop->get('multiple')) {
						foreach($entity->get($name) as $file)
							$file->delete();
					}
					elseif($file = $entity->get($name))
						$file->delete();
				}
			}

			foreach($orms as $orm)
				$orm->delete();

			return $r;
		});
	}

	public function create($entityClass, $values=null, $force=false) {
		$m = new $entityClass;
		return $m->save($values, $force);
	}
	
	public function valid(\Asgard\Entity\Entity $entity) {
		$data = $entity->toArrayRaw();
		$validator = $entity->getValidator();
		foreach($entity->getDefinition()->relations() as $name=>$relation) {
			$data[$name] = $entity::getStaticDefinition()->relation($name);
			$validator->attribute($name, $relation->getRules());
		}
		return $entity::getStaticDefinition()->trigger('validation', [$entity, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}

	public function errors(\Asgard\Entity\Entity $entity) {
		$data = $entity->toArrayRaw();
		$validator = $entity->getValidator();
		foreach($entity->getDefinition()->relations() as $name=>$relation) {
			$data[$name] = $entity::getStaticDefinition()->relation($name);
			$validator->attribute($name, $relation->getRules());
		}
		$errors = $entity::trigger('validation', [$entity, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->errors($data);
		});

		$e = [];
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
			$msg = implode("\n", \Asgard\Common\ArrayUtils::flateArray($errors));
			throw new EntityException($msg, $errors);
		}

		$entity::trigger('save', [$entity]);

		#Files
		foreach($entity::getStaticDefinition()->properties() as $name=>$prop) {
			if($prop instanceof \Asgard\Entity\Properties\FileProperty) {
				if($prop->get('multiple')) {
					$files = $entity->$name = array_values($entity->$name->all());
					foreach($files as $k=>$file) {
						if($file->shouldDelete()) {
							$file->delete();
							unset($files[$k]);
						}
						else
							$file->save();
					}
				}
				elseif($file = $entity->get($name)) {
					if($file->shouldDelete())
						$file->delete();
					else
						$file->save();
				}
			}
		}

		$vars = [];
		#process data
		foreach($entity::getStaticDefinition()->propertyNames() as $name) {
			
			if($entity::property($name)->i18n) {
				$value = $entity->get($name, $entity->getLocales());
				foreach($value as $k=>$v)
					$vars[$name][$k] = $entity::property($name)->serialize($v);
			}
			else {
				$value = $entity->get($name);
				$vars[$name] = $entity::property($name)->serialize($value);
			}
		}

		#persist entity ids
		foreach($entity::getStaticDefinition()->relations as $relation => $params) {
			if(!isset($entity->data[$relation]))
				continue;
			$rel = $entity::getStaticDefinition()->relations[$relation];
			$type = $rel['type'];
			if($type == 'belongsTo' || $type == 'hasOne') {
				$link = $rel->getLink();
				if(is_object($entity->data[$relation]))
					$vars[$link] = $entity->data[$relation]->id;
				else
					$vars[$link] = $entity->data[$relation];
			}
		}
		
		#persist i18n
		$values = [];
		$i18n = [];
		foreach($vars as $p => $v) {
			if($entity::property($p)->i18n) {
				foreach($v as $locale=>$locale_value)
					$i18n[$locale][$p] = $locale_value;
			}
			else
				$values[$p] = $v;
		}

		#Persist
		$orm = $this->orm(get_class($entity));
		#new
		if(!isset($entity->id) || !$entity->id)
			$entity->id = $orm->getDAL()->insert($values);
		#existing
		elseif(count($vars) > 0) {
			if(!$orm->reset()->where(['id'=>$entity->id])->getDAL()->update($values))
				$entity->id = $orm->getDAL()->insert($values);
		}		
		
		#Persist i18n
		foreach($i18n as $locale=>$values) {
			$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable($entity));
			if(!$dal->where(['id'=>$entity->id, 'locale'=>$locale])->update($values)) {
				$dal->insert(
					array_merge(
						$values, 
						[
							'locale'=>$locale,
							'id'=>$entity->id,
						]
					)
				);
			}
		}
	
		#Persist relations
		foreach($entity::getStaticDefinition()->relations as $relation => $params) {
			if(!isset($entity->data[$relation]))
				continue;
			$rel = static::getRelation($entity::getStaticDefinition(), $relation);
			$reverse_rel = $rel->reverse();
			$type = $rel['type'];

			if($type == 'hasOne') {
				$relation_entity = $rel['entity'];
				$link = $reverse_rel->getLink();
				$relation_entity::where([$link => $entity->id])->getDAL()->update([$link => 0]);
				$relation_entity::where(['id' => $entity->data[$relation]])->getDAL()->update([$link => $entity->id]);
			}
			elseif($type == 'hasMany' || $type == 'HMABT')
				$entity->$relation()->sync($entity->data[$relation]);
		}

		return $entity;
	}
}
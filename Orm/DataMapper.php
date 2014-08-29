<?php
namespace Asgard\Orm;

/**
 * Handle database storage of entities.
 */
class DataMapper {

	/**
	 * Database access.
	 * @var \Asgard\Db\DB
	 */
	protected $db;
	/**
	 * Default locale.
	 * @var string
	 */
	protected $locale;
	/**
	 * Tables prefix.
	 * @var string
	 */
	protected $prefix;
	/**
	 * ORM Factory.
	 * @var \Asgard\Container\Factory
	 */
	protected $ormFactory;
	/**
	 * CollectionORM Factory.
	 * @var \Asgard\Container\Factory
	 */
	protected $collectionOrmFactory;

	/**
	 * Constructor.
	 * @param \Asgard\Db\DB $db
	 * @param string                    $locale    Default locale.
	 * @param string                    $prefix    Tables prefix.
	 * @param \Asgard\Container\Factory $ormFactory
	 * @param \Asgard\Container\Factory $collectionOrmFactory
	 */
	public function __construct(\Asgard\Db\DB $db, $locale='en', $prefix=null, \Asgard\Container\Factory $ormFactory=null, \Asgard\Container\Factory $collectionOrmFactory=null) {
		$this->db                   = $db;
		$this->locale               = $locale;
		$this->prefix               = $prefix;
		$this->ormFactory           = $ormFactory;
		$this->collectionOrmFactory = $collectionOrmFactory;
	}

	/**
	 * Check if the entity is not stored.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return boolean                       true if entity not stored, false otherwise
	 */
	public function isNew(\Asgard\Entity\Entity $entity) {
		return $entity->id === null;
	}

	/**
	 * Check if the entity is stored.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return boolean                       true if entity stored, false otherwisedescription]
	 */
	public function isOld(\Asgard\Entity\Entity $entity) {
		return !static::isNew($entity);
	}

	/**
	 * Load an entity from database.
	 * @param  string                $entityClass entity class
	 * @param  integer               $id          entity id
	 * @return \Asgard\Entity\Entity
	 */
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
	
	/**
	 * Create an ORM instance.
	 * @param  string          $entityClass 
	 * @return \Asgard\Orm\ORM
	 */
	public function orm($entityClass) {
		if($this->ormFactory)
			return $this->ormFactory->create([$entityClass, $this->locale, $this->prefix, $this]);
		else
			return new ORM($entityClass, $this->locale, $this->prefix, $this);
	}
	
	/**
	 * Create an ORM instance for a specific entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return \Asgard\Orm\ORM
	 */
	protected function entityORM(\Asgard\Entity\Entity $entity) {
		if($this->isNew($entity))
			return $this->orm(get_class($entity));
		else
			return $this->orm(get_class($entity))->where(['id' => $entity->id]);
	}
	
	/**
	 * Get the translations table of an entity class.
	 * @param  string $entityClass
	 * @return string
	 */
	public function getTranslationTable($entityClass) {
		return $this->getTable($entityClass).'_translation';
	}

	/**
	 * Get the table of an entity class.
	 * @param  string $entityClass
	 * @return string
	 */
	public function getTable($entityClass) {
		if(isset($entityClass::getStaticDefinition()->table) && $entityClass::getStaticDefinition()->table)
			return $this->prefix.$entityClass::getStaticDefinition()->table;
		else
			return $this->prefix.$entityClass::getShortName();
	}

	/**
	 * Return all entities of a class.
	 * @param  string $entityClass
	 * @return array
	 */
	public function all($entityClass) {
		return $this->orm($entityClass)->all();
	}
	
	/**
	 * Destroy all entities of a clas.
	 * @param  string $entityClass 
	 * @return DataMapper $this
	 */
	public function destroyAll($entityClass) {
		foreach($this->all($entityClass) as $entity)
			$this->destroy($entity);
		return $this;
	}
	
	/**
	 * Destroy a specific entity.
	 * @param  string  $entityClass entity class
	 * @param  integer $id          entity id
	 * @return boolean true if success, false otherwise
	 */
	public function destroyOne($entityClass, $id) {
		if($entity = $this->load($entityClass, $id)) {
			$this->destroy($entity);
			return true;
		}
		return false;
	}
	
	/**
	 * Return an entity with translations.
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  string                $locale
	 * @return \Asgard\Entity\Entity
	 */
	public function getI18N(\Asgard\Entity\Entity $entity, $locale=null) {
		$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable($entity));
		$res = $dal->where(['id' => $entity->id, 'locale'=>$locale])->first();
		if(!$res)
			return;
		unset($res['id']);
		unset($res['locale']);

		return static::unserialize($entity, $res);
	}

	/**
	 * Get a relation object.
	 * @param  \Asgard\Entity\EntityDefinition $definition
	 * @param  string                          $name       relation name
	 * @return \Asgard\Entity\
	 */
	public function getRelation(\Asgard\Entity\EntityDefinition $definition, $name) {
		return $definition->relation($name);
	}

	/**
	 * Return the related entities of an entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  string             $name   relation name
	 * @return \Asgrd\Entity\Entity|CollectionORM
	 */
	public function relation(\Asgard\Entity\Entity $entity, $name) {
		$rel = $this->getRelation($entity->getDefinition(), $name);
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
				if($this->collectionOrmFactory)
					return $this->collectionOrmFactory->create([$entity, $name, $this->locale, $this->prefix, $this]);
				else
					return new CollectionORM($entity, $name, $this->locale, $this->prefix, $this);
			default:	
				throw new \Exception('Relation '.$relation_type.' does not exist.');
		}
	}

	/**
	 * Unserialize data of an entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  array                 $data
	 * @return array                 unserialized data
	 */
	protected static function unserialize(\Asgard\Entity\Entity $entity, array $data) {
		foreach($data as $k=>$v) {
			if($entity->getDefinition()->hasProperty($k))
				$data[$k] = $entity->getDefinition()->property($k)->unserialize($v, $entity, $k);
			else
				unset($data[$k]);
		}

		return $data;
	}

	/**
	 * Destroy an entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return true for success, otherwise false
	 */
	public function destroy(\Asgard\Entity\Entity $entity) {
		return $entity::trigger('destroy', [$entity], function($chain, $entity) {
			$orms = [];

			foreach($entity->getDefinition()->relations() as $name=>$relation) {
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
			foreach($entity->getefinition()->properties() as $name=>$prop) {
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

	/**
	 * Create and store an entity.
	 * @param  string  $entityClass 
	 * @param  array   $values        default entity attributes
	 * @param  boolean $force         skip validation
	 * @return \Asgard\Entity\Entity
	 */
	public function create($entityClass, $values=null, $force=false) {
		$m = new $entityClass;
		return $m->save($values, $force);
	}
	
	/**
	 * Validate an entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return true for valid, otherwise false
	 */
	public function valid(\Asgard\Entity\Entity $entity) {
		$data = $entity->toArrayRaw();
		$validator = $entity->getValidator();
		foreach($entity->getDefinition()->relations() as $name=>$relation) {
			$data[$name] = $entity->getDefinition()->relation($name);
			$validator->attribute($name, $relation->getRules());
		}
		return $entity->getDefinition()->trigger('validation', [$entity, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}

	/**
	 * Return entity errors.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return array
	 */
	public function errors(\Asgard\Entity\Entity $entity) {
		$data = $entity->toArrayRaw();
		$validator = $entity->getValidator();
		foreach($entity->getDefinition()->relations() as $name=>$relation) {
			$data[$name] = $entity->getDefinition()->relation($name);
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

	/**
	 * Store an entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  array                 $values entity attributes
	 * @param  boolean               $force  skip validation
	 * @return true for successful storage, false otherwise
	 */
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
		foreach($entity->getDefinition()->properties() as $name=>$prop) {
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
		foreach($entity->getDefinition()->propertyNames() as $name) {
			
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
		foreach($entity->getDefinition()->relations as $relation => $params) {
			if(!isset($entity->data[$relation]))
				continue;
			$rel = $entity->getDefinition()->relations[$relation];
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
		foreach($entity->getDefinition()->relations as $relation => $params) {
			if(!isset($entity->data[$relation]))
				continue;
			$rel = static::getRelation($entity->getDefinition(), $relation);
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

	/**
	 * Return the database instance.
	 * @return \Asgard\Db\DB
	 */
	public function getDB() {
		return $this->db;
	}
}
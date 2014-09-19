<?php
namespace Asgard\Orm;

/**
 * Handle database storage of entities.
 */
class DataMapper {

	/**
	 * Entities Manager.
	 * @var \Asgard\Entity\EntitiesManager
	 */
	protected $entitiesManager;
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
	public function __construct(\Asgard\Entity\EntitiesManager $entitiesManager, \Asgard\Db\DB $db, $locale='en', $prefix=null, \Asgard\Container\Factory $ormFactory=null, \Asgard\Container\Factory $collectionOrmFactory=null) {
		$this->entitiesManager      = $entitiesManager;
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
		return !$this->isNew($entity);
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
		$definition = $this->entitiesManager->get($entityClass);
		if($this->ormFactory)
			return $this->ormFactory->create([$definition, $this->locale, $this->prefix, $this]);
		else
			return new ORM($definition, $this->locale, $this->prefix, $this);
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
		$definition = $this->entitiesManager->get($entityClass);
		if(isset($definition->table) && $definition->table)
			return $this->prefix.$definition->table;
		else
			return $this->prefix.$definition->getShortName();
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
		$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable(get_class($entity)));
		$res = $dal->where(['id' => $entity->id, 'locale'=>$locale])->first();
		if(!$res)
			return;
		unset($res['id']);
		unset($res['locale']);

		return static::unserialize($entity, $res);
	}

	/**
	 * Return the entity relations objects.
	 * @param  \Asgard\Entity\EntityDefinition $definition
	 * @return array
	 */
	public function relations(\Asgard\Entity\EntityDefinition $definition) {
		if($relations = $definition->get('relations'))
			return $relations;
		else {
			$relations = [];
			foreach($definition->properties() as $name=>$prop) {
				if($prop->get('type') == 'entity')
					$relations[$name] = new EntityRelation($definition, $this, $name, $prop->params);
			}
			$definition->set('relations', $relations);
			return $relations;
		}
	}

	/**
	 * Get a relation object.
	 * @param  \Asgard\Entity\EntityDefinition entityClass
	 * @param  string                          $name       relation name
	 * @return EntityRelation
	 */
	public function getRelation(\Asgard\Entity\EntityDefinition $definition, $name) {
		return $this->relations($definition)[$name];
	}

	/**
	 * Return the related entities of an entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  string                $name   relation name
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
				return $this->orm($relEntity)->where(['id' => $entity->get($link)]);
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

	public function hasRelation(\Asgard\Entity\EntityDefinition $definition, $name) {
		return isset($this->relations($definition)[$name]);
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
			// else
			// 	unset($data[$k]);
		}

		return $data;
	}

	/**
	 * Destroy an entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return true for success, otherwise false
	 */
	public function destroy(\Asgard\Entity\Entity $entity) {
		return $entity->getDefinition()->trigger('destroy', [$entity], function($chain, $entity) {
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

			if($entity->getDefinition()->isI18N())
				$r = $this->entityORM($entity)->getDAL()->delete([$this->getTable($entity), $this->getTranslationTable($entity)]);
			else
				$r = $this->entityORM($entity)->getDAL()->delete();

			#Files
			foreach($entity->getDefinition()->properties() as $name=>$prop) {
				if($prop instanceof \Asgard\Entity\Properties\FileProperty) {
					if($prop->get('many')) {
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
		$m = $this->entitiesManager->get($entityClass)->make();
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
		foreach($this->relations($entity->getDefinition()) as $name=>$relation) {
			$data[$name] = $this->relation($entity, $name);
			$validator->attribute($name, $relation->getRules());
		}
		$errors = $entity->getDefinition()->trigger('validation', [$entity, $validator, &$data], function($chain, $entity, $validator, &$data) {
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

		$entity->getDefinition()->trigger('save', [$entity]);

		#Files
		foreach($entity->getDefinition()->properties() as $name=>$prop) {
			if($prop instanceof \Asgard\Entity\Properties\FileProperty) {
				if($prop->get('many')) {
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
		$i18n = [];
		#process data
		foreach($entity->getDefinition()->properties() as $name=>$prop) {
			#i18n properties
			if($prop->i18n) {
				$value = $entity->get($name, $entity->getLocales());
				foreach($value as $locale=>$v)
					$i18n[$locale][$name] = $entity->getDefinition()->property($name)->serialize($v);
			}
			#relations with a single entity
			elseif($prop->get('type') == 'entity') {
				$rel = $this->getRelation($entity->getDefinition(), $name);
				$type = $rel->type();
				if($type == 'belongsTo' || $type == 'hasOne') {
					$link = $rel->getLink();
					$relatedEntity = $entity->data['properties'][$name];
					if(is_object($relatedEntity)) {
						if($this->isNew($relatedEntity))
							$this->save($relatedEntity, null, $force);
						$vars[$link] = $relatedEntity->id;
					}
					else
						$vars[$link] = $relatedEntity;
				}
			}
			#other properties
			else {
				$value = $entity->get($name);
				$vars[$name] = $prop->serialize($value);
			}
		}

		#Persist
		$orm = $this->orm(get_class($entity));
		#new
		if(!isset($entity->id) || !$entity->id)
			$entity->id = $orm->getDAL()->insert($vars);
		#existing
		elseif(count($vars) > 0) {
			if(!$orm->reset()->where(['id'=>$entity->id])->getDAL()->update($vars))
				$entity->id = $orm->getDAL()->insert($vars);
		}		
		
		#Persist i18n
		foreach($i18n as $locale=>$values) {
			$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable(get_class($entity)));
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
		foreach($this->relations($entity->getDefinition()) as $relation => $params) {
			if(!isset($entity->data['properties'][$relation]))
				continue;
			$rel = $this->getRelation($entity->getDefinition(), $relation);
			$reverse_rel = $rel->reverse();
			$type = $rel->type();

			if($type == 'hasOne') {
				$relation_entity = $rel['entity'];
				$link = $reverse_rel->getLink();
				$this->orm($relation_entity)->where([$link => $entity->id])->getDAL()->update([$link => 0]);
				$this->orm($relation_entity)->where(['id' => $entity->data[$relation]->id])->getDAL()->update([$link => $entity->id]);
			}
			elseif($rel['many'])
				$this->relation($entity, $relation)->sync($entity->data['properties'][$relation]->all());
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
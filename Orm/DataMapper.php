<?php
namespace Asgard\Orm;

/**
 * Handle database storage of entities.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DataMapper implements DataMapperInterface {
	/**
	 * Entities Manager.
	 * @var \Asgard\Entity\EntityManagerInterface
	 */
	protected $entityManager;
	/**
	 * Database access.
	 * @var \Asgard\Db\DBInterface
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
	 * @var ORMFactoryInterface
	 */
	protected $ormFactory;
	/**
	 * CollectionORM Factory.
	 * @var CollectionORMFactoryInterface
	 */
	protected $collectionOrmFactory;

	/**
	 * Constructor.
	 * @param \Asgard\Db\DBInterface                  $db
	 * @param \Asgard\Entity\EntityManagerInterface $entityManager
	 * @param string                                  $locale    Default locale.
	 * @param string                                  $prefix    Tables prefix.
	 * @param ORMFactoryInterface                     $ormFactory
	 * @param CollectionORMFactoryInterface           $collectionOrmFactory
	 */
	public function __construct(\Asgard\Db\DBInterface $db, \Asgard\Entity\EntityManagerInterface $entityManager=null, $locale='en', $prefix=null, ORMFactoryInterface $ormFactory=null, CollectionORMFactoryInterface $collectionOrmFactory=null) {
		$this->db                   = $db;
		$this->entityManager      = $entityManager;
		$this->locale               = $locale;
		$this->prefix               = $prefix;
		$this->ormFactory           = $ormFactory;
		$this->collectionOrmFactory = $collectionOrmFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load($entityClass, $id) {
		if(!ctype_digit($id) && !is_int($id))
			return;

		$entity = $this->getEntityManager()->make($entityClass);
		$res = $this->orm($entityClass)->where(['id' => $id])->getDAL()->first();
		if($res)
			$entity->_set(static::unserialize($entity, $res));

		if($entity->isNew())
			return null;
		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function orm($entityClass) {
		$definition = $this->getEntityManager()->get($entityClass);
		return $this->getOrmFactory()->create($definition, $this, $this->locale, $this->prefix);
	}

	/**
	 * {@inheritDoc}
	 */
	public function all($entityClass) {
		return $this->orm($entityClass)->all();
	}

	/**
	 * {@inheritDoc}
	 */
	public function destroyAll($entityClass) {
		$count = 0;
		foreach($this->all($entityClass) as $entity)
			$count += $this->destroy($entity) ? 1:0;
		return $count;
	}

	/**
	 * {@inheritDoc}
	 */
	public function destroyOne($entityClass, $id) {
		if($entity = $this->load($entityClass, $id))
			return $this->destroy($entity);
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function destroy(\Asgard\Entity\Entity $entity) {
		return $entity->getDefinition()->trigger('destroy', [$entity], function($chain, $entity) {
			$orms = [];

			foreach($entity->getDefinition()->relations() as $name=>$relation) {
				if(isset($relation['cascade']['delete']) && $relation['cascade']['delete']) {
					$orm = $this->related($entity, $name);
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

			$entity->id = null;

			return $r > 0;
		});
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityManager() {
		if(!$this->entityManager)
			$this->entityManager = new \Asgard\Entity\EntityManager;
		return $this->entityManager;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create($entityClass, $values=null, $force=false) {
		$m = $this->getEntityManager()->get($entityClass)->make();
		$this->save($m, $values, $force);
		return $m;
	}

	public function createValidator(\Asgard\Entity\Entity $entity) {
		$validator = $entity->getDefinition()->getEntityManager()->createValidator();
		$validator->set('dataMapper', $this);
		return $validator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValidator(\Asgard\Entity\Entity $entity) {
		$validator = $this->createValidator($entity);
		$entity->prepareValidator($validator);
		$this->prepareValidator($entity, $validator);
		return $validator;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepareValidator($entity, $validator) {
		foreach($entity->getDefinition()->properties() as $name=>$property) {
			if($rules = $property->get('ormValidation'))
				$validator->attribute($name, $rules);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function valid(\Asgard\Entity\Entity $entity) {
		$data = $entity->toArrayRaw();
		$validator = $this->getValidator($entity);
		foreach($this->relations($entity->getDefinition()) as $name=>$relation) {
			$data[$name] = $this->related($entity, $name);
			$validator->attribute($name, $relation->getRules());
		}
		return $entity->getDefinition()->trigger('validation', [$entity, $validator, &$data], function($chain, $entity, $validator, &$data) {
			return $validator->valid($data);
		});
	}

	/**
	 * Return relations errors.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return array
	 */
	public function relationsErrors(\Asgard\Entity\Entity $entity) {
		$data = [];
		$validator = $this->createValidator($entity);
		foreach($this->relations($entity->getDefinition()) as $name=>$relation) {
			$data[$name] = $this->related($entity, $name);
			$relation->prepareValidator($validator->attribute($name));
		}
		$errors = $validator->errors($data);

		$e = [];
		foreach($data as $property=>$value) {
			if($propertyErrors = $errors->attribute($property)->errors())
				$e[$property] = $propertyErrors;
		}

		return $e;
	}

	/**
	 * {@inheritDoc}
	 */
	public function errors(\Asgard\Entity\Entity $entity) {
		$data = $entity->toArrayRaw();
		$validator = $this->getValidator($entity);
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
	 * {@inheritDoc}
	 */
	public function save(\Asgard\Entity\Entity $entity, $values=null, $force=false) {
		#set $values if any
		if($values)
			$entity->set($values);

		if(!$force && $errors = $this->errors($entity)) {
			$msg = implode("\n", \Asgard\Common\ArrayUtils::flatten($errors));
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

		$vars = $i18n = [];
		#process data
		foreach($entity->getDefinition()->properties() as $name=>$prop) {
			#i18n properties
			if($prop->get('i18n')) {
				$values = $entity->get($name, $entity->getLocales());
				foreach($values as $locale=>$v)
					$i18n[$locale][$name] = $entity->getDefinition()->property($name)->serialize($v);
			}
			#relations with a single entity
			elseif($prop->get('type') == 'entity') {
				$rel = $this->relation($entity->getDefinition(), $name);
				if(!$rel->get('many')) {
					$link = $rel->getLink();
					$relatedEntity = $entity->data['properties'][$name];
					#entity object
					if(is_object($relatedEntity)) {
						if($relatedEntity->isNew())
							$this->save($relatedEntity, null, $force);
						$vars[$link] = $relatedEntity->id;
						if($rel->isPolymorphic())
							$vars[$rel->getLinkType()] = get_class($relatedEntity);
					}
					else {
						#array with class and id
						if($rel->isPolymorphic()) {
							$vars[$rel->getLinkType()] = $relatedEntity[0];
							$vars[$link] = $relatedEntity[1];
						}
						#id
						else
							$vars[$link] = $relatedEntity;
					}
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
			$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable($entity->getDefinition()));
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
			$rel = $this->relation($entity->getDefinition(), $relation);

			$type = $rel->type();
			if($type !== 'belongsTo') {
				if($rel->isPolymorphic()) {

					if($type == 'hasOne') {
						#todo comment trouver l'ancien entity?
						// $reverse_rel = $rel->reverse();
						// $relation_entity = $rel->get('entity');
						// $link = $reverse_rel->getLink();
						
						// $this->orm($relation_entity)->where([$link => $entity->id])->getDAL()->update([$link => 0]);
						// $this->orm($relation_entity)->where(['id' => $entity->data['properties'][$relation]->id])->getDAL()->update([$link => $entity->id]);
					}
					elseif($rel->get('many'))
						$this->related($entity, $relation)->sync($entity->data['properties'][$relation]->all());
				}
				else {
					$reverse_rel = $rel->reverse();
					$relation_entity = $rel->get('entity');
					$link = $reverse_rel->getLink();

					if($reverse_rel->isPolymorphic()) {
						if($type == 'hasOne') {
							$linkType = $reverse_rel->getLinkType();
							$this->orm($relation_entity)->where([$link => $entity->id, $linkType => get_class($entity)])->getDAL()->update([$link => null, $linkType => null]);
							$this->orm($relation_entity)->where(['id' => $entity->data['properties'][$relation]->id])->getDAL()->update([$link => $entity->id, $linkType => get_class($entity)]);
						}
						elseif($rel->get('many'))
							$this->related($entity, $relation)->sync($entity->data['properties'][$relation]->all());
					}
					else {
						if($type == 'hasOne') {
							$this->orm($relation_entity)->where([$link => $entity->id])->getDAL()->update([$link => 0]);
							$this->orm($relation_entity)->where(['id' => $entity->data['properties'][$relation]->id])->getDAL()->update([$link => $entity->id]);
						}
						elseif($rel->get('many'))
							$this->related($entity, $relation)->sync($entity->data['properties'][$relation]->all());
					}
				}
			}
		}

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function related(\Asgard\Entity\Entity $entity, $name) {
		$rel = $this->relation($entity->getDefinition(), $name);

		switch($rel->type()) {
			case 'hasOne':
			case 'belongsTo':
				$link = $rel->getLink();
				if($rel->isPolymorphic()) {
					$relEntity = $entity->get($rel->getLinkType());
					if(!$relEntity)
						return;
				}
				else
					$relEntity = $rel->get('entity');
				return $this->orm($relEntity)->where('id', $entity->get($link));
			case 'hasMany':
			case 'HMABT':
				return $this->getCollectionOrmFactory()->create($entity, $name, $this, $this->locale, $this->prefix);
			default:
				throw new \Exception('Relation '.$rel->type().' does not exist.');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCollectionOrmFactory() {
		if(!$this->collectionOrmFactory)
			$this->collectionOrmFactory = new CollectionORMFactory;
		return $this->collectionOrmFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOrmFactory() {
		if(!$this->ormFactory)
			$this->ormFactory = new ORMFactory;
		return $this->ormFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRelated(\Asgard\Entity\Entity $entity, $name) {
		$orm = $this->related($entity, $name);
		$rel = $this->relation($entity->getDefinition(), $name);
		if($rel->get('many'))
			return $orm->get();
		else
			return $orm->first();
	}

	/**
	 * Create an ORM instance for a specific entity.
	 * @param  \Asgard\Entity\Entity $entity
	 * @return ORMInterface
	 */
	protected function entityORM(\Asgard\Entity\Entity $entity) {
		if($entity->isNew())
			return $this->orm(get_class($entity));
		else
			return $this->orm(get_class($entity))->where(['id' => $entity->id]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTranslationTable(\Asgard\Entity\Definition $definition) {
		return $this->getTable($definition).'_translation';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTable(\Asgard\Entity\Definition $definition) {
		if($definition->get('table'))
			return $this->prefix.$definition->get('table');
		else
			return $this->prefix.$definition->getShortName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTranslations(\Asgard\Entity\Entity $entity, $locale=null) {
		$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable($entity->getDefinition()));
		$res = $dal->where(['id' => $entity->id, 'locale'=>$locale])->first();
		if(!$res)
			return $entity;
		unset($res['id']);
		unset($res['locale']);

		foreach($res as $k=>$v)
			$entity->_set($k, $v, $locale);
		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function relations(\Asgard\Entity\Definition $definition) {
		if($relations = $definition->get('relations'))
			return $relations;
		else {
			$relations = [];
			foreach($definition->properties() as $name=>$prop) {
				if($prop->get('type') == 'entity')
					$relations[$name] = new EntityRelation($definition, $this, $name, $prop->getParams());
			}
			$definition->set('relations', $relations);
			return $relations;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function relation(\Asgard\Entity\Definition $definition, $name) {
		#polymorphic
		if(strpos($name, '|')) {
			list($name, $class) = explode('|', $name);
			$relation = $this->relations($definition)[$name];
			$relation->setTargetDefinition($this->entityManager->get($class));
			return $relation;
		}
		else
			return $this->relations($definition)[$name];
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasRelation(\Asgard\Entity\Definition $definition, $name) {
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
		}

		return $data;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDB() {
		return $this->db;
	}
}
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
	 * Transformers to prepare SQL inputs.
	 * @var array
	 */
	protected $sqlInputTransformers;
	/**
	 * Proxy generator.
	 * @var array
	 */
	protected $proxyGenerator;

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
		$this->entityManager        = $entityManager;
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

		return $this->orm($entityClass)->where(['id' => $id])->first();
	}

	/**
	 * {@inheritDoc}
	 */
	public function orm($entityClass) {
		$definition = $this->getEntityManager()->get($entityClass);
		return $this->getOrmFactory()->create($definition, $this, $this->locale, $this->prefix);
	}

	public function getPrefix() {
		return $this->prefix;
	}

	/**
	 * {@inheritDoc}
	 */
	public function destroyAll($entityClass) {
		$count = 0;
		foreach($this->orm($entityClass) as $entity)
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
		return $this->getEntityDefinition($entity)->trigger('destroy', [$entity], function($chain, $entity) {
			$orms = [];

			foreach($this->relations($this->getEntityDefinition($entity)) as $name=>$relation) {
				if(isset($relation->get('cascade')['delete']) && $relation->get('cascade')['delete']) {
					$orm = $this->related($entity, $name);
					if(!is_object($orm))
						continue;
					$orm->getDAL()->query();
					$orms[] = $orm;
				}
			}

			if($this->getEntityDefinition($entity)->isI18N())
				$r = $this->entityORM($entity)->getDAL()->delete([$this->getTable($entity), $this->getTranslationTable($entity)]);
			else
				$r = $this->entityORM($entity)->getDAL()->delete();

			#Files
			foreach($this->getEntityDefinition($entity)->properties() as $name=>$prop) {
				if($prop instanceof \Asgard\Entity\Property\FileProperty) {
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
			$this->entityManager = \Asgard\Entity\EntityManager::singleton();
		return $this->entityManager;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create($entityClass, array $values=[], $groups=[]) {
		$m = $this->getEntityManager()->get($entityClass)->make();
		$this->save($m, $values, $groups);
		return $m;
	}

	public function createValidator(\Asgard\Entity\Entity $entity) {
		$validator = $this->getEntityDefinition($entity)->getEntityManager()->createValidator();
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
		if($validator->get('datamapper_prepared'))
			return;

		$validator->set('datamapper_prepared', true);
		$validator->set('dataMapper', $this);
	}

	/**
	 * {@inheritDoc}
	 */
	public function valid(\Asgard\Entity\Entity $entity, $groups=[]) {
		$data = $entity->toArrayRaw();
		$validator = $this->getValidator($entity);

		return $validator->valid($data, $groups);
	}

	/**
	 * Return relations errors.
	 * @param  \Asgard\Entity\Entity $entity
	 * @param  array|null            $groups
	 * @return \Asgard\Validation\Report
	 */
	public function relationsErrors(\Asgard\Entity\Entity $entity, $groups=[]) {
		$data = [];
		$validator = $this->createValidator($entity);
		$validator->set('entity', $entity);
		$validator->set('dataMapper', $this);
		foreach($this->relations($this->getEntityDefinition($entity)) as $name=>$relation) {
			$data[$name] = $entity->get($name, null, false);
			$relation->prepareValidator($validator->attribute($name));
			$property = $this->getEntityDefinition($entity)->property($name);
			$validator->attribute($name)->ruleMessages($property->getMessages());
			$validator->attribute($name)->isNull(function(){return false;});
		}

		return $validator->errors($data, $groups);
	}

	/**
	 * {@inheritDoc}
	 */
	public function errors(\Asgard\Entity\Entity $entity, $groups=[]) {
		$data = $entity->toArrayRaw();
		$validator = $this->getValidator($entity);

		return $validator->errors($data, $groups);
	}

	/**
	 * Prepare input before inserting/updatig database.
	 * @param  \Asgard\Entity\Property $prop
	 * @param  mixed                   $value
	 * @return mixed
	 */
	protected function prepareInput(\Asgard\Entity\Property $prop, $value) {
		$class = get_class($prop);

		if(is_null($value))
			return null;
		elseif($prop->get('many'))
			return $prop->serialize($value);
		elseif(isset($this->sqlInputTransformers[$class]))
			return $this->sqlInputTransformers[$class]->toSQL($value);
		elseif(method_exists($prop, 'toSQL'))
			return $prop->toSQL($value);
		else
			return $prop->serialize($value);
	}

	/**
	 * Add a transformer to prepare SQL input.
	 * @param  string                          $class Entity property class.
	 * @param  SQLPropertyTransformerInterface $spi
	 * @return static
	 */
	public function addSqlInput($class, SQLPropertyTransformerInterface $spi) {
		$this->sqlInputTransformers[$class] = $spi;
		return $this;
	}

	/**
	 * Return a transformer to prepare SQL input.
	 * @param  string                          $class Entity property class.
	 */
	public function getSqlInput($class) {
		if(isset($this->sqlInputTransformers[$class]))
			return $this->sqlInputTransformers[$class];
	}

	/**
	 * {@inheritDoc}
	 */
	public function save(\Asgard\Entity\Entity $entity, array $values=[], $groups=[]) {
		#set $values if any
		if($values)
			$entity->set($values);

		if($groups !== null) {
			$errors = $this->errors($entity, $groups);
			if(!$errors->valid())
				throw new \Asgard\Entity\EntityException($errors->fullMessage(), $errors);
		}

		$this->getEntityDefinition($entity)->trigger('save', [$entity], function(\Asgard\Hook\Chain $chain, $entity) use($groups) {
			#Files
			foreach($this->getEntityDefinition($entity)->properties() as $name=>$prop) {
				if($prop instanceof \Asgard\Entity\Property\FileProperty) {
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

			$orm = $this->orm($entity->getClass());
			$persisted = $entity->getParameter('persisted');

			$vars = $i18n = [];
			#process data
			foreach($this->getEntityDefinition($entity)->properties() as $name=>$prop) {
				#i18n properties
				if($prop->get('i18n')) {
					$values = $entity->get($name, $entity->getLocales());
					foreach($values as $locale=>$v) {
						if(!$persisted || in_array($name, $entity->getChangedI18N($locale)))
							$i18n[$locale][$name] = $this->prepareInput($prop, $v);
					}
				}
				#other properties
				elseif(!$persisted || in_array($name, $entity->getChanged())) {
					#relations with a single entity
					if($prop->get('type') === 'entity') {
						$rel = $this->relation($this->getEntityDefinition($entity), $name);
						if(!$rel->get('many')) {
							$link = $rel->getLink();
							$relatedEntity = $entity->data['properties'][$name];
							#entity object
							if(is_object($relatedEntity)) {
								if($relatedEntity->isNew())
									$this->save($relatedEntity, [], $groups===null ? null:[]);
								$vars[$link] = $relatedEntity->id;
								if($rel->isPolymorphic())
									$vars[$rel->getLinkType()] = $relatedEntity->getClass();
							}
							elseif($relatedEntity !== null) {
								if($rel->isPolymorphic()) {
									if($relatedEntity) {
										if(!is_array($relatedEntity))
											throw new \Exception('Polymorphic entities must be an object or an array.');
										#array with class and id
										$vars[$rel->getLinkType()] = $relatedEntity[0];
										$vars[$link] = $relatedEntity[1];
									}
									else
										$vars[$rel->getLinkType()] = $vars[$link] = null;
								}
								#id
								else
									$vars[$link] = $relatedEntity;
							}
						}
					}
					else {
						$value = $entity->get($name);
						$vars[$name] = $this->prepareInput($prop, $value);
					}
				}
			}

			if(!$this->getDB()->inTransaction()) {
				$inTransaction = false;
				$this->getDB()->beginTransaction();
			}
			else
				$inTransaction = true;

			try {
				#Persist
				if(count($vars) > 0) {
					if($persisted)
						$orm->reset()->resetScopes()->where(['id'=>$entity->id])->getDAL()->update($vars);
					else
						$entity->id = $orm->getDAL()->insert($vars);
				}

				#Persist i18n
				foreach($i18n as $locale=>$values) {
					$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable($this->getEntityDefinition($entity)));
					if($dal->where(['id'=>$entity->id, 'locale'=>$locale])->count())
						$dal->where(['id'=>$entity->id, 'locale'=>$locale])->update($values);
					else {
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
				foreach($this->relations($this->getEntityDefinition($entity)) as $relation => $params) {
					if(!isset($entity->data['properties'][$relation]))
						continue;
					$value = $entity->data['properties'][$relation];
					if(!in_array($relation, $entity->getChanged())) {
						if($value instanceof PersistentCollection || $value instanceof \Asgard\Entity\ManyCollection) {
							if(!$value->isDirty())
								continue;
						}
						else
							continue;
					}
					$rel = $this->relation($this->getEntityDefinition($entity), $relation);

					#collection with many entities
					if($rel->get('many')) {
						if($value instanceof \Asgard\Entity\ManyCollection) {
							$persistentCollection = new PersistentCollection($entity, $relation, $this);
							foreach($value as $element)
								$persistentCollection->add($element);
							$entity->data['properties'][$relation] = $persistentCollection;
						}
						$entity->data['properties'][$relation]->sync();
					}
					#one-to-one relation
					elseif(!$rel->reverse()->get('many'))
						$this->related($entity, $relation)->sync($value);
						#todo unique
					#todo many-to-one relation
				}
			} catch(\Exception $e) {
				if(!$inTransaction)
					$this->getDB()->rollback();
				throw $e;
			}

			if(!$inTransaction)
				$this->getDB()->commit();

			$entity->resetChanged();
			$entity->setParameter('persisted', true);
		});

		return $entity;
	}

	/**
	 * {@inheritDoc}
	 */
	public function related(\Asgard\Entity\Entity $entity, $name) {
		return $this->getCollectionOrmFactory()->create($entity, $name, $this, $this->locale, $this->prefix);
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
		$rel = $this->relation($this->getEntityDefinition($entity), $name);

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
			return $this->orm($entity->getClass());
		else
			return $this->orm($entity->getClass())->where(['id' => $entity->id]);
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
		$dal = new \Asgard\Db\DAL($this->db, $this->getTranslationTable($this->getEntityDefinition($entity)));
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
		$name = strtolower($name);
		#polymorphic
		if(strpos($name, '|')) {
			list($name, $class) = explode('|', $name);
			$relation = $this->relations($definition)[$name];
			$relation->setTargetDefinition($this->entityManager->get($class));
			return $relation;
		}
		else {
			$relation = clone $this->relations($definition)[$name];
			$reverseRelation = $relation->reverse();
			if($reverseRelation->isPolymorphic())
				$reverseRelation->setTargetDefinition($definition);
			return $relation;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasRelation(\Asgard\Entity\Definition $definition, $name) {
		$name = strtolower($name);
		return isset($this->relations($definition)[$name]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDB() {
		return $this->db;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDB(\Asgard\Db\DBInterface $db) {
		$this->db = $db;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getEntityDefinition(\Asgard\Entity\Entity $entity) {
		return $this->entityManager->get($entity->getClass());
	}

	public function createEntityProxy($class, $id) {
		$proxyGenerator = $this->proxyGenerator;
		if(!$proxyGenerator)
			$proxyGenerator =  new Proxy\ProxyGenerator;
		$entityProxy = $proxyGenerator->createProxy($this, $class, $id);
		$entityProxy->setParameter('persisted', true);

		return $entityProxy;
	}

	/**
	 * {@inheritDoc}
	 */
	public function initializeEntityProxy($entityProxy) {
		$orm = $this->orm($entityProxy->getClass());
		$orm->initializeEntityProxy($entityProxy);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setProxyGenerator(Proxy\ProxyGenerator $proxyGenerator) {
		$this->proxyGenerator = $proxyGenerator;
	}
}
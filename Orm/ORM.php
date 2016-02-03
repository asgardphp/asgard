<?php
namespace Asgard\Orm;

/**
 * Helps performing operations like selection, deletion and update on a set of entities.
 * @author Michel Hognerud <michel@hognerud.net>
*/
class ORM implements ORMInterface {
	/**
	 * DataMapper instance.
	 * @var DataMapperInterface
	 */
	protected $dataMapper;
	/**
	 * Entity definition.
	 * @var \Asgard\Entity\Definition
	 */
	protected $definition;
	/**
	 * Eager-loaded relations.
	 * @var array
	 */
	protected $with;
	/**
	 * Conditions.
	 * @var array
	 */
	protected $where = [];
	/**
	 * Having conditions.
	 * @var array
	 */
	protected $having = [];
	/**
	 * OrderBy.
	 * @var string
	 */
	protected $orderBy;
	/**
	 * Limit.
	 * @var integer
	 */
	protected $limit;
	/**
	 * Offset.
	 * @var integer
	 */
	protected $offset;
	/**
	 * Joined relations.
	 * @var array
	 */
	protected $join = [];
	/**
	 * Page number.
	 * @var integer
	 */
	protected $page;
	/**
	 * Number of elements per page.
	 * @var integer
	 */
	protected $per_page;
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
	 * Ongoing DAL instance.
	 * @var \Asgard\Db\DAL
	 */
	protected $tmp_dal = null;
	/**
	 * Paginator factory.
	 * @var \Asgard\Common\PaginatorFactoryInterface
	 */
	protected $paginatorFactory = null;
	/**
	 * Scopes.
	 * @var array
	 */
	protected $scopes = [];
	/**
	 * Reversed query.
	 * @var boolean
	 */
	protected $reversed = false;
	/**
	 * Selects.
	 * @var array
	 */
	protected $selects = [];
	/**
	 * Dal callbacks.
	 * @var array
	 */
	protected $dalCallbacks = [];
	/**
	 * UNIONs.
	 * @var array
	 */
	protected $unions = [];

	protected $groupBy = null;

	/**
	 * Constructor.
	 * @param \Asgard\Entity\Definition $definition
	 * @param DataMapperInterface             $datamapper
	 * @param string                          $locale      default locale
	 * @param string                          $prefix      tables prefix
	 * @param \Asgard\Common\PaginatorFactoryInterface   $paginatorFactory
	 */
	public function __construct(\Asgard\Entity\Definition $definition, DataMapperInterface $datamapper, $locale=null, $prefix=null, \Asgard\Common\PaginatorFactoryInterface $paginatorFactory=null) {
		$this->definition       = $definition;
		$this->dataMapper       = $datamapper;
		if($locale !== null)
			$this->locale = $locale;
		else
			$this->locale = $definition->getEntityManager()->getDefaultLocale();
		$this->prefix           = $prefix;
		$this->paginatorFactory = $paginatorFactory;

		if($scopes = $definition->get('orm.scopes')) {
			foreach($scopes as $name=>$scope)
				$this->addScope($name, $scope);
		}

		if($this->definition->get('order_by'))
			$this->orderBy($this->definition->get('order_by'));
		else
			$this->orderBy('id DESC');
	}

	/**
	 * {@inheritDoc}
	*/
	public function addScope($name, $scope) {
		if(isset($this->scopes[$name]))
			throw new \Exception('Scope '.$name.' already exists.');
		$this->scopes[$name] = $scope;
		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function removeScope($name) {
		unset($this->scopes[$name]);
		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function resetScopes() {
		$this->scopes = [];
		return $this;
	}

	/**
	 * Return the definition.
	 * @return \Asgard\Entity\Definition
	 */
	public function getDefinition() {
		return $this->definition;
	}

	/**
	 * {@inheritDoc}
	*/
	public function __call($relationName, array $args) {
		return $this->relation($relationName);
	}

	/**
	 * Return a new ORM for the given relation.
	 * @param  string $relationName
	 * @return ORMInterface
	 */
	public function relation($relationName) {
		if(!$this->dataMapper->hasRelation($this->definition, $relationName))
			throw new \Exception('Relation '.$relationName.' does not exist.');
		$relation = $this->dataMapper->relation($this->definition, $relationName);
		$reverseRelation = $relation->reverse();
		$reverseRelationName = $reverseRelation->get('name');
		$relation_entity = $relation->get('entity');

		$table = $this->getTable();
		$alias = $reverseRelationName;

		$c = clone $this;
		$c->applyScopes();

		$where = $this->processConditions($c->getWhere());
		$where = $this->updateConditions($where, $table, $alias);

		return $this->dataMapper->orm($relation_entity)
			->where($where)
			->join('innerjoin '.$reverseRelationName, $this->join);
	}

	/**
	 * Return the where conditions.
	 * @return array
	 */
	public function getWhere() {
		return $this->where;
	}

	/**
	 * Replace the old table with the new alias.
	 * @param  array  $conditions
	 * @return array
	 */
	protected function updateConditions(array $conditions, $table, $alias) {
		$res = [];

		foreach($conditions as $k=>$v) {
			if(is_array($v))
				$v = $this->updateConditions($v, $table, $alias);
			else
				$v = preg_replace('/(?<![\.a-zA-Z0-9-_`\(\)])'.$table.'\./', $alias.'.', $v);
			$k = preg_replace('/(?<![\.a-zA-Z0-9-_`\(\)])'.$table.'\./', $alias.'.', $k);
			$res[$k] = $v;
		}

		return $res;
	}

	/**
	 * {@inheritDoc}
	*/
	public function __get($name) {
		return $this->$name()->get();
	}

	/**
	 * Load an entity.
	 * @param  integer $id
	 * @return \Asgard\Entity\Entity
	 */
	public function load($id) {
		$clone = clone $this;
		return $clone->where(['id' => $id])->first();
	}

	/**
	 * {@inheritDoc}
	*/
	public function reverse() {
		$this->reversed = !$this->reversed;
		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function last() {
		$c = clone $this;
		return $c->reverse()->first();
	}

	/**
	 * Get new alias if current already exists.
	 * @param  string $name
	 * @param  array  $existing
	 * @return string
	 */
	protected function getNewAlias($name, array $existing) {
		$i=1;
		$alias = $name;
		while(in_array($alias, $existing))
			$alias = $name.$i++;
		return $alias;
	}

	/**
	 * {@inheritDoc}
	*/
	public function joinToEntity($relation, \Asgard\Entity\Entity $entity) {
		if(is_string($relation))
			$relation = $this->dataMapper->relation($this->definition, $relation);

		$relationName = $relation->getName();
		$alias = $this->getNewAlias($relationName, $this->getAliases($this->join));

		if($alias !== $relationName)
			$relationName .= ' '.$alias;

		$this->where($alias.'.id', $entity->id);
		$this->join('innerjoin '.$relationName);

		return $this;
	}

	protected function getAliases(array $jointures) {
		$aliases = [];
		foreach($jointures as $name=>$subjointures) {
			if(is_array($subjointures)) {
				if(!is_numeric($name)) {
					$exp = explode(' ', $name);
					$alias = count($exp) > 1 ? $exp[1]:$exp[0];
					$aliases[] = $alias;
				}
				$aliases = array_merge($aliases, $this->getAliases($subjointures));
			}
			else {
				$name = $subjointures;
				$exp = explode(' ', $name);
				$alias = count($exp) > 1 ? $exp[1]:$exp[0];
				$aliases[] = $alias;
			}
		}
		return $aliases;
	}

	public function setJointuresAliases($jointures, array $existing=[]) {
		if(is_array($jointures)) {
			foreach($clone=$jointures as $k=>$v) {
				$name = $k;
				if(!is_numeric($name)) {
					$type = '';
					if(preg_match('/^[^ ]+join /', $name, $matches)) {
						$type = $matches[0];
						$name = preg_replace('/^[^ ]* /', '', $name);
					}

					$exp = explode(' ', $name);
					$origName = $exp[0];
					$oldAlias = count($exp) > 1 ? $exp[1]:$exp[0];

					$alias = $this->getNewAlias($oldAlias, $existing);

					if($alias !== $oldAlias) {
						$name = $origName.' '.$alias;
						unset($jointures[$k]);
						$this->where = $this->updateConditions($this->where, $oldAlias, $alias);#replace old name in conditions
					}
					$name = $type.$name;

					$existing[] = $alias;
				}

				$jointures[$name] = $newJointures = $this->setJointuresAliases($v, $existing);
				$newJointures = (array)$newJointures;
				$existing = array_merge($existing, $this->getAliases($newJointures)); #add new aliases
			}
		}
		else {
			$name = $jointures;
			$type = '';
			if(preg_match('/^[^ ]+join /', $name, $matches)) {
				$type = $matches[0];
				$name = preg_replace('/^[^ ]* /', '', $name);
			}
			$exp = explode(' ', $name);
			$origName = $exp[0];
			$oldAlias = count($exp) > 1 ? $exp[1]:$exp[0];

			$alias = $this->getNewAlias($oldAlias, $existing);
			if($alias !== $oldAlias) {
				$name = $origName.' '.$alias;
				$this->where = $this->updateConditions($this->where, $oldAlias, $alias);#replace old name in conditions
			}
			$name = $type.$name;
			return $name;
		}

		return $jointures;
	}

	/**
	 * {@inheritDoc}
	*/
	public function join($relation, array $subrelations=null) {
		$aliases = array_merge([$this->getTable()], $this->getAliases($this->join));
		
		if($subrelations) {
			$type = '';
			if(preg_match('/^[^ ]+join /', $relation, $matches)) {
				$type = $matches[0];
				$relation = preg_replace('/^[^ ]* /', '', $relation);
			}

			$alias = $relation;

			$i=1;
			while(in_array($alias, $aliases))
				$alias = $relation.$i++;
			$aliases[] = $alias;
			if($alias !== $relation)
				$relation .= ' '.$alias;
			$subrelations = $this->setJointuresAliases($subrelations, $aliases);
			$relation = $type.$relation;
			$this->join[$relation] = $subrelations;
		}
		else {
			$relations = $this->setJointuresAliases($relation, $aliases);
			$this->join[] = $relations;
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function getTable() {
		return $this->dataMapper->getTable($this->definition);
	}

	/**
	 * {@inheritDoc}
	*/
	public function getTranslationTable() {
		return $this->dataMapper->getTranslationTable($this->definition);
	}

	/**
	 * Converts a raw array to an entity.
	 *
	 * @param array                     $raw
	 * @param \Asgard\Entity\Definition $definition The definition of the entity to be instantiated.
	 *
	 * @return \Asgard\Entity\Entity
	*/
	protected function hydrate(array $raw, \Asgard\Entity\Definition $definition=null) {
		if(!$definition)
			$definition = $this->definition;
		$new = $definition->make([], $this->locale);
		static::unserialize($new, $raw);
		$new->resetChanged();
		return $new;
	}

	/**
	 * Fills up an entity with a raw array of data.
	 *
	 * @param \Asgard\Entity\Entity $entity
	 * @param array                 $data
	 * @param string                $locale Only necessary if the data concerns a specific locale.
	 *
	 * @return \Asgard\Entity\Entity
	*/
	protected static function unserialize(\Asgard\Entity\Entity $entity, array $data, $locale=null) {
		foreach($data as $k=>$v) {
			if($entity->getDefinition()->hasProperty($k))
				$data[$k] = $entity->getDefinition()->property($k)->unserialize($v, $entity, $k);
		}

		return $entity->_set($data, $locale, null, false);
	}

	/**
	 * {@inheritDoc}
	*/
	public function next() {
		if(!$this->tmp_dal)
			$this->tmp_dal = $this->getDAL();
		if(!($r = $this->tmp_dal->next()))
			return null;
		else
			return $this->hydrate($r);
	}

	/**
	 * {@inheritDoc}
	*/
	public function ids() {
		return $this->values('id');
	}

	/**
	 * {@inheritDoc}
	*/
	public function values($property) {
		$res = [];
		$this->tmp_dal = $this->getDAL();
		while($one = $this->next())
			$res[] = $one->get($property);
		return $res;
	}

	/**
	 * {@inheritDoc}
	*/
	public function first() {
		$res = $this->limit(1)->get();
		if(!count($res))
			return null;
		return $res[0];
	}

	public function applyScopes() {
		foreach($this->scopes as $scope)
			$scope->process($this);
		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function getDAL() {
		$clone = clone $this;
		return $clone->applyScopes()->_getDAL();
	}

	public function addSelect($select) {
		$this->selects[] = $select;
		return $this;
	}

	/**
	 * Build the DAL.
	 * @return \Asgard\Db\DAL
	 */
	public function _getDAL() {
		$dal = new \Asgard\Db\DAL($this->dataMapper->getDB());
		$table = $this->getTable();
		$dal->orderBy($this->orderBy);
		if($this->reversed)
			$dal->reverse();
		$dal->limit($this->limit);
		$dal->offset($this->offset);
		if($this->groupBy === null)
			$dal->groupBy($table.'.id');
		elseif($this->groupBy !== false)
			$dal->groupBy($this->groupBy);
		$dal->select($this->selects);

		$dal->having($this->processConditions($this->having));
		$dal->where($this->processConditions($this->where));

		if($this->definition->isI18N()) {
			$translation_table = $this->getTranslationTable();
			$selects = [$table.'.*'];
			foreach($this->definition->properties() as $name=>$property) {
				if($property->get('i18n'))
					$selects[] = $translation_table.'.'.$name;
			}
			$dal->addSelect($selects);
			$dal->from($table);
			$dal->leftjoin([
				$translation_table => $this->processConditions([
					$table.'.id = '.$translation_table.'.id',
					$translation_table.'.locale' => $this->locale
				])
			]);
		}
		else {
			$dal->addSelect([$table.'.*']);
			$dal->from($table);
		}

		$this->recursiveJointures($dal, $this->join, $this->definition, $this->getTable());

		foreach($this->dalCallbacks as $cb)
			$cb($dal);

		foreach($this->unions as $union)
			$dal->union($union->getDAL());

		return $dal;
	}

	public function dalCallback(callable $cb) {
		$this->dalCallbacks[] = $cb;
		return $this;
	}

	/**
	 * Performs jointures on the DAL object.
	 *
	 * @param \Asgard\Db\DAL            $dal
	 * @param array                     $jointures Array of relations.
	 * @param \Asgard\Entity\Definition $definition The entity class from which jointures are built.
	 * @param string                    $table The table from which to performs jointures.
	*/
	protected function recursiveJointures(\Asgard\Db\DAL $dal, $jointures, \Asgard\Entity\Definition $definition, $table) {
		$alias = null;
		if(is_array($jointures)) {
			foreach($jointures as $relation=>$v) {
				#jointure type
				if(preg_match('/^[^ ]+join /', $relation, $matches)) {
					$type = trim($matches[0]);
					$relation = preg_replace('/^[^ ]* /', '', $relation);
				}
				else
					$type = 'leftjoin';
				if(!is_numeric($relation)) {
					$relationName = $relation;
					if(strpos($relationName, ' '))
						list($relationName, $alias) = explode(' ', $relationName);
					else
						$alias = null;
					$relation = $this->dataMapper->relation($definition, $relationName);
					$this->jointure($dal, $relation, $alias, $table, $type);

					$tableAlias = $alias ? $alias:$relationName;

					$this->recursiveJointures($dal, $v, $relation->getTargetDefinition(), $tableAlias);
				}
				else
					$this->recursiveJointures($dal, $v, $definition, $table);
			}
		}
		else {
			$relation = $jointures;
			#jointure type
			if(preg_match('/^[^ ]+join /', $relation, $matches)) {
				$type = trim($matches[0]);
				$relation = preg_replace('/^[^ ]* /', '', $relation);
			}
			else
				$type = 'leftjoin';
			if(strpos($relation, ' '))
				list($relation, $alias) = explode(' ', $relation);
			$relation = $this->dataMapper->relation($definition, $relation);
			$this->jointure($dal, $relation, $alias, $table, $type);
		}
	}

	/**
	 * Performs a single jointure.
	 *
	 * @param \Asgard\Db\DAL $dal
	 * @param EntityRelation $relation The name of the relation.
	 * @param string         $alias How the related table will be referenced in the SQL query.
	 * @param string         $ref_table The table from which to performs the jointure.
	*/
	protected function jointure(\Asgard\Db\DAL $dal, $relation, $alias, $ref_table, $type='leftjoin') {
		$relationName = $relation->getName();

		$relationDefinition = $relation->getTargetDefinition();
		if($alias === null)
			$alias = $relationName;

		switch($relation->type()) {
			case 'hasOne':
			case 'belongsTo':
				$link = $relation->getLink();
				$table = $this->dataMapper->getTable($relationDefinition);
				$dal->join($type, [
					$table.' '.$alias => $this->processConditions([
						$alias.'.id = '.$ref_table.'.'.$link
					])
				]);
				break;
			case 'hasMany':
				$link = $relation->getLink();
				$table = $this->dataMapper->getTable($relationDefinition);
				if($relation->isPolymorphic()) {
					$dal->join($type, [
						$table.' '.$alias => $this->processConditions([
							$alias.'.'.$link.' = '.$ref_table.'.id',
						])
					]);
				}
				else {
					$dal->join($type, [
						$table.' '.$alias => $this->processConditions([
							$alias.'.'.$link.' = '.$ref_table.'.id',
						])
					]);
				}
				break;
			case 'HMABT':
				if($relation->isPolymorphic()) {
					$dal->join($type, [
						$relation->getAssociationTable() => $this->processConditions([
							$relation->getAssociationTable().'.'.$relation->getLinkA().' = '.$ref_table.'.id',
							$relation->getAssociationTable().'.'.$relation->getLinkType() => $relation->getTargetDefinition()->getClass(),
						])
					]);
				}
				else {
					$dal->join($type, [
						$relation->getAssociationTable() => $this->processConditions([
							$relation->getAssociationTable().'.'.$relation->getLinkA().' = '.$ref_table.'.id',
						])
					]);
				}
				$dal->join($type, [
					$this->dataMapper->getTable($relationDefinition).' '.$alias => $this->processConditions([
						$relation->getAssociationTable().'.'.$relation->getLinkB().' = '.$alias.'.id',
					])
				]);
				if($relation->reverse()->get('sortable'))
					$dal->orderBy($relation->getAssociationTable().'.'.$relation->reverse()->getPositionField().' ASC');
				break;
		}

		if($relationDefinition->isI18N()) {
			$translation_table = $this->dataMapper->getTranslationTable($relationDefinition);
			$dal->leftjoin([
				$translation_table.' '.$relationName.'_translation' => $this->processConditions([
					$ref_table.'.id = '.$relationName.'_translation.id',
					$relationName.'_translation.locale' => $this->locale
				])
			]);
		}
	}

	/**
	 * {@inheritDoc}
	*/
	public function get() {
		$entities = [];
		$ids = [];

		if($this->tmp_dal)
			$dal = $this->tmp_dal;
		else
			$dal = $this->getDAL();

		$rows = $dal->get();
		foreach($rows as $row) {
			if(!$row['id'])
				continue;
			$entities[] = $this->hydrate($row);
			$ids[] = $row['id'];
		}

		if(count($entities) && count($this->with)) {
			foreach($this->with as $relationName=>$closure) {
				$rel = $this->dataMapper->relation($this->definition, $relationName);
				$relation_type = $rel->type();
				$relation_entity = $rel->get('entity');
				$relationDefinition = $rel->getTargetDefinition();

				switch($relation_type) {
					case 'hasOne':
					case 'belongsTo':
						$link = $rel->getLink();

						$orm = $this->dataMapper->orm($relation_entity)->where(['id IN ('.implode(', ', $ids).')']);
						if(is_callable($closure))
							$closure($orm);
						$res = [];
						foreach($orm->get() as $e)
							$res[$e->id] = $e;
						foreach($entities as $entity) {
							$relation_id = $entity->get($link);
							$e = isset($res[$relation_id]) ? $res[$relation_id]:null;
							$entity->set($relationName, $e);
						}
						break;
					case 'hasMany':
						$link = $rel->getLink();

						$orm = $this->dataMapper->orm($relation_entity)->where([$link.' IN ('.implode(', ', $ids).')']);
						if(is_callable($closure))
							$closure($orm);
						$res = $orm->get();
						foreach($entities as $entity) {
							$id = $entity->id;
							$filter = array_filter($res, function($result) use($id, $link) {
								return ($id == $result->get($link));
							});
							$filter = array_values($filter);
							$entity->set($relationName, $filter);
						}
						break;
					case 'HMABT':
						$joinTable = $rel->getAssociationTable();
						$currentEntityIdfield = $rel->getLinkA();
						$reverseRelationName = $rel->reverse()->get('name');

						$orm = $this->dataMapper
							->orm($relation_entity)
							->join('innerjoin '.$reverseRelationName)
							->where([
								$reverseRelationName.'.id IN ('.implode(', ', $ids).')',
							]);

						if(is_callable($closure))
							$closure($orm);
						$res = $orm->getDAL()->addSelect($joinTable.'.'.$currentEntityIdfield.' as __ormrelid')->groupBy(null)->get();
						foreach($entities as $entity) {
							$id = $entity->id;
							$filter = array_filter($res, function($result) use ($id) {
								return $id == $result['__ormrelid'];
							});
							$filter = array_values($filter);
							$mres = [];
							foreach($filter as $m)
								$mres[] = $this->hydrate($m, $relationDefinition);
							$entity->set($relationName, $mres);
						}
						break;
					default:
						throw new \Exception('Relation type '.$relation_type.' does not exist');
				}
			}
		}

		return $entities;
	}

	/**
	 * {@inheritDoc}
	*/
	public function query($sql, array $args=[]) {
		$this->tmp_dal = new \Asgard\Db\DAL($this->dataMapper->getDB());
		$this->tmp_dal->query($sql, $args);
		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function paginate($page=1, $per_page=10) {
		$this->page = $page;
		$this->per_page = $per_page;
		$this->offset(($this->page-1)*$this->per_page);
		$this->limit($this->per_page);

		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function getPaginator() {
		$page = $this->page !== null ? $this->page : 1;
		$per_page = $this->per_page !== null ? $this->per_page : 10;

		if($this->paginatorFactory)
			return $this->paginatorFactory->create($this->count(), $page, $per_page);
		else
			return new \Asgard\Common\Paginator($this->count(), $page, $per_page);
	}

	/**
	 * {@inheritDoc}
	*/
	public function with($with, \Closure $closure=null) {
		$this->with[$with] = $closure;
		return $this;
	}

	/**
	 * Set the tables.
	 *
	 * @param string $sql SQL query.
	 *
	 * @return string The modified SQL query.
	*/
	protected function replaceTable($sql) {
		$table = $this->getTable();
		$i18nTable = $this->getTranslationTable();
		preg_match_all('/(?<![\.a-zA-Z0-9-_`\(\)])([a-z_][a-zA-Z0-9-_]*)(?![\.`\(\)])/', $sql, $matches);
		foreach($matches[0] as $property) {
			if($this->definition->hasProperty($property))
				$table = $this->definition->property($property)->get('i18n') ? $i18nTable:$table;
			$sql = preg_replace('/(?<![\.a-zA-Z0-9-_`\(\)])('.$property.')(?![\.a-zA-Z0-9-_`\(\)])/', $table.'.$1', $sql);
		}

		return $sql;
	}

	/**
	 * Format the conditions before being used in SQL.
	 *
	 * @param array $conditions
	 *
	 * @return array FormInterfaceatted conditions.
	*/
	protected function processConditions(array $conditions) {
		foreach($cp=$conditions as $k=>$v) {
			if(is_numeric($k) || in_array(strtolower($k), ['and', 'or', 'not'])) {
				$newK = $k;
				if(is_array($v))
					$v = $this->processConditions($v);
				else
					$v = $this->replaceTable($v);
			}
			else
				$newK = $this->replaceTable($k);

			if($newK != $k)
				unset($conditions[$k]);
			$conditions[$newK] = $v;
		}

		return $conditions;
	}

	/**
	 * {@inheritDoc}
	*/
	public function where($conditions, $val=null) {
		if(!$conditions)
			return $this;
		if($val === null) {
			if(!is_array($conditions))
				$conditions = [$conditions];
			$this->where[] = $this->processConditions($conditions);
		}
		else
			$this->where[] = $this->processConditions([$conditions=>$val]);

		return $this;
	}

	public function having($conditions, $val=null) {
		if(!$conditions)
			return $this;
		if($val === null) {
			if(!is_array($conditions))
				$conditions = [$conditions];
			$this->having[] = $this->processConditions($conditions);
		}
		else
			$this->having[] = $this->processConditions([$conditions=>$val]);

		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function orderBy($orderBy) {
		$this->orderBy = $orderBy;
		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function delete() {
		$count = 0;
		while($entity = $this->next())
			$count += $this->dataMapper->destroy($entity);

		return $count;
	}

	/**
	 * {@inheritDoc}
	*/
	public function update(array $values) {
		while($entity = $this->next())
			$this->dataMapper->save($entity, $values);

		return $this;
	}

	/**
	 * {@inheritDoc}
	*/
	public function count($group_by=null) {
		return $this->getDAL()->count('*', $group_by);
	}

	/**
	 * {@inheritDoc}
	*/
	public function min($what, $group_by=null) {
		return $this->getDAL()->min($what, $group_by);
	}

	/**
	 * {@inheritDoc}
	*/
	public function max($what, $group_by=null) {
		return $this->getDAL()->max($what, $group_by);
	}

	/**
	 * {@inheritDoc}
	*/
	public function avg($what, $group_by=null) {
		return $this->getDAL()->avg($what, $group_by);
	}

	/**
	 * {@inheritDoc}
	*/
	public function sum($what, $group_by=null) {
		return $this->getDAL()->sum($what, $group_by);
	}

	/**
	 * {@inheritDoc}
	*/
	public function reset() {
		$this->where   = [];
		$this->with    = [];
		$this->orderBy = null;
		$this->limit   = null;
		$this->offset  = null;
		$this->join    = [];

		return $this;
	}

	/**
	 * Rewind query.
	 */
	public function rewind() {
		if(!$this->tmp_dal)
			$this->tmp_dal = $this->getDAL();
		$this->tmp_dal->rewind();
	}

	/**
	 * Return current entity.
	 * @return \Asgard\Entity\Entity
	 */
	public function current() {
		if(!($r = $this->tmp_dal->current()))
			return null;
		else
			return $this->hydrate($r);
	}

	/**
	 * Return currenty key.
	 * @return integer
	 */
	public function key() {
		return $this->tmp_dal->key();
	}

	/**
	 * Check if iteration is still valid.
	 * @return boolean
	 */
	public function valid() {
		return $this->tmp_dal->valid();
	}

	/**
	 * Add UNIONs.
	 * @param  array|static $dals
	 * @return DAL
	 */
	public function union($dals) {
		if(!is_array($dals))
			$dals = [$dals];
		$this->unions = array_merge($this->unions, $dals);

		return $this;
	}

	public function getHaving() {
		return $this->having;
	}

	public function getWith() {
		return $this->with;
	}

	public function getOrderBy() {
		return $this->orderBy;
	}

	public function getLimit() {
		return $this->limit;
	}

	public function getOffset() {
		return $this->offset;
	}

	public function getJoin() {
		return $this->join;
	}

	public function getPage() {
		return $this->page;
	}

	public function getPerPage() {
		return $this->per_page;
	}

	public function getLocale() {
		return $this->locale;
	}

	public function getPrefix() {
		return $this->prefix;
	}

	public function getScopes() {
		return $this->scopes;
	}

	public function getReversed() {
		return $this->reversed;
	}

	public function getSelects() {
		return $this->selects;
	}

	public function getUnions() {
		return $this->unions;
	}

	public function setDataMapper(DataMapperInterface $dataMapper) {
		$this->dataMapper = $dataMapper;
		return $this;
	}

	public function groupBy($groupBy) {
		$this->groupBy = $groupBy;
		return $this;
	}
}
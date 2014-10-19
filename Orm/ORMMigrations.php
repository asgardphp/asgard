<?php
namespace Asgard\Orm;

/**
 * Handle the migrations for the ORM.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ORMMigrations {
	/**
	 * MigrationManager dependency.
	 * @var \Asgard\Migration\MigrationManagerInterface
	 */
	protected $MigrationManager;
	/**
	 * DataMapper dependency.
	 * @var DataMapperInterface
	 */
	protected $dataMapper;

	/**
	 * Constructor.
	 * @param DataMapperInterface                          $dataMapper
	 * @param \Asgard\Migration\MigrationManagerInterface $MigrationManager
	 */
	public function __construct(DataMapperInterface $dataMapper, \Asgard\Migration\MigrationManagerInterface $MigrationManager=null) {
		$this->dataMapper = $dataMapper;
		$this->MigrationManager = $MigrationManager;
	}

	/**
	 * Automatically migrate given entity definitions.
	 * @param  array|\Asgard\Entity\Definition $definitions
	 * @param  \Asgard\Db\SchemaInterface                     $schema
	 */
	public function autoMigrate($definitions, \Asgard\Db\SchemaInterface $schema) {
		if(!is_array($definitions))
			$definitions = [$definitions];
		$this->processSchemas($this->getEntitiesSchemas($definitions), $schema);
	}

	/**
	 * Generate a migration from given entity definitions.
	 * @param  array|\Asgard\Entity\Definition $definitions
	 * @param  string                                $migrationName
	 * @return string                                name of migration
	 */
	public function generateMigration($definitions, $migrationName) {
		if(!is_array($definitions))
			$definitions = [$definitions];
		$entitiesSchemas = $this->getEntitiesSchemas($definitions);
		$sqlSchemas = $this->getSQLSchemas($this->dataMapper->getDB());
		$up = $this->buildMigration($entitiesSchemas, $sqlSchemas, false);
		$down = $this->buildMigration($sqlSchemas, $entitiesSchemas, true);

		if($up == '' && $down == '')
			return;

		return $this->MigrationManager->create($up, $down, $migrationName, '\Asgard\Migration\DBMigration');
	}

	/**
	 * Generate schemas of entities.
	 * @param  array $definitions
	 * @return array
	 */
	protected function getEntitiesSchemas(array $definitions) {
		$dataMapper = $this->dataMapper;
		$schemas = [];
		foreach($definitions as $definition) {
			$schema = [
				'columns' => [],
				'indexes' => [],
			];

			foreach($definition->properties() as $name=>$prop) {
				if($prop->get('orm'))
					$col = $prop->get('orm');
				else
					$col = [];

				#relations
				if($prop->get('type') == 'entity') {
					$relation = $dataMapper->relation($definition, $name);
					#relations with one entity
					if(!$relation->get('many')) {
						$schema['columns'][$relation->getLink()] = [
							'type'           => 'int(11)',
							'nullable'       => true,
							'auto_increment' => false,
							'default'        => null,
							'key'            => null,
						];
						if($relation->isPolymorphic()) {
							$schema['columns'][$relation->getLinkType()] = [
								'type'           => 'varchar(50)',
								'nullable'       => true,
								'auto_increment' => false,
								'default'        => null,
								'key'            => null,
							];
						}
					}
					#HMABT relations
					elseif($relation->type() == 'HMABT' && !$relation->isPolymorphic()) {
						$table_name = $relation->getAssociationTable();
						#if table was not already created by the opposite entity
						if(!isset($schemas[$table_name])) {
							$arr = [
								$relation->getLinkB() => [
									'type'           => 'int(11)',
									'nullable'       => true,
									'auto_increment' => false,
									'default'        => null,
									'key'            => null,
								],
								$relation->getLinkA() => [
									'type'           => 'int(11)',
									'nullable'       => true,
									'auto_increment' => false,
									'default'        => null,
									'key'            => null,
								],
							];
							$schemas[$table_name] = [
								'columns' => $arr,
								'indexes' => [],
							];
						}
						if($relation->reverse()->isPolymorphic()) {
							$schemas[$table_name]['columns'][$relation->reverse()->getLinkType()] = [
								'type'           => 'varchar(50)',
								'nullable'       => true,
								'auto_increment' => false,
								'default'        => null,
								'key'            => null,
							];
						}
						#sortable
						if($relation->get('sortable')) {
							$schemas[$table_name]['columns'][$relation->getPositionField()] = [
								'type'           => 'int(11)',
								'nullable'       => true,
								'auto_increment' => false,
								'default'        => null,
								'key'            => null,
							];
						}
					}
					continue;
				}

				if($prop->get('many'))
					$col['type'] = 'blob';
				elseif(!isset($prop->get('orm')['type'])) {
					if(method_exists($prop, 'getSQLType'))
						$col['type'] = $prop->getSQLType();
					else
						throw new \Exception('Cannot convert '.$prop->type.' type');
				}

				if(!isset($col['default']))
					$col['default'] = false;
				if(!isset($col['nullable']))
					$col['nullable'] = true;
				if(!isset($col['key']))
					$col['key'] = null;
				if(!isset($col['auto_increment']))
					$col['auto_increment'] = false;
				$col['position'] = $prop->getPosition('position');

				if($prop->get('i18n')) {
					if(!isset($schemas[$dataMapper->getTranslationTable($definition)])) {
						$schemas[$dataMapper->getTranslationTable($definition)]['columns'] = [
							'id' => [
								'type'           => 'int(11)',
								'nullable'       => false,
								'auto_increment' => false,
								'default'        => null,
								'key'            => null,
								'position'       => 0,
							],
							'locale' => [
								'type'           => 'varchar(50)',
								'nullable'       => false,
								'auto_increment' => false,
								'default'        => null,
								'key'            => null,
								'position'       => 1,
							],
						];
					}
					$schemas[$dataMapper->getTranslationTable($definition)]['columns'][$name] = $col;
				}
				else
					$schema['columns'][$name] = $col;
			}

			if(isset($definition->get('orm')['indexes'])) {
				foreach($definition->get('orm')['indexes'] as $index) {
					if(count($index['columns']) === 1) {
						$column = $index['columns'][0];
						$schema[$column]['key'] = $index['type'];
						#todo column index length
					}
					else {
						foreach($index['columns'] as $k=>$v) {
							if(!isset($index['lengths'][$k])) {
								if($schema['columns'][$v]['type'] === 'text')
									$index['lengths'][$k] = '255';
								else
									$index['lengths'][$k] = null;
							}
						}

						$index['type'] = strtoupper($index['type']);

						if($index['type'] == 'PRIMARY')
							$indexName = 'PRIMARY';
						else
							$indexName = $_indexName = implode('_', $index['columns']);
						$i = 1;
						while(isset($schema['indexes'][$indexName]))
							$indexName = $_indexName.$i++;
						$schema['indexes'][$indexName] = $index;
					}
				}
			}

			$schemas[$dataMapper->getTable($definition)] = $schema;
		}

		foreach($schemas as &$schema) {
			uasort($schema['columns'], function($a, $b) {
				if(!isset($a['position']))
					return 1;
				if(!isset($b['position']))
					return -1;
				if($a['position'] < $b['position'])
					return -1;
				return 1;
			});

			$i = 0;
			foreach($schema['columns'] as $k=>$col)
				$schema['columns'][$k]['position'] = $i++;
		}

		return $schemas;
	}

	/**
	 * Process the schemas.
	 * @param  array             $schemas
	 * @param  \Asgard\Db\SchemaInterface $s
	 */
	protected function processSchemas(array $schemas, \Asgard\Db\SchemaInterface $s) {
		foreach($schemas as $tableName=>$table) {
			$cols = $table['columns'];
			$indexes = isset($table['indexes']) ? $table['indexes']:[];

			$s->create($tableName, function($table) use($cols, $indexes) {
				foreach($cols as $col=>$params) {
					$c = $table->add($col, $params['type']);
					if($params['key'] === 'PRIMARY')
						$c->primary();
					elseif($params['key'] === 'UNIQUE')
						$c->unique();
					if($params['nullable'])
						$c->nullable();
					if($params['auto_increment'])
						$c->autoincrement();
					if($params['default'] !== null)
						$c->def($params['default']);
				}

				foreach($indexes as $indexName=>$index) {
					if($index['type'] == 'PRIMARY')
						$table->setPrimary($index, $indexName);
					else {
						$method = 'add'.ucfirst(strtolower($index['type']));
						$table->{$method}($index, $indexName);
					}
				}
			});
		}
	}

	/**
	 * Fetch the SQL schemas
	 * @param  \Asgard\Db\DBInterface $db
	 * @return array
	 */
	protected function getSQLSchemas(\Asgard\Db\DBInterface $db) {
		$tables = [];
		foreach($db->query('SHOW TABLES')->all() as $v) {
			$table = array_values($v)[0];

			$description = [];
			$pos = 0;
			foreach($db->query('Describe `'.$table.'`')->all() as $row) {
				$params = [];
				$name = $row['Field'];
				$params['type'] = $row['Type'];
				$params['nullable'] = ($row['Null'] == 'YES');
				if($row['Key'] == 'PRI')
					$params['key'] = 'PRIMARY';
				elseif($row['Key'] == 'UNI')
					$params['key'] = 'UNIQUE';
				else
					$params['key'] = null;
				$params['default'] = $row['Default'];
				$params['auto_increment'] = (strpos($row['Extra'], 'auto_increment') !== false);
				$params['position'] = $pos++;

				$description[$name] = $params;
			}

			$indexes = [];
			foreach ($db->query('SHOW INDEX FROM `'.$table.'`')->all() as $row) {
				$indexes[$row['Key_name']]['type'] = (
					$row['Key_name'] == 'PRIMARY' ? 'PRIMARY' : 
						($row['Index_type'] == 'FULLTEXT' ? 'FULLTEXT' : 
							($row['Non_unique'] ? 'INDEX' : 'UNIQUE')
						)
				);
				$indexes[$row['Key_name']]['columns'][] = $row['Column_name'];
				$indexes[$row['Key_name']]['lengths'][] = $row['Sub_part'];
			}

			foreach($indexes as $indexName=>$index) {
				if(count($index['columns']) === 1 && ($index['type'] === 'PRIMARY' || $index['type'] === 'UNIQUE')) {
					$column = $index['columns'][0];
					$descriptions[$column]['key'] = $index['type'];
					#todo column index length
					
					unset($indexes[$indexName]);
				}
			}

			$tables[$table] = [
				'columns' => $description,
				'indexes' => $indexes
			];
		}

		return $tables;
	}

	/**
	 * Build the migration code by comparing the new schemas to the old ones.
	 * @param  array   $newSchemas
	 * @param  array   $oldSchemas
	 * @param  boolean $drop       true to drop the old tables
	 * @return string  migration code
	 */
	protected function buildMigration($newSchemas, $oldSchemas, $drop) {
		$res = '';
		foreach($newSchemas as $table=>$newSchema) {
			if(!in_array($table, array_keys($oldSchemas))) {
				if(!$drop)
					$res .= $this->createTable($table, $newSchema);
				continue;
			}
			$newColumns = $newSchema['columns'];
			$newIndexes = $newSchema['indexes'];

			$oldSchema = $oldSchemas[$table];
			$oldColumns = $oldSchema['columns'];
			$oldIndexes = $oldSchema['indexes'];

			#Columns
			$colsRes = '';
			foreach(array_keys($newColumns) as $k=>$col) {
				if(!in_array($col, array_keys($oldColumns)))
					$colsRes .= $this->createColumn($col, $newColumns[$col]);
				else {
					$diff = array_diff_assoc($newColumns[$col], $oldColumns[$col]);
					if(isset($diff['position'])) {
						if($k === 0)
							$diff['after'] = false;
						else
							$diff['after'] = array_keys($newColumns)[$k-1];
						unset($diff['position']);
					}
					if($diff)
						$colsRes .=  $this->updateColumn($col, $diff);
				}
			}
			foreach($oldColumns as $col=>$params) {
				if(!in_array($col, array_keys($newColumns)))
					$colsRes .= $this->dropColumn($col);
			}

			#Indexes
			foreach($newIndexes as $indexName=>$index) {
				#if the index or its name is not in the old indexes
				if(!in_array($index, $oldIndexes) || !in_array($indexName, array_keys($oldIndexes)))
					$colsRes .=  $this->createIndex($indexName, $index);
			}
			foreach($oldIndexes as $indexName=>$index) {
				#if the index or its name is only in the old indexes
				if(!in_array($index, $newIndexes) || !in_array($indexName, array_keys($newIndexes)))
					$colsRes .=  $this->dropIndex($indexName);
			}

			if($colsRes)
				$res .= "\$this->container['schema']->table('$table', function(\$table) {".$colsRes."\n});\n\n";
		}
		if($drop) {
			foreach($oldSchemas as $table=>$oldSchema) {
				if(!in_array($table, array_keys($newSchemas))) {
					$res .= $this->dropTable($table);
					continue;
				}
			}
		}

		return trim($res, "\n");
	}

	/**
	 * Generate code to drop a table.
	 * @param  string $table
	 * @return string
	 */
	protected function dropTable($table) {
		return "\$this->container['schema']->drop('$table');\n\n";
	}

	/**
	 * Generate code to drop a column.
	 * @param  string $col
	 * @return string
	 */
	protected function dropColumn($col) {
		return "\n\t\$table->drop('$col');";
	}

	/**
	 * Generate code to create a table.
	 * @param  string $table
	 * @param  array $cols
	 * @return string
	 */
	protected function createTable($tableName, $table) {
		$res = "\$this->container['schema']->create('$tableName', function(\$table) {";

		$cols = $table['columns'];
		foreach($cols as $col=>$params)
			$res .= $this->createColumn($col, $params);

		$indexes = isset($table['indexes']) ? $table['indexes']:[];
		foreach($indexes as $indexName=>$index)
			$res .= $this->createIndex($indexName, $index);

		$res .= "\n});\n\n";

		return $res;
	}

	/**
	 * Generate the code to update a column.
	 * @param  string $col    column name
	 * @param  array  $params column parameters
	 * @return string
	 */
	protected function updateColumn($col, $params) {
		$res = "\n\t\$table->col('$col')";
		if(array_key_exists('type', $params))
			$res .= "\n		->type('$params[type]')";
		if(array_key_exists('after', $params)) {
			if($params['after'] === false)
				$res .= "\n		->first()";
			else
				$res .= "\n		->after('$params[after]')";
		}
		if(array_key_exists('nullable', $params)) {
			if($params['nullable'])
				$res .= "\n		->nullable()";
			else
				$res .= "\n		->NotNullable()";
		}
		if(array_key_exists('key', $params)) {
			if($params['key'] === 'PRIMARY')
				$res .= "\n		->primary()";
			elseif($params['key'] === 'UNIQUE')
				$res .= "\n		->unique()";
			elseif($params['key'] === null)
				$res .= "\n		->dropIndex()";
		}
		if(array_key_exists('auto_increment', $params)) {
			if($params['auto_increment'])
				$res .= "\n		->autoincrement()";
			else
				$res .= "\n		->notAutoincrement()";
		}
		if(array_key_exists('default', $params)) {
			if($params['default'] === false)
				$res .= "\n		->def(false)";
			else
				$res .= "\n		->def('$params[default]')";
		}
		$res .= ';';

		return $res;
	}

	/**
	 * Generate the code to create a column.
	 * @param  string $col    column name
	 * @param  array  $params column parameters
	 * @return string
	 */
	protected function createColumn($col, $params) {
		$res = "\n\t\$table->add('$col', '$params[type]')";
		if($params['nullable'])
			$res .= "\n		->nullable()";
		if($params['key'] === 'PRIMARY')
			$res .= "\n		->primary()";
		elseif($params['key'] === 'UNIQUE')
			$res .= "\n		->unique()";
		if($params['auto_increment'])
			$res .= "\n		->autoincrement()";
		if($params['default'])
			$res .= "\n		->def('$params[default]')";
		$res .= ';';

		return $res;
	}

	/**
	 * Build an index.
	 * @param  string $indexName
	 * @param  array  $index
	 * @return string
	 */
	protected function createIndex($indexName, $index) {
		if($index['type'] == 'PRIMARY')
			$res = "\n\t\$table->setPrimary(";
		else
			$res = "\n\t\$table->add".ucfirst(strtolower($index['type']))."(";
		unset($index['type']);
		$res .= $this->outputPHP($index, 1);
		$res .= ', \''.$indexName.'\');';

		return $res;
	}

	/**
	 * Drop an index.
	 * @param  string $indexName
	 * @return string
	 */
	protected function dropIndex($indexName) {
		return "\n\t\$table->dropIndex('$indexName');";
	}

	/**
	 * Format PHP variables to string.
	 * @param  mixed $v
	 * @return string
	 */
	public function outputPHP($v, $tabs=0, $line=false) {
		$r = '';

		if($line)
			$r .= "\n".str_repeat("\t", $tabs);

		if(is_array($v)) {
			$r .= '[';
			if($v === array_values($v)) {
				foreach($v as $_v)
					$r .= $this->outputPHP($_v, $tabs+1, true).",";
			}
			else {
				foreach($v as $_k=>$_v)
					$r .= $this->outputPHP($_k, $tabs+1, true).' => '.$this->outputPHP($_v, $tabs+1).",";
			}
			$r .= "\n".str_repeat("\t", $tabs).']';

			return $r;
		}
		else
			return $r.var_export($v, true);
	}
}
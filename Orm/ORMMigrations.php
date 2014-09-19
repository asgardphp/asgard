<?php
namespace Asgard\Orm;

/**
 * Handle the migrations for the ORM.
 */
class ORMMigrations {
	/**
	 * MigrationsManager dependency.
	 * @var \Asgard\Migration\MigrationsManager
	 */
	protected $migrationsManager;
	/**
	 * DataMapper dependency.
	 * @var DataMapper
	 */
	protected $dataMapper;

	/**
	 * Constructor.
	 * @param \Asgard\Migration\MigrationsManager $migrationsManager
	 */
	public function __construct($dataMapper, $migrationsManager=null) {
		$this->dataMapper = $dataMapper;
		$this->migrationsManager = $migrationsManager;
	}

	/**
	 * Automatically migrate entities tables.
	 * @param  \Asgard\Entity\EntitiesManager $entitiesManager
	 * @param  \Asgard\Db\Schema              $schema
	 */
	public function autoMigrate(\Asgard\Entity\EntitiesManager $entitiesManager, \Asgard\Db\Schema $schema) {
		$this->doAutoMigrate($entitiesManager->getDefinitions(), $schema);
	}

	/**
	 * Generate a migration from entities.
	 * @param  \Asgard\Entity\EntitiesManager $entitiesManager
	 * @param  string     $migrationName
	 * @return string     name of migration
	 */
	public function generateMigration(\Asgard\Entity\EntitiesManager $entitiesManager, $migrationName) {
		return $this->doGenerateMigration($entitiesManager->getDefinitions(), $migrationName);
	}

	/**
	 * Automatically migrate given entity definitions.
	 * @param  array|\Asgard\Entity\EntityDefinition $definitions
	 * @param  \Asgard\Db\Schema                     schema
	 */
	public function doAutoMigrate($definitions, \Asgard\Db\Schema $schema) {
		if(!is_array($definitions))
			$definitions = [$definitions];
		$this->processSchemas($this->getEntitiesSchemas($definitions), $schema);
	}

	/**
	 * Generate a migration from given entity definitions.
	 * @param  array|\Asgard\Entity\EntityDefinition $definitions
	 * @param  string                                $migrationName
	 * @return string                                name of migration
	 */
	public function doGenerateMigration($definitions, $migrationName) {
		if(!is_array($definitions))
			$definitions = [$definitions];
		$entitiesSchemas = $this->getEntitiesSchemas($definitions);
		$sqlSchemas = $this->getSQLSchemas($this->dataMapper->getDB());
		$up = $this->buildMigration($entitiesSchemas, $sqlSchemas, false);
		$down = $this->buildMigration($sqlSchemas, $entitiesSchemas, true);
		return $this->migrationsManager->create($up, $down, $migrationName, '\Asgard\Migration\DBMigration');
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
			$schema = [];
			
			foreach($definition->properties() as $name=>$prop) {
				if(!$prop->orm)
					$col = [];
				else
					$col = $prop->orm;

				#relations
				if($prop->get('type') == 'entity') {
					$relation = $dataMapper->getRelation($definition, $name);
					#relations with one entity
					if(!$relation['many']) {
						$schema[$relation->getLink()] = [
							'type'           => 'int(11)',
							'nullable'       => true,
							'auto_increment' => false,
							'default'        => null,
							'key'            => null,
						];
					}
					#HMABT relations
					elseif($relation->type() == 'HMABT') {
						$table_name = $relation->getTable();
						#if table was not already created by the opposite entity
						if(!isset($schemas[$table_name])) {
							$arr = [
								$relation->getLinkA() => [
									'type'           => 'int(11)',
									'nullable'       => true,
									'auto_increment' => false,
									'default'        => null,
									'key'            => null,
								],
								$relation->getLinkB() => [
									'type'           => 'int(11)',
									'nullable'       => true,
									'auto_increment' => false,
									'default'        => null,
									'key'            => null,
								],
							];
							$schemas[$table_name] = $arr;
						}
					}
					continue;
				}

				if($prop->get('many'))
					$col['type'] = 'blob';
				elseif(!isset($prop->orm['type'])) {
					if(method_exists($prop, 'getSQLType'))
						$col['type'] = $prop->getSQLType();
					else
						throw new \Exception('Cannot convert '.$prop->type.' type');
				}

				if(!isset($prop->orm['default']))
					$col['default'] = false;
				if(!isset($prop->orm['nullable']))
					$col['nullable'] = true;
				if(!isset($prop->orm['key']))
					$col['key'] = '';
				if(!isset($prop->orm['auto_increment']))
					$col['auto_increment'] = false;
				$col['position'] = $prop->params['position'];

				if($prop->i18n) {
					if(!isset($schemas[$dataMapper->getTable($definition->getClass()).'_translation'])) {
						$schemas[$dataMapper->getTable($definition->getClass()).'_translation'] = [
							'id' => [
								'type'           => 'int(11)',
								'nullable'       => false,
								'auto_increment' => false,
								'default'        => null,
								'key'            => null,
							],
							'locale' => [
								'type'           => 'varchar(50)',
								'nullable'       => false,
								'auto_increment' => false,
								'default'        => null,
								'key'            => null,
							],
						];
					}
					$schemas[$dataMapper->getTable($definition->getClass()).'_translation'][$name] = $col; #todo replace by getTranslationTable
				}
				else
					$schema[$name] = $col;
			}

			uasort($schema, function($a, $b) {
				if(!isset($a['position']))
					return 1;
				if(!isset($b['position']))
					return -1;
				if($a['position'] < $b['position'])
					return -1;
				return 1;
			});

			$i = 0;
			foreach($schema as $k=>$col)
				$schema[$k]['position'] = $i++;

			$schemas[$dataMapper->getTable($definition->getClass())] = $schema;
		}

		return $schemas;
	}

	/**
	 * Process the schemas.
	 * @param  array             $schemas
	 * @param  \Asgard\Db\Schema $s
	 */
	protected function processSchemas(array $schemas, \Asgard\Db\Schema $s) {
		foreach($schemas as $tableName=>$cols) {
			$s->create($tableName, function($table) use($cols) {
				foreach($cols as $col=>$params) {
					$c = $table->add($col, $params['type']);
					if($params['nullable'])
						$c->nullable();
					if($params['auto_increment'])
						$c->autoincrement();
					if($params['default'] !== null)
						$c->def($params['default']);
					if($params['key'] == 'PRI')
						$c->primary();
					elseif($params['key'] == 'UNI')
						$c->unique();
					elseif($params['key'] == 'MUL')
						$c->index();
				}
			});
		}
	}

	/**
	 * Fetch the SQL schemas
	 * @param  \Asgard\Db\DB $db
	 * @return array
	 */
	protected function getSQLSchemas(\Asgard\Db\DB $db) {
		$tables = [];
		foreach($db->query('SHOW TABLES')->all() as $v) {
			$table = array_values($v)[0];
			$description = $db->query('Describe `'.$table.'`')->all();
			$pos = 0;
			foreach($description as $k=>$v) {
				$params = [];
				$name = $v['Field'];
				$params['type'] = $v['Type'];
				$params['nullable'] = ($v['Null'] == 'YES');
				$params['key'] = $v['Key'];
				$params['default'] = $v['Default'];
				$params['auto_increment'] = (strpos($v['Extra'], 'auto_increment') !== false);
				$params['position'] = $pos++;
				
				$description[$name] = $params;
				unset($description[$k]);
			}
			$tables[$table] = $description;
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
			$oldSchema = $oldSchemas[$table];
			$colsRes = '';
			foreach(array_keys($newSchema) as $k=>$col) {
				if(!in_array($col, array_keys($oldSchema)))
					$colsRes .=  $this->createColumn($col, $newSchema[$col]);
				else {
					$diff = array_diff_assoc($newSchema[$col], $oldSchema[$col]);
					if(isset($diff['position'])) {
						if($k === 0)
							$diff['after'] = false;
						else
							$diff['after'] = array_keys($newSchema)[$k-1];
						unset($diff['position']);
					}
					if($diff)
						$colsRes .=  $this->updateColumn($col, $diff);
				}
			}
			foreach($oldSchema as $col=>$params) {
				if(!in_array($col, array_keys($newSchema)))
					$colsRes .= $this->dropColumn($col);
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
	protected function createTable($table, $cols) {
		$res = "\$this->container['schema']->create('$table', function(\$table) {";
		foreach($cols as $col=>$params)
			$res .= $this->createColumn($col, $params);
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
		if(isset($params['type']))
			$res .= "\n		->type('$params[type]')";
		if(isset($params['after'])) {
			if($params['after'] === false)
				$res .= "\n		->first()";
			else
				$res .= "\n		->after('$params[after]')";
		}
		if(isset($params['nullable'])) {
			if($params['nullable'])
				$res .= "\n		->nullable()";
			else
				$res .= "\n		->NotNullable()";
		}
		if(isset($params['key'])) {
			if($params['key'] == 'PRI')
				$res .= "\n		->primary()";
			elseif($params['key']=='UNI')
				$res .= "\n		->unique()";
			elseif($params['key']=='MUL')
				$res .= "\n		->index()";
			else
				$res .= "\n		->dropIndex()";
		}
		if(isset($params['auto_increment'])) {
			if($params['auto_increment'])
				$res .= "\n		->autoincrement()";
			else
				$res .= "\n		->notAutoincrement()";
		}
		if(isset($params['default'])) {
			if($params['default'] === false)
				$res .= "\n		->def(false)";
			else
				$res .= "\n		->def('$params[default]')";
		}
		$res .= ";";
		
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
		if($params['key'] == 'PRI')
			$res .= "\n		->primary()";
		if($params['key'] == 'UNI')
			$res .= "\n		->unique()";
		if($params['key'] == 'MUL')
			$res .= "\n		->index()";
		if($params['auto_increment'])
			$res .= "\n		->autoincrement()";
		if($params['default'])
			$res .= "\n		->def('$params[default]')";
		$res .= ";";
		
		return $res;
	}
}
<?php
namespace Asgard\Orm;

class ORMMigrations {
	protected $migrationsManager;

	public function __construct($migrationsManager=null) {
		$this->migrationsManager = $migrationsManager;
	}

	public function autoMigrate($entities, \Asgard\Db\Schema $s) {
		if(!is_array($entities))
			$entities = array($entities);
		$this->processSchemas($this->getEntitiesSchemas($entities), $s);
	}

	public function generateMigration(array $entities, $migrationName, \Asgard\Db\DB $db) {
		if(!is_array($entities))
			$entities = array($entities);
		$entitiesSchemas = $this->getEntitiesSchemas($entities);
		$sqlSchemas = $this->getSQLSchemas($db);
		$up = $this->buildMigration($entitiesSchemas, $sqlSchemas, false);
		$down = $this->buildMigration($sqlSchemas, $entitiesSchemas, true);
		return $this->migrationsManager->create($up, $down, $migrationName);
	}

	protected function getEntitiesSchemas(array $entities) {
		$schemas = array();
		foreach($entities as $class) {
			$schema = array();
			
			foreach($class::getDefinition()->properties() as $name=>$prop) {
				if(!$prop->orm)
					$col = array();
				else
					$col = $prop->orm;
				if($prop->get('multiple'))
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
					if(!isset($schemas[$class::getTable().'_translation'])) {
						$schemas[$class::getTable().'_translation'] = array(
							'id' => array(
								'type'	=>	'int(11)',
								'nullable'	=>	false,
								'auto_increment'	=>	false,
								'default'	=>	null,
								'key'	=>	null,
							),
							'locale' => array(
								'type'	=>	'varchar(50)',
								'nullable'	=>	false,
								'auto_increment'	=>	false,
								'default'	=>	null,
								'key'	=>	null,
							),
						);
					}
					$schemas[$class::getTable().'_translation'][$name] = $col;
				}
				else
					$schema[$name] = $col;
			}

			foreach($class::getDefinition()->relations as $name=>$rel) {
				if($rel->type() == 'HMABT') {
					$table_name = $rel->getTable();
					if(!isset($schemas[$table_name])) {
						$arr = array(
							$rel->getLinkA()	=>	array(
								'type'	=>	'int(11)',
								'nullable'	=>	false,
								'auto_increment'	=>	false,
								'default'	=>	null,
								'key'	=>	null,
							),
							$rel->getLinkB()	=>	array(
								'type'	=>	'int(11)',
								'nullable'	=>	false,
								'auto_increment'	=>	false,
								'default'	=>	null,
								'key'	=>	null,
							),
						);
						$schemas[$table_name] = $arr;
					}
					#todo
					if($rel['sortable'])
						$schemas[$table_name][$rel['sortable']] = array(
							'type'	=>	'int(11)',
							'nullable'	=>	false,
							'auto_increment'	=>	false,
							'default'	=>	null,
							'key'	=>	null,
						);
				}
			}

			uasort($schema, function($a, $b) {
				if(!isset($a['position']))
					return -1;
				if(!isset($b['position']))
					return 1;
				if($a['position'] < $b['position'])
					return -1;
				return 1;
			});

			$i = 0;
			foreach($schema as $k=>$col)
				$schema[$k]['position'] = $i++;

			$schemas[$class::getTable()] = $schema;
		}

		return $schemas;
	}

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

	protected function getSQLSchemas(\Asgard\Db\DB $db) {
		$tables = array();
		foreach($db->query('SHOW TABLES')->all() as $v) {
			$table = array_values($v)[0];
			$description = $db->query('Describe `'.$table.'`')->all();
			$pos = 0;
			foreach($description as $k=>$v) {
				$params = array();
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
				$res .= "\$this->app['schema']->table('$table', function(\$table) {".$colsRes."\n});\n\n";
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

	protected function dropTable($table) {
		return "\$this->app['schema']->drop('$table');\n\n";
	}

	protected function dropColumn($col) {
		return "\n\t\$table->drop('$col');";
	}

	protected function createTable($table, $cols) {
		$res = "\$this->app['schema']->create('$table', function(\$table) {";
		foreach($cols as $col=>$params)
			$res .= "\t".$this->createColumn($col, $params);
		$res .= "\n});\n\n";
		
		return $res;
	}

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
		$res .= ";";
		
		return $res;
	}

	protected function createColumn($col, $params) {
		$res = '';
		$res = "\n\t\$table->add('$col', '$params[type]')";
		if($params['nullable'])
			$res .= "\n		->nullable()";
		if($params['auto_increment'])
			$res .= "\n		->autoincrement()";
		if($params['default'])
			$res .= "\n		->def('$params[default]')";
		if($params['key'] == 'PRI')
			$res .= "\n		->primary()";
		if($params['key'] == 'UNI')
			$res .= "\n		->unique()";
		if($params['key'] == 'MUL')
			$res .= "\n		->index()";
		$res .= ";";
		
		return $res;
	}
}
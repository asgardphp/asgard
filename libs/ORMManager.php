<?php
namespace Coxis\ORM\Libs;

class ORMManager {
	public static function autobuild() {
		list($up) = static::_diff();

		foreach($up as $one)
			eval($one);
	}

	public static function loadModelFixtures($file) {
		$yaml = new \Symfony\Component\Yaml\Parser();
		$raw = $yaml->parse(file_get_contents($file));

		$models = array();

		foreach($raw as $class => $raw_models) {
			foreach($raw_models as $name => $raw_model) {
				foreach($raw_model as $k=>$V)
					if(!$class::hasProperty($k))
						unset($raw_model[$k]);

				$model = new $class;
				$model->set($raw_model, 'all');
				$model->save(array(), true);
				$model->save(null, true);
				$models[$class][$name] = $model;
			}
		}

		foreach($models as $class => $classmodels) {
			foreach($classmodels as $name => $model) {
				foreach($class::getDefinition()->relations as $relation => $params) {
					if(!isset($raw[$class][$name][$relation]))
						continue;
					$relationFixtures = $raw[$class][$name][$relation];

					$rel = $class::getDefinition()->relations[$relation];
					$relationClass = $rel['model'];

					if(is_array($relationFixtures))
						foreach($relationFixtures as $v)
							$relationFixtures[$k] = $models[$relationClass][$v]->id;
					else
						$relationFixtures = $models[$relationClass][$relationFixtures]->id;

					$model->save(array($relation => $relationFixtures), true);
				}
			}
		}
	}

	public static function diff($verbose=false) {
		list($up, $down) = static::_diff();

		if(isset($request[0]))
			$filename = $request[0];
		else
			$filename = 'diff';
		static::addMigration($up, $down, $filename, $verbose);
	}

	protected static function _diff() {
		$bundles = BundlesManager::inst()->getBundlesPath();
		
		foreach($bundles as $bundle)
			foreach(glob($bundle.'/models/*.php') as $model)
				\Importer::loadClassFile($model);

		$newSchemas = array();
		$oldSchemas = array();
		$tables = DB::query('SHOW TABLES')->all();
		foreach($tables as $k=>$v) {
			$table = \Coxis\Utils\Tools::get(array_values($v), 0);
			$oldSchemas[$table] = static::tableSchema($table);
		}

		foreach(get_declared_classes() as $class) {
			if(is_subclass_of($class, 'Coxis\Core\Model')) {
				if($class == 'Coxis\Core\Model')
					continue;
				$reflection = new \ReflectionClass($class);
				if($reflection->isAbstract())
					continue;
				
				$schema = array();
				
				foreach($class::getDefinition()->properties() as $name=>$prop) {
					if(!$prop->orm)
						$neworm = array();
					else
						$neworm = $prop->orm;
					if(!isset($prop->orm['type'])) {
						if(method_exists($prop, 'getSQLType'))
							$neworm['type'] = $prop->getSQLType();
						else {
							#match type
							#and length
							switch($prop->type) {
								// case 'datetime':
								// 	$neworm['type'] = 'datetime';
								// 	break;
								default:
									throw new \Exception('Cannot convert '.$prop->type.' type');
							}
						}
					}

					if(!isset($prop->orm['default']))
						$neworm['default'] = false;
					if(!isset($prop->orm['nullable']))
						$neworm['nullable'] = true;
					if(!isset($prop->orm['key']))
						$neworm['key'] = '';
					if(!isset($prop->orm['auto_increment']))
						$neworm['auto_increment'] = false;
					$neworm['position'] = $prop->params['position'];

					if($prop->i18n) {
						if(!isset($newSchemas[$class::getTable().'_translation'])) {
							$newSchemas[$class::getTable().'_translation'] = array(
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
						$newSchemas[$class::getTable().'_translation'][$prop->getName()] = $neworm;
					}
					else
						$schema[$name] = $prop->orm = $neworm;
				}

				foreach($class::getDefinition()->relations as $name=>$rel) {
					if($rel['type'] == 'HMABT') {
						$table_name = $rel['join_table'];
						if(!isset($newSchemas[$table_name])) {
							$arr = array(
								$rel['link_a']	=>	array(
									'type'	=>	'int(11)',
									'nullable'	=>	false,
									'auto_increment'	=>	false,
									'default'	=>	null,
									'key'	=>	null,
								),
								$rel['link_b']	=>	array(
									'type'	=>	'int(11)',
									'nullable'	=>	false,
									'auto_increment'	=>	false,
									'default'	=>	null,
									'key'	=>	null,
								),
							);
							$newSchemas[$table_name] = $arr;
						}
						if($rel['sortable'])
							$newSchemas[$table_name][$rel['sortable']] = array(
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

				$newSchemas[$class::getTable()] = $schema;
			}
		}
		
		$oldSchemas = array_filter($oldSchemas);

		$up = static::diffBetween($newSchemas, $oldSchemas);
		$down = static::diffBetween($oldSchemas, $newSchemas, true);

		return array($up, $down);
	}
	
	private static function diffBetween($newSchemas, $oldSchemas, $down=false) {
		$migrations = array();
		$migration = '';
		foreach($newSchemas as $class=>$schema) {
			$table = $class;
			if(!in_array($class, array_keys($oldSchemas))) {
				if(!$down) {
					$migration = static::buildTableFor($class, $newSchemas[$class]);
					$migrations[] = $migration;
				}
				continue;
			}
			$tableSchema = $oldSchemas[$class];
			$schema = $newSchemas[$class];
			$oldcols = array_diff(array_keys($tableSchema), array_keys($schema));
			$newcols = array_diff(array_keys($schema), array_keys($tableSchema));
			$colsmigration = '';
			foreach(array_keys($schema) as $col) {
				if(!in_array($col, array_keys($tableSchema))) {
					$colsmigration .=  static::buildColumnFor($table, $col, $schema[$col]);
				}
				else {
					$diff = array_diff_assoc($schema[$col], $tableSchema[$col]);
					unset($diff['position']);
					if($diff)
						$colsmigration .=  static::updateColumn($table, $col, $diff);
				}
			}
			foreach(array_keys($tableSchema) as $col) {
				if(!in_array($col, array_keys($schema))) {
					$colsmigration .=  static::dropColumn($col);
				}
			}
			if($colsmigration) {
				$migration = "Schema::table('$table', function(\$table) {".$colsmigration."\n});";
				$migrations[] = $migration;
			}
		}
		return $migrations;
	}

	public static function migrate($verbose = false) {
		$migrations = static::todo();

		if(!sizeof($migrations))
			return;
		
		DB::beginTransaction();
		foreach($migrations as $migration)
			$last = static::runMigration($migration, $verbose);
		DB::commit();
			
		file_put_contents('migrations/migrate_version', $last);
	}
	
	private static function addMigration($up, $down, $filename='diff', $verbose=false) {
		if(!$up)
			return;
		if(!is_array($up))
			$up = array($up);
		foreach($up as $k=>$v)
			$up[$k] = static::tabs($v, 2);
		if(!is_array($down))
			$down = array($down);
		foreach($down as $k=>$v)
			$down[$k] = static::tabs($v, 2);
			
		$i = static::current()+1;
			
		$migration = '<?php
class '.$filename.'_'.$i.' {
	public static function up() {
		'.implode("\n\n\t\t", $up).'
	}
	
	public static function down() {
		'.implode("\n\n\t\t", $down)."
	}
}";
		\Coxis\Utils\FileManager::mkdir('migrations');
		file_put_contents('migrations/'.$i.'_'.$filename.'.php', $migration);

		if($verbose)
			echo 'New migration: '.$i.'_'.$filename;
	}
	
	private static function tabs($str, $tabs) {
		return implode("\n".str_repeat("\t", $tabs), explode("\n", $str));
	}
	
	private static function dropColumn($col) {
		$migration = "\n\t\$table->drop('$col');";
		return $migration;
	}
	
	private static function updateColumn($table, $col, $diff) {
		$migration = "\n\t\$table->col('$col')";
		if(isset($diff['type']))
			$migration .= "\n		->type('$diff[type]')";
		if(isset($diff['nullable']))
			if($diff['nullable'])
				$migration .= "\n		->nullable()";
			else
				$migration .= "\n		->NotNullable()";
		if(isset($diff['auto_increment']))
			if($diff['auto_increment'])
				$migration .= "\n		->autoincrement()";
			else
				$migration .= "\n		->notAutoincrement()";
		if(isset($diff['default'])) {
			if($diff['default'] === false)
				$migration .= "\n		->def(false)";
			else
				$migration .= "\n		->def('$diff[default]')";
		}
		if(isset($diff['key'])) {
			if($diff['key']=='PRI')
				$migration .= "\n		->primary()";
			elseif($diff['key']=='UNI')
				$migration .= "\n		->unique()";
			elseif($diff['key']=='MUL')
				$migration .= "\n		->index()";
			else
				$migration .= "\n		->dropIndex()";
		}
		$migration .= ";";
		
		return $migration;
	}
	
	private static function buildColumnFor($table, $col, $definition) {
		$migration = '';
		$migration = "\n\t\$table->add('$col', '$definition[type]')";
		if($definition['nullable'])
			$migration .= "\n		->nullable()";
		if($definition['auto_increment'])
			$migration .= "\n		->autoincrement()";
		if($definition['default'])
			$migration .= "\n		->def('$definition[default]')";
		if($definition['key']=='PRI')
			$migration .= "\n		->primary()";
		if($definition['key']=='UNI')
			$migration .= "\n		->unique()";
		if($definition['key']=='MUL')
			$migration .= "\n		->index()";
		$migration .= ";";
		
		return $migration;
	}
	
	private static function buildTableFor($class, $definition) {
		$table = $class;
		
		$migration = "Schema::create('$table', function(".'$table'.") {";
		foreach($definition as $col=>$col_definition)
			$migration .= "\t".static::buildColumnFor($table, $col, $col_definition);
		$migration .= "\n});";
		
		return $migration;
	}
	
	public static function current() {
		try {
		return file_get_contents('migrations/migrate_version');
		} catch(\ErrorException $e) {
			return 0;
		}
	}
	
	public static function uptodate() {
		$migrations = static::todo();
				
		return !(sizeof($migrations) > 0);
	}

	private static function tableSchema($table) {
		$structure = array();
		try{
			$res = DB::query('Describe `'.$table.'`')->all();
		} catch(\Exception $e) {
			return null;
		}
		foreach($res as $one) {
			$col = array();
			$col['type'] = $one['Type'];
			$col['default'] = $one['Default'];
			$col['nullable'] = $one['Null'] == 'YES';
			$col['key'] = $one['Key'];
			$col['auto_increment'] = strpos($one['Extra'], 'auto_increment') !== false;
			$struc[$one['Field']] = $col;
		}
		
		return $struc;
	}
	
	public static function runMigration($migration, $verbose) {
		preg_match('/([0-9]+)_([^.]+)/', $migration, $matches);
		$version = $matches[1];
		$name = $matches[2];
		$class = $name.'_'.$version;
		include($migration);
		$class::up();
		if($verbose)
			echo 'Running '.$class."\n";
		return $version;
	}
	
	public static function todo() {
		$migrations = array();
		$files = glob('migrations/*.php');
		foreach($files as $file) {
			preg_match('/\/([0-9]+)_/', $file, $matches);
			//~ d($matches);
			$migrations[$matches[1]] = $file;
		}
		ksort($migrations);
		$current = static::current();
		foreach($migrations as $k=>$v)
			if($k <= $current)
				unset($migrations[$k]);
				
		return $migrations;
	}

	public static function automigrate() {
		static::diff();
		static::migrate();
	}
}

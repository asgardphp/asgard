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
	 * @param DataMapperInterface                         $dataMapper
	 * @param \Asgard\Migration\MigrationManagerInterface $MigrationManager
	 */
	public function __construct(DataMapperInterface $dataMapper, \Asgard\Migration\MigrationManagerInterface $MigrationManager=null) {
		$this->dataMapper = $dataMapper;
		$this->MigrationManager = $MigrationManager;
	}

	/**
	 * Automatically migrate given entity definitions.
	 * @param \Asgard\Entity\Definition[]|\Asgard\Entity\Definition $definitions
	 */
	public function autoMigrate($definitions) {
		if(!is_array($definitions))
			$definitions = [$definitions];
		$entitiesSchema = $this->getEntitiesSchemas($definitions);
		$sqlSchema = $this->getSQLSchemas();
		$this->processSchemas($entitiesSchema, $sqlSchema);
	}

	/**
	 * Generate a migration from given entity definitions.
	 * @param  array|\Asgard\Entity\Definition $definitions
	 * @param  string                          $migrationName
	 * @return string                          name of migration
	 */
	public function generateMigration($definitions, $migrationName) {
		if(!is_array($definitions))
			$definitions = [$definitions];
		$entitiesSchema = $this->getEntitiesSchemas($definitions);
		$sqlSchema = $this->getSQLSchemas();
		$up = $this->buildMigration($entitiesSchema, $sqlSchema, false);
		$down = $this->buildMigration($sqlSchema, $entitiesSchema, true);

		if($up == '' && $down == '')
			return;

		return $this->MigrationManager->create($up, $down, $migrationName, '\Asgard\Migration\DBMigration');
	}

	/**
	 * Generate schemas of entities.
	 * @param  array $definitions
	 * @return \Doctrine\DBAL\Schema\Schema
	 */
	protected function getEntitiesSchemas(array $definitions) {
		$dataMapper = $this->dataMapper;
		$schema = new \Doctrine\DBAL\Schema\Schema;
		foreach($definitions as $definition) {
			$table = $schema->createTable($dataMapper->getTable($definition));

			foreach($definition->properties() as $name=>$prop) {
				#relations
				if($prop->get('type') == 'entity') {
					$relation = $dataMapper->relation($definition, $name);
					#relations with one entity
					if(!$relation->get('many')) {
						$table->addColumn($relation->getLink(), 'integer', [
							'length'         => 11,
							'notnull'        => false,
							'autoincrement'  => false,
						]);
						if($relation->isPolymorphic()) {
							$table->addColumn($relation->getLinkType(), 'string', [
								'length'         => 50,
								'notnull'        => false,
								'autoincrement'  => false,
							]);
						}
					}
					#HMABT relations
					elseif($relation->type() == 'HMABT' && !$relation->isPolymorphic()) {
						$table_name = $relation->getAssociationTable();
						#if table was not already created by the opposite entity
						if(!$schema->hasTable($table_name)) {
							$relTable = $schema->createTable($table_name);
							$relTable->addColumn($relation->getLinkB(), 'integer', [
								'length'         => 11,
								'notnull'        => false,
								'autoincrement'  => false,
							]);
							$relTable->addColumn($relation->getLinkA(), 'integer', [
								'length'         => 11,
								'notnull'        => false,
								'autoincrement'  => false,
							]);
						}
						if($relation->reverse()->isPolymorphic()) {
							$schema->getTable($table_name)->addColumn($relation->reverse()->getLinkType(), 'string', [
								'length'         => 50,
								'notnull'        => false,
								'autoincrement'  => false,
							]);
						}
						#sortable
						if($relation->get('sortable')) {
							$schema->getTable($table_name)->addColumn($relation->getPositionField(), 'integer', [
								'length'         => 11,
								'notnull'        => false,
								'autoincrement'  => false,
							]);
						}
					}
					continue;
				}

				$col = [];
				if(method_exists($prop, 'getORMParameters'))
					$col = $prop->getORMParameters();
				if($ormCol = $prop->get('orm'))
					$col = array_merge($col, $ormCol);

				if($prop->get('many')) {
					$type = 'blob';
					$col['length'] = 65535;
				}
				elseif(!isset($col['type']))
					throw new \Exception('Cannot convert '.get_class($prop).' type to SQL type.');
				else
					$type = $col['type'];
				unset($col['type']);

				if(!isset($col['notnull']))
					$col['notnull'] = false;
				if(!isset($col['autoincrement']))
					$col['autoincrement'] = false;

				if($prop->get('i18n')) {
					$table_name = $dataMapper->getTranslationTable($definition);
					if(!$schema->hasTable($table_name)) {
						$i18nTable = $schema->createTable($table_name);
						$i18nTable->addColumn('id', 'integer', [
							'length'         => 11,
							'notnull'        => true,
							'autoincrement'  => false,
						]);
						$i18nTable->addColumn('locale', 'string', [
							'length'         => 50,
							'notnull'        => true,
							'autoincrement'  => false,
						]);
					}
					$schema->getTable($table_name)->addColumn($name, $type, $col);
				}
				else {
					if($name === 'id') {
						$col['autoincrement'] = true;
						$col['notnull'] = true;
					}
					$table->addColumn($name, $type, $col);
				}
			}

			if(isset($definition->get('orm')['indexes'])) {
				foreach($definition->get('orm')['indexes'] as $index) {
					$index['type'] = strtoupper($index['type']);

					foreach($index['columns'] as $col) {
						if(in_array($table->getColumn($col)->getType()->getName(), ['text', 'blob']))
							throw new \Exception('Table '.$table->getName().' cannot have indexes with text/blog columns.');
					}

					if($index['type'] == 'PRIMARY')
						$table->setPrimaryKey($index['columns'], 'PRIMARY');
					else {
						$indexName = $_indexName = implode('_', $index['columns']);
						$i = 1;
						while($table->hasIndex($indexName))
							$indexName = $_indexName.$i++;
						if($index['type'] == 'UNIQUE')
							$table->addUniqueIndex($index['columns'], $indexName);
						elseif($index['type'] == 'INDEX')
							$table->addIndex($index['columns'], $indexName);
						elseif($index['type'] == 'FULLTEXT') {
							$table->addIndex($index['columns'], $indexName);
							$table->getIndex($indexName)->addFlag('fulltext');
						}
					}
				}
			}
			
			$table->setPrimaryKey(['id']);
		}

		return $schema;
	}

	/**
	 * Process the schemas.
	 * @param  \Doctrine\DBAL\Schema\Schema $newSchema
	 * @param  \Doctrine\DBAL\Schema\Schema $oldSchema
	 */
	protected function processSchemas(\Doctrine\DBAL\Schema\Schema $newSchema, \Doctrine\DBAL\Schema\Schema $oldSchema) {
		$comparator = new \Doctrine\DBAL\Schema\Comparator;
		$schemaDiff = $comparator->compare($oldSchema, $newSchema);

		$platform = $this->dataMapper->getDB()->getConn()->getDatabasePlatform();

		$queries = $schemaDiff->toSql($platform);
		foreach($queries as $query)
			$this->dataMapper->getDB()->query($query);
	}

	/**
	 * Fetch the SQL schemas
	 * @return \Doctrine\DBAL\Schema\Schema
	 */
	protected function getSQLSchemas() {
		return $this->dataMapper->getDb()->getConn()->getSchemaManager()->createSchema();
	}

	/**
	 * Build the migration code by comparing the new schemas to the old ones.
	 * @param  \Doctrine\DBAL\Schema\Schema $newSchema
	 * @param  \Doctrine\DBAL\Schema\Schema $oldSchema
	 * @param  boolean                      $drop       true to drop the old tables
	 * @return string  migration code
	 */
	protected function buildMigration(\Doctrine\DBAL\Schema\Schema $newSchema, \Doctrine\DBAL\Schema\Schema $oldSchema, $drop) {
		$comparator = new \Doctrine\DBAL\Schema\Comparator;
		$schemaDiff = $comparator->compare($oldSchema, $newSchema);

		$res = '';

		if($drop) {
			foreach($schemaDiff->removedTables as $tableName=>$table)
				$res .= $this->dropTable($tableName);
		}
		else {
			foreach($schemaDiff->newTables as $tableName=>$table)
				$res .= $this->createTable($tableName, $table);
		}

		foreach($schemaDiff->changedTables as $tableName=>$table) {
			$colsRes = '';

			foreach($table->addedColumns as $colName=>$col)
				$colsRes .= $this->createColumn($colName, $col);

			foreach($table->changedColumns as $colName=>$col)
				$colsRes .= $this->updateColumn($colName, $col);

			foreach($table->renamedColumns as $colName=>$col)
				$colsRes .= $this->renameColumn($colName, $col);

			foreach($table->removedColumns as $colName=>$col)
				$colsRes .= $this->dropColumn($colName);

			foreach($table->addedIndexes as $index)
				$colsRes .= $this->createIndex($index);

			foreach($table->removedIndexes as $indexName=>$index)
				$colsRes .= $this->dropIndex($indexName);

			if($colsRes)
				$res .= "\$this->schema->table('$tableName', function(\$table) {".$colsRes."\n});\n\n";
		}

		return trim($res, "\n");
	}

	/**
	 * Generate code to drop a table.
	 * @param  string $table
	 * @return string
	 */
	protected function dropTable($table) {
		return "\$this->schema->drop('$table');\n\n";
	}

	/**
	 * Generate code to drop a column.
	 * @param  string $col
	 * @return string
	 */
	protected function dropColumn($col) {
		return "\n\t\$table->dropColumn('$col');";
	}

	/**
	 * Generate code to rename a column.
	 * @param  string                       $name
	 * @param  \Doctrine\DBAL\Schema\Column $col
	 * @return string
	 */
	protected function renameColumn($name, \Doctrine\DBAL\Schema\Column $col) {
		$res = "\n\t\$table->rename('$name', '".$col->getName()."');";

		return $res;
	}

	/**
	 * Generate code to create a table.
	 * @param  string                      $tableName
	 * @param  \Doctrine\DBAL\Schema\Table $table
	 * @return string
	 */
	protected function createTable($tableName, $table) {
		$res = "\$this->schema->create('$tableName', function(\$table) {";

		foreach($table->getColumns() as $colName=>$col)
			$res .= $this->createColumn($colName, $col);

		foreach($table->getIndexes() as $index)
			$res .= $this->createIndex($index);

		$res .= "\n});\n\n";

		return $res;
	}

	/**
	 * Generate the code to update a column.
	 * @param  string                           $name column name
	 * @param  \Doctrine\DBAL\Schema\ColumnDiff $col
	 * @return string
	 */
	protected function updateColumn($name, \Doctrine\DBAL\Schema\ColumnDiff $col) {
		$res = "\n\t\$table->changeColumn('$name', [";

		foreach($col->column->toArray() as $propName=>$prop) {
			if(in_array($propName, $col->changedProperties)) {
				if($propName === 'type')
					$res .= "\n\t\t'$propName' => '".strtolower($prop)."',";
				else
					$res .= "\n\t\t'$propName' => '$prop',";
			}
		}
		$res .= "\n\t]);";

		return $res;
	}

	/**
	 * Generate the code to create a column.
	 * @param  string                       $name column name
	 * @param  \Doctrine\DBAL\Schema\Column $col
	 * @return string
	 */
	protected function createColumn($name, \Doctrine\DBAL\Schema\Column $col) {
		$type = strtolower($col->getType());

		$res = "\n\t\$table->addColumn('$name', '".$type."', [";
		if($col->getNotnull())
			$res .= "\n		'notnull' => true,";
		if($col->getAutoincrement())
			$res .= "\n		'autoincrement' => true,";
		if($col->getDefault())
			$res .= "\n		'default' => '".$col->getDefault()."',";
		if($col->getScale())
			$res .= "\n		'scale' => ".$col->getScale().",";
		if(in_array($type, ['decimal', 'float', 'double']) && $col->getPrecision())
			$res .= "\n		'precision' => ".$col->getPrecision().",";
		if($col->getLength() !== null)
			$res .= "\n		'length' => ".$col->getLength().",";
		if($col->getFixed())
			$res .= "\n		'length' => true,";
		if($col->getUnsigned())
			$res .= "\n		'length' => true,";
		$res .= "\n	]);";

		return $res;
	}

	/**
	 * Build an index.
	 * @param  \Doctrine\DBAL\Schema\Index $index
	 * @return string
	 */
	protected function createIndex($index) {
		if($index->isPrimary())
			$res = "\n\t\$table->setPrimaryKey(\n\t\t".$this->outputPHP($index->getColumns(), 2)."\n\t);";
		elseif($index->isUnique())
			$res = "\n\t\$table->addUniqueIndex(\n\t\t".$this->outputPHP($index->getColumns(), 2)."\n\t);";
		else {
			if(count($index->getFlags()) > 0)
				$res = "\n\t\$table->addIndex(\n\t\t".$this->outputPHP($index->getColumns(), 2).",\n\t\tnull,\n\t\t".$this->outputPHP($index->getFlags(), 2)."\n\t);";
			else
				$res = "\n\t\$table->addIndex(\n\t\t".$this->outputPHP($index->getColumns(), 2)."\n\t);";
		}
		
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
	 * @param  mixed   $v
	 * @param  integer $tabs
	 * @param  boolean $line
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
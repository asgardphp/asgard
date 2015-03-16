<?php
namespace Asgard\Migration;

/**
 * Tracker class to track migrations statuses.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Tracker {
	/**
	 * Migations directory.
	 * @var string
	 */
	protected $dir;
	/**
	 * Database.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;

	/**
	 * Constructor.
	 * @param string $dir
	 */
	public function __construct($dir, \Asgard\Db\DBInterface $db) {
		$this->dir = $dir;
		$this->db = $db;
	}

	public function createTable() {
		if(!$this->db->getSchema()->hasTable('_migrations')) {
			$this->db->getSchema()->create('_migrations', function($table) {
				$table->addColumn('name', 'string', [
					'length' => 255,
				]);
				$table->addColumn('migrated', 'datetime', [
				]);
			});
		}
	}

	/**
	 * Return the list of registered migratins.
	 * @return array
	 */
	public function getList() {
		if(!file_exists($this->dir.'/migrations.json'))
			return [];
		$migrations = json_decode(file_get_contents($this->dir.'/migrations.json'), true);

		$this->createTable();

		$tracking = [];
		foreach($this->db->dal()->from('_migrations')->get() as $r)
			$tracking[$r['name']] = ['migrated' => strtotime($r['migrated'])];

		foreach($migrations as $migration=>$params) {
			if(isset($tracking[$migration]))
				$migrations[$migration] = array_merge($migrations[$migration], $tracking[$migration]);
		}

		uasort($migrations, function($a, $b) {
			if(isset($a['migrated']) && !isset($b['migrated']))
				return -1;
			elseif(!isset($a['migrated']) && isset($b['migrated']))
				return 1;
			elseif(isset($a['migrated']) && isset($b['migrated'])) {
				if($a['migrated'] !== $b['migrated'])
					return $a['migrated'] > $b['migrated'];
			}
			return $a['added'] > $b['added'];
		});
		
		return $migrations;
	}

	/**
	 * Return the list of down migrations.
	 * @return array
	 */
	public function getDownList() {
		$list = $this->getList();
		foreach($list as $migration=>$params) {
			if(isset($params['migrated']))
				unset($list[$migration]);
		}
		return $list;
	}

	/**
	 * Return the list of up migrations.
	 * @return array
	 */
	public function getUpList() {
		$list = $this->getList();
		foreach($list as $migration=>$params) {
			if(!isset($params['migrated']))
				unset($list[$migration]);
		}
		return $list;
	}

	/**
	 * Check if a migration is registered.
	 * @param  string  $migration
	 * @return boolean            true if registered, false otherwise
	 */
	public function has($migration) {
		$list = $this->getList();
		return isset($list[$migration]);
	}

	/**
	 * Return the next migration to be executed.
	 * @return string    migration name
	 */
	public function getNext() {
		$list = $this->getList();
		foreach($list as $migration=>$params) {
			if(!isset($params['migrated']))
				return $migration;
		}
	}

	/**
	 * Return the last executed migration.
	 * @return string    migration name
	 */
	public function getLast() {
		$list = array_reverse($this->getList());
		foreach($list as $migration=>$params) {
			if(isset($params['migrated']))
				return $migration;
		}
	}

	/**
	 * Get all migrations until a given migration name
	 * @param  string $untilMigration
	 * @return arra
	 */
	public function getUntil($untilMigration) {
		$list = [];
		if(!in_array($untilMigration, array_keys($this->getList())))
			throw new \Exception($untilMigration.' is not in the list.');
		foreach(array_reverse($this->getList()) as $migration=>$params) {
			if(isset($params['migrated']))
				$list[] = $migration;
			if($migration == $untilMigration)
				break;
		}
		return $list;
	}

	/**
	 * Register a migration.
	 * @param string $migrationName
	 */
	public function add($migrationName) {
		$list = $this->getList();
		if(isset($list[$migrationName]))
			return;
		$list[$migrationName] = ['added'=>time()+microtime()];
		$this->writeMigrations($list);
	}

	/**
	 * Remove a migration.
	 * @param  string $migrationName
	 */
	public function remove($migrationName) {
		$list = $this->getList();
		unset($list[$migrationName]);
		$this->writeMigrations($list);
	}

	/**
	 * Mark a migration as unmigrated.
	 * @param  string $migrationName
	 */
	public function unmigrate($migrationName) {
		$this->createTable();
		$this->db->dal()->from('_migrations')->where('name', $migrationName)->delete();
	}

	/**
	 * Mark a migration as migrated.
	 * @param  string $migrationName
	 */
	public function migrate($migrationName) {
		$this->createTable();
		$this->db->dal()->into('_migrations')->insert(['name'=>$migrationName, 'migrated'=>date('Y-m-d H:i:s')]);
	}

	/**
	 * Check if a migration was migrated.
	 * @param  string  $migrationName
	 * @return boolean                true if it was, false otherwise
	 */
	public function isUp($migrationName) {
		return isset($this->getList()[$migrationName]['migrated']);
	}

	/**
	 * Persist migrations list.
	 * @param  array $res
	 */
	protected function writeMigrations($res) {
		uasort($res, function($a, $b) {
			if(isset($a['migrated']) && !isset($b['migrated']))
				return -1;
			elseif(!isset($a['migrated']) && isset($b['migrated']))
				return 1;
			elseif(isset($a['migrated']) && isset($b['migrated'])) {
				if($a['migrated'] !== $b['migrated'])
					return $a['migrated'] > $b['migrated'];
			}
			return $a['added'] > $b['added'];
		});

		$migrations = [];
		foreach($res as $migration=>$params)
			$migrations[$migration] = ['added'=>$params['added']];

		file_put_contents($this->dir.'/migrations.json', json_encode($migrations, JSON_PRETTY_PRINT));
	}
}
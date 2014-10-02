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
	 * Constructor.
	 * @param string $dir
	 */
	public function __construct($dir) {
		$this->dir = $dir;
	}

	/**
	 * Return the list of registered migratins.
	 * @return array
	 */
	public function getList() {
		if(!file_exists($this->dir.'/migrations.json'))
			return [];
		$migrations = json_decode(file_get_contents($this->dir.'/migrations.json'), true);
		if(file_exists($this->dir.'/tracking.json'))
			$tracking = json_decode(file_get_contents($this->dir.'/tracking.json'), true);
		foreach($migrations as $migration=>$params) {
			if(isset($tracking[$migration]))
				$migrations[$migration] = array_merge($migrations[$migration], $tracking[$migration]);
		}
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
		$list = $this->getList();
		if(!isset($list[$migrationName]['migrated']))
			return;
		unset($list[$migrationName]['migrated']);
		$this->writeTracking($list);
	}

	/**
	 * Mark a migration as migrated.
	 * @param  string $migrationName
	 */
	public function migrate($migrationName) {
		$list = $this->getList();
		$list[$migrationName]['migrated'] = time()+microtime();
		$this->writeTracking($list);
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
			elseif(isset($a['migrated']) && isset($b['migrated']))
				return $a['migrated'] > $b['migrated'];
			else
				return $a['added'] > $b['added'];
		});

		$migrations = [];
		foreach($res as $migration=>$params)
			$migrations[$migration] = ['added'=>$params['added']];

		file_put_contents($this->dir.'/migrations.json', json_encode($migrations, JSON_PRETTY_PRINT));
	}

	/**
	 * Persist migration statuses.
	 * @param  array $res
	 */
	protected function writeTracking($res) {
		uasort($res, function($a, $b) {
			if(isset($a['migrated']) && !isset($b['migrated']))
				return -1;
			elseif(!isset($a['migrated']) && isset($b['migrated']))
				return 1;
			elseif(isset($a['migrated']) && isset($b['migrated']))
				return $a['migrated'] > $b['migrated'];
			else
				return $a['added'] > $b['added'];
		});

		$tracking = [];
		foreach($res as $migration=>$params) {
			if(isset($params['migrated']))
				$tracking[$migration] = ['migrated'=>$params['migrated']];
		}

		file_put_contents($this->dir.'/tracking.json', json_encode($tracking, JSON_PRETTY_PRINT));
	}
}
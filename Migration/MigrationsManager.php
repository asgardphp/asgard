<?php
namespace Asgard\Migration;

/**
 * Manage the migrations.
 */
class MigrationsManager {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * Directory where migrations are located.
	 * @var string
	 */
	protected $directory;
	/**
	 * Tracker instance to track migrations statuses.
	 * @var Tracker
	 */
	protected $tracker;

	/**
	 * Constructor.
	 * @param string $directory
	 * @param \Asgard\Container\Container $container 
	 */
	public function __construct($directory, $container=[]) {
		$this->directory = $directory;
		$this->container = $container;
		$this->tracker = new Tracker($directory);
	}

	/**
	 * Return the tracker instance.
	 * @return Tracker
	 */
	public function getTracker() {
		return $this->tracker;
	}

	/**
	 * Add a migration file.
	 * @param string $file file path
	 */
	public function add($file) {
		$dst = $this->directory.'/'.basename($file);
		if(($path = \Asgard\File\FileSystem::copy($file, $dst, \Asgard\File\FileSystem::RENAME)) === false)
			return false;
		$migrationName = explode('.', basename($path))[0];
		$this->tracker->add($migrationName);
		return $migrationName;
	}

	/**
	 * Create a new migration from given code.
	 * @param  string $up    
	 * @param  string $down  
	 * @param  string $name  migration name
	 * @param  string $class entity class
	 * @return string        final migration name
	 */
	public function create($up, $down, $name, $class='\Asgard\Migration\Migration') {
		$up = implode("\n\t\t", explode("\n", $up));
		$down = implode("\n\t\t", explode("\n", $down));
		$name = ucfirst(strtolower($name));

		$dst = $this->directory.'/'.$name.'.php';
		$dst = \Asgard\File\FileSystem::getNewFilename($dst);
		$name = str_replace('.php', '', basename($dst));
			
		$migration = '<?php
class '.$name.' extends '.$class.' {
	public function up() {
		'.$up.'
	}
	
	public function down() {
		'.$down."
	}
}";
		$dst = \Asgard\File\FileSystem::write($dst, $migration, \Asgard\File\FileSystem::RENAME);
		if($dst === false)
			throw new \Exception($dst.' can not be created.');

		$this->tracker->add($name);

		return explode('.', basename($dst))[0];
	}

	/**
	 * Check if it contains a migration.
	 * @param  string  $migrationName 
	 * @return boolean                true if migration exists, false otherwise
	 */
	public function has($migrationName) {
		return $this->tracker->has($migrationName);
	}

	/**
	 * Remove a migration.
	 * @param  string $migrationName
	 */
	public function remove($migrationName) {
		if($this->tracker->isUp($migrationName))
			return;
		if(\Asgard\File\FileSystem::delete($this->directory.'/'.$migrationName.'.php'))
			$this->tracker->remove($migrationName);
	}

	/**
	 * Execute a migration.
	 * @param  string  $migrationName
	 * @param  boolean $tracking      true to track the migration status
	 * @return boolean                true for success, otherwise false
	 */
	public function migrate($migrationName, $tracking=false) {
		if($tracking && $this->tracker->isUp($migrationName))
			return false;

		$this->migrateFile($this->directory.'/'.$migrationName.'.php');

		if($tracking)
			$this->tracker->migrate($migrationName);
		return true;
	}

	/**
	 * Execute a migration file directly.
	 * @param  string $file file path
	 */
	public function migrateFile($file) {
		if(!file_exists($file))
			throw new \Exception($file.' does not exists.');
		$class = \Asgard\Common\Tools::loadClassFile($file);
		$migration = new $class($this->container);

		$migration->_up();
	}

	/**
	 * Execute all migrations.
	 * @param  boolean $tracking true to track the migration status
	 * @return boolean                true for success, otherwise false
	 */
	public function migrateAll($tracking=false) {
		if($tracking)
			$list = $this->tracker->getDownList();
		else
			$list = $this->tracker->getList();
		foreach($list as $migrationName=>$params) {
			if($this->migrate($migrationName, $tracking) === false)
				return false;
		}
		return true;
	}

	/**
	 * Rollback and re-execute all migrations.
	 * @return boolean                true for success, otherwise false
	 */
	public function reset() {
		foreach($this->tracker->getUpList() as $migrationName=>$params) {
			if($this->unmigrate($migrationName) === false)
				return false;
		}
		return $this->migrateAll(true);
	}

	/**
	 * Rollback a migration.
	 * @param  string $migrationName 
	 * @return boolean                true for success, otherwise false
	 */
	public function unmigrate($migrationName) {
		if(!$this->tracker->isUp($migrationName))
			return false;
		if(!file_exists($this->directory.'/'.$migrationName.'.php'))
			return;
		require_once $this->directory.'/'.$migrationName.'.php';
		$migration = new $migrationName($this->container);

		$migration->_down();
		$this->tracker->unmigrate($migrationName);
		return true;
	}

	/**
	 * Rollback the last migration.
	 */
	public function rollback() {
		return $this->unmigrate($this->tracker->getLast());
	}

	/**
	 * Rollback until a given migration name.
	 * @param  string $migrationName 
	 */
	public function rollbackUntil($migrationName) {
		foreach($this->tracker->getUntil($migrationName) as $_migrationName)
			$this->unmigrate($_migrationName);
	}
}
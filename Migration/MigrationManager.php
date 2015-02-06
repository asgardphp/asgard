<?php
namespace Asgard\Migration;

/**
 * Manage the migrations.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class MigrationManager implements MigrationManagerInterface {
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
	 * DB dependency.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;
	/**
	 * Schema dependency.
	 * @var \Asgard\Db\SchemaInterface
	 */
	protected $schema;

	/**
	 * Constructor.
	 * @param string $directory
	 * @param \Asgard\Db\DBInterface $directory
	 * @param \Asgard\Container\ContainerInterface $container
	 */
	public function __construct($directory, \Asgard\Db\DBInterface $db, \Asgard\Db\SchemaInterface $schema, \Asgard\Container\ContainerInterface $container=null) {
		$this->directory = $directory;
		$this->container = $container;
		$this->db        = $db;
		$this->schema    = $schema;
		$this->tracker   = new Tracker($directory, $db);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTracker() {
		return $this->tracker;
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function has($migrationName) {
		return $this->tracker->has($migrationName);
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove($migrationName) {
		if($this->tracker->isUp($migrationName))
			return;
		if(\Asgard\File\FileSystem::delete($this->directory.'/'.$migrationName.'.php'))
			$this->tracker->remove($migrationName);
	}

	/**
	 * {@inheritDoc}
	 */
	public function migrate($migrationName) {
		if($this->tracker->isUp($migrationName))
			return false;

		$this->migrateFile($this->directory.'/'.$migrationName.'.php');

		$this->tracker->migrate($migrationName);
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function migrateFile($file) {
		if(!file_exists($file))
			throw new \Exception($file.' does not exists.');
		$class = \Asgard\Common\Tools::loadClassFile($file);
		$migration = new $class($this->container);
		if($migration instanceof DBMigration) {
			$migration->setDB($this->db);
			$migration->setSchema($this->schema);
		}

		$migration->_up();
	}

	/**
	 * {@inheritDoc}
	 */
	public function migrateAll() {
		$list = $this->tracker->getDownList();
		foreach($list as $migrationName=>$params) {
			if($this->migrate($migrationName) === false)
				return false;
		}
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function reset() {
		foreach($this->tracker->getUpList() as $migrationName=>$params) {
			if($this->unmigrate($migrationName) === false)
				return false;
		}
		return $this->migrateAll(true);
	}

	/**
	 * {@inheritDoc}
	 */
	public function unmigrate($migrationName) {
		if(!$this->tracker->isUp($migrationName))
			return false;
		if(!file_exists($this->directory.'/'.$migrationName.'.php'))
			return;
		require_once $this->directory.'/'.$migrationName.'.php';
		$migration = new $migrationName($this->container);
		if($migration instanceof DBMigration) {
			$migration->setDB($this->db);
			$migration->setSchema($this->schema);
		}

		$migration->_down();
		$this->tracker->unmigrate($migrationName);
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rollback() {
		return $this->unmigrate($this->tracker->getLast());
	}

	/**
	 * {@inheritDoc}
	 */
	public function rollbackUntil($migrationName) {
		foreach($this->tracker->getUntil($migrationName) as $_migrationName)
			$this->unmigrate($_migrationName);
	}
}
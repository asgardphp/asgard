<?php
namespace Asgard\Migration;

class MigrationsManager {
	protected $directory;
	protected $app;
	protected $tracker;

	public function __construct($directory, $app=[]) {
		$this->directory = $directory;
		$this->app = $app;
		$this->tracker = new Tracker($directory);
	}

	public function getTracker() {
		return $this->tracker;
	}

	public function add($file) {
		$code = file_get_contents($file);
		$dst = $this->directory.'/'.basename($file);
		$dst = \Asgard\Common\FileManager::getNewFilename($dst);
		$migrationName = explode('.', basename($dst))[0];
		$code = preg_replace('/{{migrationMame}}/', $migrationName, $code);
		if($r = \Asgard\Common\FileManager::put($dst, $code))
			$this->tracker->add($migrationName);
		if(!$r)
			return false;
		return $migrationName;
	}

	public function create($up, $down, $name, $class='\Asgard\Migration\Migration') {
		$up = implode("\n\t\t", explode("\n", $up));
		$down = implode("\n\t\t", explode("\n", $down));
		$name = ucfirst(strtolower($name));

		$dst = $this->directory.'/'.$name.'.php';
		$dst = \Asgard\Common\FileManager::getNewFilename($dst);
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
		$dst = \Asgard\Common\FileManager::put($dst, $migration, true);

		$this->tracker->add($name);

		return explode('.', basename($dst))[0];
	}

	public function has($migrationName) {
		return $this->tracker->has($migrationName);
	}

	public function remove($migrationName) {
		if($this->tracker->isUp($migrationName))
			return;
		if(\Asgard\Common\FileManager::unlink($this->directory.'/'.$migrationName.'.php'))
			$this->tracker->remove($migrationName);
	}

	public function migrate($migrationName, $tracking=false) {
		if($tracking && $this->tracker->isUp($migrationName))
			return false;

		$this->migrateFile($this->directory.'/'.$migrationName.'.php');

		if($tracking)
			$this->tracker->migrate($migrationName);
		return true;
	}

	public function migrateFile($file) {
		if(!file_exists($file))
			throw new \Exception($file.' does not exists.');
		$class = \Asgard\Common\Tools::loadClassFile($file);
		$migration = new $class($this->app);

		$migration->_up();
	}

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

	public function reset() {
		foreach($this->tracker->getUpList() as $migrationName=>$params) {
			if($this->unmigrate($migrationName, true) === false)
				return false;
		}
		return true;
	}

	public function unmigrate($migrationName) {
		if(!$this->tracker->isUp($migrationName))
			return false;
		if(!file_exists($this->directory.'/'.$migrationName.'.php'))
			return;
		require_once $this->directory.'/'.$migrationName.'.php';
		$migration = new $migrationName($this->app);

		$migration->_down();
		$this->tracker->unmigrate($migrationName);
		return true;
	}

	public function rollback() {
		return $this->unmigrate($this->tracker->getLast());
	}

	public function rollbackUntil($migrationName) {
		foreach($this->tracker->getUntil($migrationName) as $_migrationName)
			$this->unmigrate($_migrationName);
	}
}
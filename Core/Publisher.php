<?php
namespace Asgard\Core;

class Publisher {
	protected $app;

	public function __construct($app) {
		$this->app = $app;
	}

	public function publish($src, $dst) {
		return \Asgard\File\FileSystem::copy($src, $dst);
	}

	public function publishMigrations($src, $dst, $migrate) {
			if(!\Asgard\File\FileSystem::copy($src, $dst))
				return false;
			$mm = new \Asgard\Migration\MigrationsManager($dst, $this->app);
			$tracking = new \Asgard\Migration\Tracker($src);
			foreach(array_keys($tracking->getList()) as $migration) {
				if($migrate)
					$mm->migrate($migration, true);
				else
					$mm->add($migration);
			}
			return true;
	}
}
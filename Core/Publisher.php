<?php
namespace Asgard\Core\Commands;

class Publisher {
	public function publish($src, $dst) {
		\Asgard\File\FileSystem::copy($src, $dst);
	}

	public function publishMigrations($src, $dst, $migrate) {
			\Asgard\File\FileSystem::copy($src, $dst);
			$mm = new \Asgard\Migration\MigrationsManager($dst);
			$tracking = new \Asgard\Migration\Tracker($src);
			foreach($tracking->getList() as $migration=>$params) {
				if($migrate)
					$mm->migrate($migration);
				else
					$mm->add($migration);
			}
	}
}
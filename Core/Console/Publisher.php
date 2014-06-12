<?php
namespace Asgard\Core\Console;

class Publisher {
	public function publish($src, $dst) {
		\Asgard\Common\FileManager::copy($src, $dst);
	}

	public function publishMigrations($src, $dst, $migrate) {
			\Asgard\Common\FileManager::copy($src, $dst);
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
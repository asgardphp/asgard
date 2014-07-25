<?php
namespace Asgard\Core;

class Publisher {
	use \Asgard\Container\ContainerAware;
	
	protected $output;

	public function __construct($container, $output) {
		$this->container = $container;
		$this->output = $output;
	}

	public function publish($src, $dst) {
		$r = true;
		foreach(glob($src.'/*') as $file) {
			$finalDst = \Asgard\File\FileSystem::copy($file, $dst, \Asgard\File\FileSystem::RENAME);
			if($finalDst !== $dst) {
				$this->output->warning('The file '.$dst.' had to be renamed into '.$finalDst.'.');
				$r = false;
			}
		}
		return $r;
	}

	public function publishMigrations($src, $dst, $migrate) {
			if(!$this->publish($src, $dst) && $migrate) {
				$this->output->warning('The migrations could not be migrated because some files had to be renamed.');
				$migrate = false;
			}
			$mm = new \Asgard\Migration\MigrationsManager($dst, $this->container);
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
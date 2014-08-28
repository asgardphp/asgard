<?php
namespace Asgard\Core;

class Publisher {
	use \Asgard\Container\ContainerAwareTrait;
	
	protected $output;

	public function __construct($container, $output) {
		$this->container = $container;
		$this->output = $output;
	}

	public function publish($src, $dstDir) {
		$r = true;
		foreach(glob($src.'/*') as $file) {
			$dst = $dstDir.'/'.basename($file);
			static::copy($file, $dst);
		}
		return $r;
	}

	public function publishMigrations($src, $dstDir, $migrate) {
		$r = true;
		foreach(glob($src.'/*') as $file) {
			if(basename($file) === 'migrations.json')
				continue;
			$dst = $dstDir.'/'.basename($file);
			static::copy($file, $dst);
		}

		if(!$r) {
			$this->output->writeln('<warning>The migrations could not be added because some files had to be renamed. Please add them manually.</warning>');
			return false;
		}
		else {
			$mm = new \Asgard\Migration\MigrationsManager($dstDir, $this->container);
			$tracking = new \Asgard\Migration\Tracker($src);
			foreach(array_keys($tracking->getList()) as $migration) {
				$mm->getTracker()->add($migration);
				if($migrate)
					$mm->migrate($migration, true);
			}
			return true;
		}
	}

	public static function copy($src, $dst) {
		if(is_dir($src))
			return static::copyDir($src, $dst);
		else {
			if(file_exists($dst)) {
				$dst = static::getNewFilename($odst = $dst);
				$this->output->writeln('<warning>The file '.$odst.' had to be renamed into '.$dst.'.</warning>');
			}

			\Asgard\File\FileSystem::mkdir(dirname($dst));
			$r = copy($src, $dst);

			if($r !== false)
				return $dst;
			return false;
		}
	}

	protected static function copyDir($src, $dst) {
		$r = true;
		$dir = opendir($src);
		\Asgard\File\FileSystem::mkdir($dst);
		while(false !== ($file = readdir($dir))) { 
			if(($file != '.') && ($file != '..'))
				$r = $r && static::copy($src.'/'.$file, $dst.'/'.$file);
		} 
		closedir($dir);

		if($r !== false)
			return $dst;
		return false;
	}
}
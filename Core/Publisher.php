<?php
namespace Asgard\Core;

/**
 * Publish bundle assets.
 */
class Publisher {
	use \Asgard\Container\ContainerAwareTrait;
	
	/**
	 * Console output.
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	protected $output;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface                       $container
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	public function __construct($container, $output) {
		$this->container = $container;
		$this->output = $output;
	}

	/**
	 * Publish assets from a directory to another.
	 * @param  string $src   
	 * @param  string $dstDir
	 * @return boolean true for success
	 */
	public function publish($src, $dstDir) {
		$r = true;
		foreach(glob($src.'/*') as $file) {
			$dst = $dstDir.'/'.basename($file);
			if(!$this->copy($file, $dst))
				$r = false;
		}
		return $r;
	}

	/**
	 * Publish migration files.
	 * @param  string  $src    
	 * @param  string  $dstDir 
	 * @param  boolean $migrate
	 * @return boolean true for success         
	 */
	public function publishMigrations($src, $dstDir, $migrate) {
		$r = true;
		foreach(glob($src.'/*') as $file) {
			if(basename($file) === 'migrations.json')
				continue;
			$dst = $dstDir.'/'.basename($file);
			$this->copy($file, $dst);
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

	/**
	 * Copy files.
	 * @param  string $src
	 * @param  string $dst
	 * @return boolean   true for success
	 */
	public function copy($src, $dst) {
		if(is_dir($src))
			return $this->copyDir($src, $dst);
		else {
			if(file_exists($dst)) {
				$dst = \Asgard\File\FileSystem::getNewFilename($odst = $dst);
				$this->output->writeln('<warning>The file '.$odst.' had to be renamed into '.$dst.'.</warning>');
			}

			\Asgard\File\FileSystem::mkdir(dirname($dst));
			$r = copy($src, $dst);

			if($r !== false)
				return $dst;
			return false;
		}
	}

	/**
	 * Copy a directory.
	 * @param  string $src
	 * @param  string $dst
	 * @return boolean   true for success
	 */
	protected function copyDir($src, $dst) {
		$r = true;
		$dir = opendir($src);
		\Asgard\File\FileSystem::mkdir($dst);
		while(false !== ($file = readdir($dir))) { 
			if(($file != '.') && ($file != '..'))
				$r = $r && $this->copy($src.'/'.$file, $dst.'/'.$file);
		} 
		closedir($dir);

		if($r !== false)
			return $dst;
		return false;
	}
}
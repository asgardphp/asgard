<?php
namespace Asgard\Config;

/**
 * Configuration bag.
 */
class Config extends \Asgard\Common\Bag {
	/**
	 * Load a directory.
	 * @param  string $dir
	 * @param  string $env
	 * @return Config      $this
	 */
	public function loadConfigDir($dir, $env=null) {
		foreach(glob($dir.'/*.yml') as $filename) {
			if(is_dir($filename))
				$this->loadConfigDir($filename);
			else {
				$basename = basename($filename);
				if(preg_match('/^[^_]+.[^.]+$/', $basename))
					$this->loadConfigFile($filename);
				if($env !== null && preg_match('/^.+_'.$env.'.[^.]+$/', $basename))
					$this->loadConfigFile($filename);
			}
		}
		return $this;
	}
	
	/**
	 * Load a file.
	 * @param  string $filename
	 * @return Confug $this
	 */
	public function loadConfigFile($filename) {
		$yaml = new \Symfony\Component\Yaml\Parser();
		if(($r = file_get_contents($filename))) {
			if(is_array($res = $yaml->parse($r)))
				$this->load($res);
		}
		return $this;
	}
}
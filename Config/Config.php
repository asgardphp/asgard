<?php
namespace Asgard\Config;

class Config extends \Asgard\Common\Bag {
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
	
	public function loadConfigFile($filename) {
		$yaml = new \Symfony\Component\Yaml\Parser();
		$this->load($yaml->parse(file_get_contents($filename)));
		return $this;
	}
}
<?php
namespace Asgard\Config;

/**
 * Configuration bag.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Config extends \Asgard\Common\Bag implements ConfigInterface {
	/**
	 * {@inheritDoc}
	 */
	public function loadDir($dir, $env=null) {
		$files = glob($dir.'/*.yml');
		usort($files, function($a, $b) {
			if(strpos($a, '.locale.') !== false)
				return 1;
			if(strpos($b, '.locale.') !== false)
				return -1;
		});
		foreach($files as $filename) {
			if(is_dir($filename))
				$this->loadDir($filename);
			else {
				$basename = basename($filename);
				if(preg_match('/^[^_]+.[^.]+$/', $basename))
					$this->loadFile($filename);
				if($env !== null && preg_match('/^.+_'.$env.'.[^.]+$/', $basename))
					$this->loadFile($filename);
			}
		}
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadFile($filename) {
		$yaml = new \Symfony\Component\Yaml\Parser();
		if(($r = file_get_contents($filename))) {
			if(is_array($res = $yaml->parse($r)))
				$this->set($res);
		}
		return $this;
	}
}
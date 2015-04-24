<?php
namespace Asgard\Config;

/**
 * Configuration bag.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Config extends \Asgard\Common\Bag implements ConfigInterface {
	protected $cache;

	public function __construct($cache=null) {
		$this->cache = $cache;
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadDir($dir, $env=null) {
		if($this->cache && $res = $this->cache->fetch('config.dir.'.$dir.'.'.$env))
			$this->set($res);
		else {
			$res = $this->_loadDir($dir, $env);
			$this->set($res);
			if($this->cache)
				$this->cache->save('config.dir.'.$dir.'.'.$env, $res);
		}
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadFile($filename) {
		$this->set($this->_loadFile($filename));
		return $this;
	}

	protected function _loadDir($dir, $env=null) {
		$res = [];
		$files = glob($dir.'/*.yml');
		usort($files, function($a, $b) {
			if(strpos($a, '.local.') !== false)
				return 1;
			if(strpos($b, '.local.') !== false)
				return -1;
		});
		foreach($files as $filename) {
			if(is_dir($filename))
				$res = array_merge($this->_loadDir($filename), $res);
			else {
				$basename = basename($filename);
				if(preg_match('/^[^_]+.[^.]+$/', $basename))
					$res = array_merge($res, $this->_loadFile($filename));
				if($env !== null && preg_match('/^.+_'.$env.'.[^.]+$/', $basename))
					$res = array_merge($res, $this->_loadFile($filename));
			}
		}
		return $res;
	}


	protected function _loadFile($filename) {
		$yaml = new \Symfony\Component\Yaml\Parser();
		if(($r = file_get_contents($filename))) {
			if(is_array($res = $yaml->parse($r)))
				return $res;
		}
		return [];
	}
}
<?php
namespace Asgard\Cache;

class FileCache implements CacheInterface {
	protected $path;
	protected $active;

	public function __construct($path=null, $active=true) {
		$this->path = $path;
		$this->active = $active;
	}
	
	public function clear() {
		if(!$this->active)
			return;

		\Asgard\Utils\FileManager::unlink($this->path);
	}

	public function get($identifier, $default=null) {
		if(!$this->active)
			return;

		try {
			return include $this->path.$file.'.php';
		} catch(\ErrorException $e) {}

		if(\Asgard\Utils\Tools::is_function($default)) {
			$r = $default();
			static::set($identifier, $r);
			return $r;
		}
		else
			return $default;
	}
	
	public function set($file, $var) {
		if(!$this->active)
			return false;

		if(static::sizeofvar($var) > 5*1024*1024)
			return false;
		try {
			if(is_object($var))
				$res = 'unserialize(\''.serialize($var).'\')';
			elseif(($ve = var_export($var, true)) == '')
				$res = 'null';
			else
				$res = $ve;
			$res = '<?php'."\n".'return '.$res.';';
			$output = $this->path.$file.'.php';
			\Asgard\Utils\FileManager::mkdir(dirname($output));
			file_put_contents($output, $res);
		} catch(\ErrorException $e) {
			return false;
		}
		return true;
	}
	
	public function delete($file) {
		if(!$this->active)
			return;
		$path = $this->path.$file.'.php';
		return \Asgard\Utils\FileManager::unlink($path);
	}

	protected static function sizeofvar($var) {
		$start_memory = memory_get_usage();
		$tmp = unserialize(serialize($var));
		$r = memory_get_usage() - $start_memory;
		unset($tmp);
		return $r;
	}
}
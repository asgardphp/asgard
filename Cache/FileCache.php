<?php
namespace Asgard\Cache;

class FileCache implements CacheInterface {
	protected $path;

	public function __construct($path=null) {
		$this->path = $path;
	}
	
	public function clear() {
		\Asgard\Utils\FileManager::unlink($this->path);
	}

	public function get($identifier, $default=null) {
		try {
			if(file_exists($this->path.$identifier.'.php'))
				return include $this->path.$identifier.'.php';
		} catch(\ErrorException $e) {}

		if(is_callable($default)) {
			$r = $default();
			static::set($identifier, $r);
			return $r;
		}
		else
			return $default;
	}
	
	public function set($file, $var) {
		if(!static::isSerializable($var))
			return false;
		try {
			if(static::sizeofvar($var) > 5*1024*1024)
				return false;

			if(is_object($var))
				$res = 'unserialize(\''.serialize($var).'\')';
			elseif(($ve = var_export($var, true)) == '')
				$res = 'null';
			else
				$res = $ve;
			$res = '<?php'."\n".'return '.$res.';';
			$dst = $this->path.$file.'.php';
			\Asgard\Utils\FileManager::mkdir(dirname($dst));
			file_put_contents($dst, $res);
		} catch(\Exception $e) {
			return false;
		}
		return true;
	}
	
	public function delete($file) {
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

	protected static function isSerializable($value) {
		if(is_object($value))
			return $value instanceof \Serializable;

		if(!is_array($value))
			return true;

		foreach($value as $element) {
			if(!static::isSerializable($element))
				return false;
		}

		return true;
	}
}
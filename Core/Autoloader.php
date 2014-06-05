<?php
namespace Asgard\Core;

class Autoloader {
	protected $search = false;
	protected $goUp = false;
	protected $preload = false;
	protected $cache;

	public $map = array(
		// 'Something'	=>	'there/somewhere.php',
	);
	public $namespaces = array(
		// 'App'	=>	'app',
	);
	
	public function map($class, $path) {
		$this->map[$class] = $path;
	}
	
	public function namespaceMap($namespace, $dir) {
		$this->namespaces[$namespace] = $dir;
	}

	public function search($search) {
		$this->search = $search;
	}

	public function preload($preload) {
		$this->preload = $preload;
	}

	public function goUp($goUp) {
		$this->goUp = $goUp;
	}

	public function setCache($cache) {
		$this->cache = $cache;
	}

	public function preloadFile($file) {
		if(!$this->preload)
			return array();
		
		list($class) = explode('.', basename($file));
		$this->map($class, $file);
	}
	
	public function preloadDir($dir) {
		if(!$this->preload)
			return array();

		if($this->cache) {
			$preload = $this->cache->get('Asgard\Core\Autoloader\preloadDir\\'.$dir, function() use($file) {
				return $this->_preloadDir($dir, false);
			});
		}
		else
			$preload = $this->_preloadDir($dir, false);
		foreach($preload as $class=>$file)
			$this->map($class, $file);
	}

	protected function _preloadDir($file, $onlyCapital=true) {
		if(is_dir($file)) {
			$preload = array();
			if($onlyCapital && !preg_match('/^[A-Z]{1}/', basename($file)))
				return array();
			$preload = array();
			foreach(glob($file.'/*') as $sub_file)
				$preload = array_merge($preload, $this->_preloadDir($sub_file));
			return $preload;
		}
		else {
			if(!preg_match('/^[A-Z]{1}[a-zA-Z0-9_]+.php$/', basename($file)))
				return array();
			list($class) = explode('.', basename($file));
			return array($class => $file);
		}
	}

	public function importClass($class, $alias=null) {
		$class = preg_replace('/^\\\+/', '', $class);

		#look for the class
		if($res = $this->loadClass($class)) {
			if($alias !== null)
				return static::createAlias($res, $alias);
			else
				return static::createAlias($res, $class);
		}
		#go to upper level
		elseif($this->goUp) {
			$dir = static::dirname($class);

			if($dir != '.') {
				$base = static::basename($class);
				if(static::dirname($dir) == '.')
					$next = $base;
				else
					$next = str_replace(DIRECTORY_SEPARATOR, '\\', static::dirname($dir)).'\\'.$base;

				if($alias === null)
					$alias = $class;
				return $this->importClass($next, $alias);
			}
		
			return false;
		}
	}

	public function loadClass($class) {
		#already loaded
		if(class_exists($class, false) || interface_exists($class, false))
			return $class;
		#class map
		elseif(isset($this->map[$class]))
			return static::loadClassFile($this->map[$class], $class);
		else {
			#namespace map
			foreach($this->namespaces as $namespace=>$dir) {
				if(preg_match('/^'.preg_quote($namespace).'/', $class)) {
					$rest = preg_replace('/^'.preg_quote($namespace).'\\\?/', '', $class);
					$path = $dir.DIRECTORY_SEPARATOR.static::class2path($rest);

					if(file_exists($path))
						return static::loadClassFile($path, $class);
				}
			}

			#psr
			if(file_exists(($path = static::class2path($class))))
				return static::loadClassFile($path, $class);

			#lookup for global classes
			if($this->search && static::dirname($class) == '.') {
				$classes = array();
				
				#check if there is any corresponding class already loaded, e.g. Foo => Test\Foo
				foreach(array_merge(get_declared_classes(), get_declared_interfaces()) as $v) {
					if(strtolower(static::basename($class)) == strtolower(static::basename($v)))
						return $v;
				}
			}
		}
		
		return false;
	}
	
	public static function loadClassFile($file, $alias=null) {
		$before = array_merge(get_declared_classes(), get_declared_interfaces());
		require_once $file;
		$after = array_merge(get_declared_classes(), get_declared_interfaces());
		
		$diff = array_diff($after, $before);
		$result = static::arrayGet(array_values($diff), count($diff)-1);
		if(!$result) {
			foreach(array_merge(get_declared_classes(), get_declared_interfaces()) as $class) {
				$reflector = new \ReflectionClass($class);
				if($reflector->getFileName() == realpath($file)) {
					$result = $class;
					break;
				}
			}
		}

		return $result;
	}
	
	protected static function class2path($class) {
		$className = static::basename($class);
		$namespace = strtolower(static::dirname($class));

		$namespace = str_replace('\\', DIRECTORY_SEPARATOR , $namespace );

		if($namespace != '.')
			$path = $namespace.DIRECTORY_SEPARATOR;
		else
			$path = '';
		$path .= str_replace('_', DIRECTORY_SEPARATOR , $className);				

		return $path.'.php';
	}

	protected static function createAlias($class, $alias) {
		if(strtolower(static::basename($alias)) !== strtolower(static::basename($class)))
			return false;
		try {
			if($class !== $alias)
				class_alias($class, $alias);
			return true;
		} catch(\ErrorException $e) {
			return false;
		}
	}
	
	public function autoload($class) {
		if(class_exists($class))
			return;
		$this->importClass($class);
	}

	protected static function basename($ns) {
		return basename(str_replace('\\', DIRECTORY_SEPARATOR, $ns));
	}

	protected static function dirname($ns) {
		return str_replace(DIRECTORY_SEPARATOR, '\\', dirname(str_replace('\\', DIRECTORY_SEPARATOR, $ns)));
	}
	
	protected static function arrayGet($arr, $path, $default=null) {
		if(!is_array($path))
			$path = array($path);
		foreach($path as $key) {
			if(!isset($arr[$key]))
				return $default;
			else
				$arr = $arr[$key];
		}
		return $arr;
	}
}
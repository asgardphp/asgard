<?php
namespace Asgard\Core;

class Autoloader {
	protected $globalNamespace = false;
	protected $preload = false;

	public $map = array(
		// 'Something'	=>	'there/somewhere.php',
	);
	public $namespaces = array(
		// 'App'	=>	'app',
	);
	public $preloaded = array(
		// array('Somewhere', 'there/somewhere.php'),
	);
	
	public function map($class, $path) {
		$this->map[$class] = $path;
	}
	
	public function namespaceMap($k, $v) {
		$this->namespaces[$k] = $v;
	}

	public function globalNamespace($globalNamespace) {
		$this->globalNamespace = $globalNamespace;
	}

	public function preload($preload) {
		$this->preload = $preload;
	}

	public function addPreloadedClasses(array $classes) {
		if(!$this->globalNamespace || !$this->preload)
			return;
		foreach($classes as $class)
			$this->preloaded[] = $class;
		#remove duplicate files
		$this->preloaded = array_unique($this->preloaded, SORT_REGULAR);
	}

	public function preloadClass($class, $file) {
		if(!$this->globalNamespace || !$this->preload)
			return;
		if(!array_search(realpath($file), $this->preloaded));
			$this->preloaded[] = array(strtolower($class), realpath($file));
	}
	
	public function preloadDir($file) {
		if(!$this->preload)
			return array();

		$preload = \Asgard\Utils\Cache::get('Asgard\Core\Autoloader\preloadDir\\'.$file, function() use($file) {
			return $this->_preloadDir($file, false);
		});
		$this->preloaded = array_merge($this->preloaded, $preload);
	}

	public function _preloadDir($file, $onlyCapital=true) {
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
		if($res=$this->loadClass($class)) {
			if($alias !== null)
				return static::createAlias($class, $alias);
			return true;
		}
		#go to upper level
		else {
			$dir = \Asgard\Utils\NamespaceUtils::dirname($class);

			if($dir != '.') {
				$base = \Asgard\Utils\NamespaceUtils::basename($class);
				if(\Asgard\Utils\NamespaceUtils::dirname($dir) == '.')
					$next = $base;
				else
					$next = str_replace(DIRECTORY_SEPARATOR, '\\', \Asgard\Utils\NamespaceUtils::dirname($dir)).'\\'.$base;

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
			return true;
		#class map
		elseif(isset($this->map[strtolower($class)]))
			return static::loadClassFile($this->map[strtolower($class)], $class);
		else {
			#namespace map
			foreach($this->namespaces as $namespace=>$dir) {
				if(preg_match('/^'.preg_quote($namespace).'/', $class)) {
					$rest = preg_replace('/^'.preg_quote($namespace).'\\\?/', '', $class);
					$path = _DIR_.$dir.DIRECTORY_SEPARATOR.static::class2path($rest);

					if(file_exists($path))
						return static::loadClassFile($path, $class);
				}
			}

			#psr
			if(file_exists(_DIR_.($path = static::class2path($class))))
				return static::loadClassFile(_DIR_.$path, $class);

			#lookup for global classes
			if($this->globalNamespace && \Asgard\Utils\NamespaceUtils::dirname($class) == '.') {
				$classes = array();
				
				#check if there is any corresponding class already loaded
				foreach(array_merge(get_declared_classes(), get_declared_interfaces()) as $v) {
					if(strtolower(\Asgard\Utils\NamespaceUtils::basename($class)) == strtolower(\Asgard\Utils\NamespaceUtils::basename($v)))
						return static::createAlias($v, $class);
				}
				
				if($this->preload) {
					if(isset($this->preloaded[$class]))
						return static::loadClassFile($this->preloaded[$class], $class);
					return false;
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
		$result = \Asgard\Utils\Tools::array_get(array_values($diff), count($diff)-1);
		if(!$result) {
			foreach(array_merge(get_declared_classes(), get_declared_interfaces()) as $class) {
				$reflector = new \ReflectionClass($class);
				if($reflector->getFileName() == realpath($file)) {
					$result = $class;
					break;
				}
			}
		}
		if($alias && !static::createAlias($result, $alias))
			return false;
		return $result;
	}
	
	protected static function class2path($class) {
		$className = \Asgard\Utils\NamespaceUtils::basename($class);
		$namespace = strtolower(\Asgard\Utils\NamespaceUtils::dirname($class));

		$namespace = str_replace('\\', DIRECTORY_SEPARATOR , $namespace );

		if($namespace != '.')
			$path = $namespace.DIRECTORY_SEPARATOR;
		else
			$path = '';
		$path .= str_replace('_', DIRECTORY_SEPARATOR , $className);				

		return $path.'.php';
	}

	protected static function createAlias($class, $alias) {
		if(strtolower(\Asgard\Utils\NamespaceUtils::basename($alias)) != strtolower(\Asgard\Utils\NamespaceUtils::basename($class)))
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
}
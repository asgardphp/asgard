<?php
namespace Asgard\Core;

class Importer {
	public $from = '';

	public $basedir;

	public $aliases = array();

	public function __construct($from='') {
		$this->basedir = _DIR_;
		$this->from = $from;
	}
	
	public function alias($original, $alias) {
		$this->aliases[$alias] = $original;
	}

	public function import($what, $into='') {
		$imports = explode(',', $what);
		foreach($imports as $import) {
			$import = trim($import);
			
			$class = $what;
			$alias = \Asgard\Utils\NamespaceUtils::basename($class);
			$vals = explode(' as ', $import);
			if(isset($vals[1])) {
				$class = trim($vals[0]);
				$alias = trim($vals[1]);
			}
		
			$alias = preg_replace('/^\\\+/', '', $into.'\\'.$alias);
			$class = preg_replace('/^\\\+/', '', $this->from.'\\'.$class);
			$this->preimported[$alias] = $class;
		}
	
		return $this;
	}
	
	public function _import($class, $params=array()) {
		$class = preg_replace('/^\\\+/', '', $class);
		$alias = isset($params['as']) ? $params['as']:null;
		$intoNamespace= isset($params['into']) ? $params['into']:null;
		
		if($intoNamespace == '.')
			$intoNamespace = '';

		if(!$alias && $alias !== false) 
			$alias = ($intoNamespace ? $intoNamespace.'\\':'').\Asgard\Utils\NamespaceUtils::basename($class);

		#look for the class
		if($res=$this->loadClass($class)) {
			if($alias !== false)
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

				return static::_import($next, array('into'=>$intoNamespace, 'as'=>$alias));
			}
		
			return false;
		}
	}

	public function loadClass($class) {
		#already loaded
		if(class_exists($class, false) || interface_exists($class, false))
			return true;
		#alias
		elseif(isset($this->aliases[$class]))
			return class_alias($this->aliases[$class], $class, true);
		#file map
		elseif(isset(Autoloader::$map[strtolower($class)]))
			return static::loadClassFile(Autoloader::$map[strtolower($class)], $class);
		else {
			#directory map
			foreach(Autoloader::$directories as $prefix=>$dir) {
				if(preg_match('/^'.preg_quote($prefix).'/', $class)) {
					$rest = preg_replace('/^'.preg_quote($prefix).'\\\?/', '', $class);
					$path = _DIR_.$dir.DIRECTORY_SEPARATOR.static::class2path($rest);

					if(file_exists($path))
						return static::loadClassFile($path, $class);
				}
			}

			if(file_exists($this->basedir.($path = static::class2path($class))))
				return static::loadClassFile($this->basedir.$path, $class);

			#lookup for global classes
			if(\Asgard\Core\App::get('config')->get('global_namespace') && \Asgard\Utils\NamespaceUtils::dirname($class) == '.') {
				$classes = array();
				
				#check if there is any corresponding class already loaded
				foreach(array_merge(get_declared_classes(), get_declared_interfaces()) as $v) {
					if(strtolower(\Asgard\Utils\NamespaceUtils::basename($class)) == strtolower(\Asgard\Utils\NamespaceUtils::basename($v)))
						return static::createAlias($v, $class);
				}
				
				foreach(Autoloader::$preloaded as $v) {
					if(strtolower(\Asgard\Utils\NamespaceUtils::basename($class)) == $v[0])
						$classes[] = $v;
				}
				if(sizeof($classes) == 1)
					return static::loadClassFile($classes[0][1], $class);
				#if multiple classes, don't load
				elseif(sizeof($classes) > 1) {
					$classfiles = array();
					foreach($classes as $classname)
						$classfiles[] = $classname[1];
					throw new \Exception('There are multiple classes '.$class.': '.implode(', ', $classfiles));
				}
				#if no class, don't load
				else
					return false;
			}
		}
		
		return false;
	}
	
	public static function loadClassFile($file, $alias=null) {
		$before = array_merge(get_declared_classes(), get_declared_interfaces());
		require_once $file;
		$after = array_merge(get_declared_classes(), get_declared_interfaces());
		
		$diff = array_diff($after, $before);
		$result = \Asgard\Utils\Tools::array_get(array_values($diff), sizeof($diff)-1);
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
	
	public static function class2path($class) {
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

	public static function createAlias($loadedClass, $class) {
		if(strtolower(\Asgard\Utils\NamespaceUtils::basename($class)) != strtolower(\Asgard\Utils\NamespaceUtils::basename($loadedClass)))
			return false;
		try {
			if($loadedClass !== $class)
				class_alias($loadedClass, $class);
			return true;
		} catch(\ErrorException $e) {
			return false;
		}
	}
}
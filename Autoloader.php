<?php
namespace Coxis\Core;

class Autoloader {
	public static $map = array(
		// 'Something'	=>	'there/somewhere.php',
	);
	public static $directories = array(
		// 'App'	=>	'app',
	);
	public static $preloaded = array(
		// array('Somewhere', 'there/somewhere.php'),
	);
	
	public static function map($class, $path) {
		static::$map[$class] = $path;
	}
	
	public static function dir($k, $v) {
		static::$directories[$k] = $v;
	}

	public static function preloadClass($class, $file) {
		if(!array_search(realpath($file), static::$preloaded));
			static::$preloaded[] = array(strtolower($class), realpath($file));
	}
	
	public static function preloadDir($file) {
		if(is_dir($file) && !strpos($file, '.') !== 0) {
			foreach(glob($file.'/*') as $sub_file)
				static::preloadDir($sub_file);
		}
		else {
			if(!preg_match('/^[A-Z]{1}[a-zA-Z0-9_]+.php$/', basename($file)))
				return;
			list($class) = explode('.', basename($file));
			static::preloadClass($class, $file);
		}
		#remove duplicate files
		static::$preloaded = array_unique(static::$preloaded, SORT_REGULAR);
	}
	
	public static function loadClass($class) {
		if(function_exists('__autoload'))
			__autoload($class);
		if(class_exists($class))
			return;
		
		$dir = \Coxis\Core\NamespaceUtils::dirname($class);

		Context::get('importer')->_import($class, array('into'=>$dir));
	}
}
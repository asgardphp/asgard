<?php
namespace Coxis\Core;

class Autoloader {
	public static $map = array(
		// 'Something'	=>	'there/somewhere.php',
	);
	public static $directories = array(
		// 'foo\bar'	=>	'there',
		// 'swift_'	=>	'swift',
		'Coxis\App'	=>	'app',
		// 'Coxis'	=>	'coxis',
		'App'	=>	'app',
		'Tests'	=>	'tests',
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
		static::$preloaded[] = array(strtolower($class), $file);
	}
	
	public static function preloadDir($file) {
		if(is_dir($file))
			foreach(glob($file.'/*') as $sub_file)
				static::preloadDir($sub_file);
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
		// $dir = str_replace('\\', DIRECTORY_SEPARATOR, \Coxis\Core\NamespaceUtils::dirname($class));

		Context::get('importer')->_import($class, array('into'=>$dir));
	}
}
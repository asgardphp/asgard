<?php
// namespace Asgard\Core;

// class Asgard {
// 	static $loaded = false;

// 	public static function setDefaultEnvironment() {
// 		if(!defined('_ENV_')) {
// 			if(PHP_SAPI == 'cli' || \Asgard\Core\App::get('server')->get('HTTP_HOST') == '127.0.0.1' || \Asgard\Core\App::get('server')->get('HTTP_HOST') == 'localhost')
// 				define('_ENV_', 'dev');
// 			else
// 				define('_ENV_', 'prod');
// 		}
// 	}

// 	public static function load() {
// 		if(static::$loaded)
// 			return;
		
// 		static::setDefaultEnvironment();
// 		if(file_exists(_DIR_.'app/load.php'))
// 			include _DIR_.'app/load.php';
// 		BundlesManager::instance()->loadBundles(\Asgard\Core\App::get('config')->get('bundles'));
		
// 		\Asgard\Core\App::get('request')->inst()->isInitial = true;
// 		\Asgard\Core\App::get('locale')->importLocales('locales');

// 		static::$loaded = true;
// 	}
// }

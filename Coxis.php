<?php
namespace Coxis\Core;

class Coxis {
	static $loaded = false;

	public static function setDefaultEnvironment() {
		if(!defined('_ENV_')) {
			if(PHP_SAPI == 'cli' || \Server::get('HTTP_HOST') == '127.0.0.1' || \Server::get('HTTP_HOST') == 'localhost')
				define('_ENV_', 'dev');
			else
				define('_ENV_', 'prod');
		}
	}

	public static function load() {
		if(static::$loaded)
			return;
		
		static::setDefaultEnvironment();
		if(file_exists(_DIR_.'app/load.php'))
			include _DIR_.'app/load.php';
		BundlesManager::instance()->loadBundles(\Coxis\Core\Context::get('config')->get('bundles'));
		
		if(file_exists(_DIR_.'app/start.php'))
			include _DIR_.'app/start.php';
		// \Hook::trigger('start');
		\Request::inst()->isInitial = true;
		\Context::get('locale')->importLocales('locales');

		static::$loaded = true;
	}
}

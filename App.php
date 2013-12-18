<?php
namespace Coxis\Core;

class App {
	protected static $instance;
	protected $instances = array();
	protected $registry = array();
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
		$app = static::instance();
		if(file_exists(_DIR_.'app/load.php'))
			include _DIR_.'app/load.php';
		BundlesManager::instance()->loadBundles($app->get('config')->get('bundles'));
		
		\Request::inst()->isInitial = true;
		$app->get('locale')->importLocales('locales');

		static::$loaded = true;
	}

	function __construct() {
		#default instances
		$this->_set('importer', function() {
			return new \Coxis\Core\Importer;
		});
		$this->_set('config', function() {
			return new \Coxis\Core\Config('config');
		});
		#used in errorhandler..
		$this->_set('hook', function() {
			return new \Coxis\Hook\Hook;
		});
		$this->_set('request', function() {
			return \Coxis\Core\Request::createFromGlobals();
		});
		$this->_set('url', function() {
			return \Coxis\Core\App::get('request')->url;
		});
		$this->_set('response', function() {
			return new \Coxis\Core\Response;
		});
		$this->_set('facades', function() {
			return \Coxis\Core\Facades::inst();
		});
	}

	public static function instance() {
		if(!isset(static::$instance))
			static::$instance = new static;
		return static::$instance;
	}

	public static function get($class) {
		$instance = static::instance();
		return $instance->_get($class);
	}

	public function _get($class) {
		if(!isset($this->instances[$class]))
			$this->instances[$class] = $this->make($class);

		return $this->instances[$class];
	}

	public function __get($name) {
		return $this->_get($name);
	}

	public static function set($name, $value) {
		$instance = static::instance();
		return $instance->_set($name, $value);
	}

	public function _set($name, $value) {
		if(is_callable($value))
			$this->register($name, $value);
		else
			$this->instances[$name] = $value;
		return $this;
	}

	public function __set($name, $value) {
		return $this->set($name, $value);
	}

	public static function has($class) {
		$instance = static::instance();
		return $instance->_has($class);
	}

	public function _has($class) {
		return $this->registered($class);
	}

	public function register($name, $callback) {
		$this->registry[$name] = $callback;
	}
	
	public function make($name, $params=array(), $default=null) {
		if(isset($this->registry[$name]))
			return call_user_func_array($this->registry[$name], $params);
		else {
			if($default instanceof \Closure)
				return call_user_func_array($default, $params);
			else
				return $default;
		}
	}

	public function registered($name) {
		return isset($this->registry[$name]);
	}
}
<?php
namespace Coxis\Core;

class App {
	protected static $instance;
	protected $instances = array();
	protected $registry = array();
	protected $loaded = false;

	public static function setDefaultEnvironment() {
		if(!defined('_ENV_')) {
			if(PHP_SAPI == 'cli' || \Coxis\Core\App::get('server')->get('HTTP_HOST') == '127.0.0.1' || \Coxis\Core\App::get('server')->get('HTTP_HOST') == 'localhost')
				define('_ENV_', 'dev');
			else
				define('_ENV_', 'prod');
		}
	}

	public static function loadDefaultApp($config=null) {
		static::instance($config)->load();
	}

	public function load() {
		if($this->loaded)
			return;
		
		static::setDefaultEnvironment();

		include _DIR_.'app/load.php';

		$bundles = $this->get('config')->get('bundles');
		$bundlesmanager = new BundlesManager;
		$bundlesmanager->loadBundles($bundles);

		$this->loaded = true;
	}

	function __construct($config=null) {
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
			return \Coxis\Core\Coxis\Core\App::get('request')->createFromGlobals();
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

		if($config)
			$this->config = $config;
	}

	public static function hasInstance() {
		return isset(static::$instance);
	}

	public static function instance($new=false, $config=null) {
		if(!isset(static::$instance) || $new)
			static::$instance = new static($config);
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
		return $this->_set($name, $value);
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
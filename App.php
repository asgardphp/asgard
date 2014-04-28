<?php
namespace Asgard\Core;

class App {
	protected static $instance;
	protected $instances = array();
	protected $registry = array();
	protected $loaded = false;

	public static function setDefaultEnvironment() {
		if(!defined('_ENV_')) {
			if(PHP_SAPI == 'cli' || \Asgard\Core\App::get('server')->get('HTTP_HOST') == '127.0.0.1' || \Asgard\Core\App::get('server')->get('HTTP_HOST') == 'localhost')
				define('_ENV_', 'dev');
			else
				define('_ENV_', 'prod');
		}
	}

	public static function loadDefaultApp($bootstrap=true) {
		static::setDefaultEnvironment();
		static::instance()->load($bootstrap);
	}

	public function load($bootstrap=true) {
		if(version_compare(PHP_VERSION, '5.4.0') < 0)
			die('You need PHP ≥ 5.4');

		if($this->loaded)
			return;
		
		ob_start();

		if($bootstrap) {
			if(file_exists(_DIR_.'app/bootstrap_'.strtolower(_ENV_).'.php'))
				include _DIR_.'app/bootstrap_'.strtolower(_ENV_).'.php';
			if(file_exists(_DIR_.'app/bootstrap_all.php'))
				include _DIR_.'app/bootstrap_all.php';
		}

		$bundles = $this->get('config')->get('bundles');
		$bundlesdirs = $this->get('config')->get('bundlesdirs');
		$bundlesmanager = new BundlesManager;
		$bundlesmanager->addBundles($bundles);
		$bundlesmanager->addBundlesDirs($bundlesdirs);
		$bundlesmanager->loadBundles();
		$this->_set('bundlesmanager', $bundlesmanager);

		$this->loaded = true;
	}

	public function __construct($config=null) {
		#needed for loading bundles
		$this->_set('config', function() {
			return new \Asgard\Core\Config('config');
		});
		$this->_set('hook', function() {
			return new \Asgard\Hook\Hook;
		});
		$this->_set('cache', function() {
			$driver = \Asgard\Core\App::get('config')->get('cache_driver');
			if(!$driver)
				$driver = 'Asgard\Cache\NullCache';
			return $this->make($driver);
		});
		$this->_set('translator', function() {
			return new \Asgard\Translation\Translator;
		});
		$this->_set('importer', function() {
			return new \Asgard\Core\Importer;
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
		if(!isset($this->instances[$class])) {
			if($this->registry[$class]['save']) {
				$this->instances[$class] = $this->make($class);
				return $this->instances[$class];
			}
			else
				return $this->make($class);
		}
		else
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

	public function register($name, $callback, $save=true) {
		if(
			$this->_has('config')
			&& $this->_get('config')->get('autofacade')
			&& preg_match('/^[a-zA-Z0-9]+$/', $name)
			&& !class_exists(ucfirst(strtolower($name)))) {
			$string = 'class '.ucfirst(strtolower($name)).' extends \Asgard\Core\Facade {}';
			eval($string);
		}

		$this->registry[$name] = array('callback'=>$callback, 'save'=>$save);
	}
	
	public function make($name, $params=array(), $default=null) {
		if(isset($this->registry[$name]))
			return call_user_func_array($this->registry[$name]['callback'], $params);
		else {
			if($default instanceof \Closure)
				return call_user_func_array($default, $params);
			else {
				if($default === null)
					throw new \Exception('There is no constructor for "'.$name.'".');
				return $default;
			}
		}
	}

	public function registered($name) {
		return isset($this->registry[$name]);
	}
}
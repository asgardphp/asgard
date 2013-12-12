<?php
namespace Coxis\Core;

class App {
	protected static $instance;
	protected $instances = array();
	protected $registry = array();

	function __construct() {
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

		foreach(\Coxis\Core\Facades::inst()->all() as $facade=>$f) {
			list($class, $cb) = $f;
			if(!$this->registered(strtolower($facade)))
				$this->register(strtolower($facade), $cb);
		}
		foreach(\Coxis\Core\Facades::inst()->all() as $facade=>$f) {
			list($class, $cb) = $f;
			$this->_get('importer')->alias($class, $facade);
		}
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
		if($class == 'ioc')
			return $this;

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
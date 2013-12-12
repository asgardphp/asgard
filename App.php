<?php
namespace Coxis\Core;

class App {
	protected static $instance;
	public $classes = array();

	protected $ioc = null;

	function __construct() {
		$this->ioc = new IoC;

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
			if(!$this->ioc->registered(strtolower($facade)))
				$this->ioc->register(strtolower($facade), $cb);
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
		$context = static::instance();
		return $context->_get($class);
	}

	protected function _get($class) {
		if($class == 'ioc')
			return $this->ioc;

		if(!isset($this->classes[$class]))
			$this->classes[$class] = $this->ioc->get($class);

		return $this->classes[$class];
	}

	public function __get($name) {
		return $this->_get($name);
	}

	public static function set($name, $value) {
		$context = static::instance();
		return $context->_set($name, $value);
	}

	public function _set($name, $value) {
		if(is_callable($value))
			$this->ioc->register($name, $value);
		else
			$this->classes[$name] = $value;
		return $this;
	}

	public function __set($name, $value) {
		return $this->set($name, $value);
	}

	public static function has($class) {
		$context = static::instance();
		return $context->_has($class);
	}

	public function _has($class) {
		return $this->ioc->registered($class);
	}
}
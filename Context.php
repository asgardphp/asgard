<?php
namespace Coxis\Core;

class Context {
	public static $default = 'default';

	protected static $instances = array();
	public $classes = array();

	protected $ioc = null;

	function __construct() {
		$this->ioc = new IoC;

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

	public static function newDefault() {
		$rand = Tools::randstr(10);
		static::setDefault($rand);
	}

	public static function getDefault() {
		return static::$default;
	}

	public static function setDefault($def) {
		static::$default = $def;
	}

	public static function instance($context=null) {
		if(!$context)
			$context = static::$default;
		if(!isset(static::$instances[$context]))
			static::$instances[$context] = new static;
		return static::$instances[$context];
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

	public function set($name, $value) {
		if(is_callable($value)) {
			$this->ioc->register($name, $value);
			// return $this->get($name);
		}
		else {
			$this->classes[$name] = $value;
			// return $this;
		}
	}

	public function __set($name, $value) {
		return $this->set($name, $value);
	}
}
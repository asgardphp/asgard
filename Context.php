<?php
namespace Coxis\Core;

class Context {
	public static $default = 'default';

	protected static $instances = array();
	public $classes = array();

	protected $ioc = null;

	function __construct() {
		$context = $this;
		$this->ioc = new IoC;

		foreach(Coxis::$facades as $facade=>$class) {
			if(!$this->ioc->registered(strtolower($facade))) {
				if(is_function($class))
					$this->ioc->register(strtolower($facade), $class);
				else {
					$this->ioc->register(strtolower($facade), function() use($class) {
						return new $class;
					});
				}
			}
		}
	}

	public static function newDefault() {
		$rand = Tools::randstr(10);
		Context::setDefault($rand);
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
		$this->classes[$name] = $value;
		return $this;
	}

	public function __set($name, $value) {
		return $this->set($name, $value);
	}
}
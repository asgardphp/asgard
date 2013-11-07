<?php
namespace Coxis\Core;

class Facades {
	static $inst;
	protected $facades = array();

	public static function inst() {
		if(!static::$inst)
			static::$inst = new static;
		return static::$inst;
	}

	public function all() {
		return $this->facades;
	}

	public function register($alias, $class, $cb=null) {
		if(!$cb)
			$cb = array($class, 'callback');
		\Coxis\Core\Context::instance()->set(strtolower($alias), $cb);
		\Coxis\Core\Context::instance()->get('importer')->alias($class, $alias);
		$this->facades[$alias] = array($class, $cb);
	}
}
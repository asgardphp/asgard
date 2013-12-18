<?php
namespace Coxis\Core;

class Facades {
	static $inst;
	protected $facades = array();
	protected $app;

	function __construct($app=null) {
		if($app === null)
			$this->app = App::instance();
		else
			$this->app = $app;
	}

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
		if(!$this->app->has(strtolower($alias)))
			$this->app->set(strtolower($alias), $cb); #create instance
		$this->app->get('importer')->alias($class, $alias); #class alias
		$this->facades[$alias] = array($class, $cb);
	}
}
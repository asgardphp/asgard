<?php
namespace Asgard\Container;

use Jeremeamia\SuperClosure\SerializableClosure;

class Container implements \ArrayAccess {
	protected static $instance;
	protected $instances = [];
	protected $registry = [];
	protected $autofacade = false;

	public function __construct(array $instances=[]) {
		foreach($instances as $name=>$instance)
			$this->set($name, $instance);
	}

	public function getRegistry() {
		return $this->registry;
	}

	public function getInstances() {
		return $this->instances;
	}

	public static function singleton() {
		if(!isset(static::$instance))
			static::$instance = new static;
		return static::$instance;
	}

	public static function setInstance($instance) {
		static::$instance = $instance;
	}

	public function setAutofacade($facade) {
		$this->autofacade = $facade;
	}

	public function get($name) {
		$name = strtolower($name);
		if(!isset($this->instances[$name])) {
			if(!isset($this->registry[$name]))
				throw new \Exception($name.' has not been registered in container.');
			$this->instances[$name] = $this->make($name);
			return $this->instances[$name];
		}
		else
			return $this->instances[$name];
	}

	public function set($name, $value) {
		$name = strtolower($name);
		if($this->autofacade)
			$this->createFacade($name);

		$this->instances[$name] = $value;
	}

	protected function createFacade($name) {
		if(preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $name) && !class_exists(ucfirst($name)))
			eval('class '.ucfirst($name).' extends \Asgard\Container\Facade {}');
	}

	public function has($name) {
		$name = strtolower($name);
		return $this->registered($name) || isset($this->instances[$name]);
	}

	public function remove($name) {
		$name = strtolower($name);
		unset($this->instances[$name]);
	}

	public function register($name, $callback) {
		$name = strtolower($name);
		if($this->autofacade)
			$this->createFacade($name);

		if($callback instanceof \Closure)
			$callback = new SerializableClosure($callback);
		$this->registry[$name] = $callback;
	}
	
	public function make($name, array $params=[], $default=null) {
		$name = strtolower($name);
		if(isset($this->registry[$name]))
			return call_user_func_array($this->registry[$name], array_merge([$this], $params));
		else {
			if(is_callable($default))
				return call_user_func_array($default, $params);
			elseif($default === null)
				throw new \Exception('There is no constructor for "'.$name.'".');
			return $default;
		}
	}

	public function registered($name) {
		$name = strtolower($name);
		return isset($this->registry[$name]);
	}

	public function createFactory($what) {
		if(is_string($what)) {
			return new Factory(function($container, array $params) use($what) {
				return $container->make($what, $params);
			}, $this);
		}
		elseif(is_callable($what)) {
			return new Factory(function($container, array $params) use($what) {
				return $what($container, $params);
			}, $this);
		}
	}

	public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset must not be null.');
		else
			$this->set($offset, $value);
	}

	public function offsetExists($offset) {
		return $this->has($offset);
	}

	public function offsetUnset($offset) {
		return $this->remove($offset);
	}

	public function offsetGet($offset) {
		return $this->get($offset);
	}

	public function __wakeup() {
		if($this->autofacade) {
			foreach($this->instances as $name=>$instance)
				$this->createFacade($name);
		}
	}
}
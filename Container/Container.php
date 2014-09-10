<?php
namespace Asgard\Container;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Services container.
 */
class Container implements \ArrayAccess {
	/**
	 * Default instance.
	 * @var Container
	 */
	protected static $instance;
	/**
	 * Services instances.
	 * @var array
	 */
	protected $instances = [];
	/**
	 * Services registry.
	 * @var array
	 */
	protected $registry = [];
	/**
	 * Automatically create facades.
	 * @var boolean
	 */
	protected $autofacade = false;

	/**
	 * Constructor.
	 * @param array $instances
	 */
	public function __construct(array $instances=[]) {
		foreach($instances as $name=>$instance)
			$this->set($name, $instance);
	}

	/**
	 * Get the registry.
	 * @return array
	 */
	public function getRegistry() {
		return $this->registry;
	}

	/**
	 * Get the instances.
	 * @return array
	 */
	public function getInstances() {
		return $this->instances;
	}

	/**
	 * Get the default instance.
	 * @return Container
	 */
	public static function singleton() {
		if(!isset(static::$instance))
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * Set the default instance.
	 * @param Container $instance
	 */
	public static function setInstance($instance) {
		static::$instance = $instance;
	}

	/**
	 * Set autofacade to true or false.
	 * @param boolean $facade
	 */
	public function setAutofacade($facade) {
		$this->autofacade = $facade;
	}

	/**
	 * Get a service.
	 * @param  string $name
	 * @return mixed
	 */
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

	/**
	 * Set a service.
	 * @param string $name
	 * @param mixed  $value
	 */
	public function set($name, $value) {
		$name = strtolower($name);
		if($this->autofacade)
			$this->createFacade($name);

		$this->instances[$name] = $value;
	}

	/**
	 * Create a facade.
	 * @param  string service $name
	 */
	protected function createFacade($name) {
		if(preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $name) && !class_exists(ucfirst($name)))
			eval('class '.ucfirst($name).' extends \Asgard\Container\Facade {}');
	}

	/**
	 * Check if has a service.
	 * @param  string  $name
	 * @return boolean       true if service exists.
	 */
	public function has($name) {
		$name = strtolower($name);
		return $this->registered($name) || isset($this->instances[$name]);
	}

	/**
	 * Remove a service.
	 * @param  string $name
	 */
	public function remove($name) {
		$name = strtolower($name);
		unset($this->instances[$name]);
	}

	/**
	 * Register a service.
	 * @param  string $name    
	 * @param  callable $callback
	 */
	public function register($name, $callback) {
		$name = strtolower($name);
		if($this->autofacade)
			$this->createFacade($name);

		if($callback instanceof \Closure)
			$callback = new SerializableClosure($callback);
		$this->registry[$name] = $callback;
	}
	
	/**
	 * Make a service.
	 * @param  string $name  
	 * @param  array  $params
	 * @param  mixed $default
	 * @return mixed
	 */
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

	/**
	 * Check if a service was registered.
	 * @param  string $name
	 * @return boolean      true if registered
	 */
	public function registered($name) {
		$name = strtolower($name);
		return isset($this->registry[$name]);
	}

	/**
	 * Create a factory.
	 * @param  string|callable $what
	 * @return Factory
	 */
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

	/**
	 * Array set implementation.
	 * @param  integer $offset
	 * @param  mixed $value
	 * @throws LogicException If offset is null
	 */
	public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset must not be null.');
		else
			$this->set($offset, $value);
	}

	/**
	 * Array exists implementation.
	 * @param  integer $offset
	 * @return boolean true if it exists.
	 */
	public function offsetExists($offset) {
		return $this->has($offset);
	}

	/**
	 * Array unset implementation.
	 * @param  integer $offset
	 */
	public function offsetUnset($offset) {
		$this->remove($offset);
	}

	/**
	 * Array get implementation.
	 * @param  integer $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * __wakeup magic method
	 */
	public function __wakeup() {
		if($this->autofacade) {
			foreach($this->instances as $name=>$instance)
				$this->createFacade($name);
		}
	}
}
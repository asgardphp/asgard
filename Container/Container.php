<?php
namespace Asgard\Container;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Services container.
 */
class Container implements ContainerInterface {
	/**
	 * Default instance.
	 * @var ContainerInterface
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
	 * Parent classes.
	 * @var array
	 */
	protected $parents = [];

	/**
	 * Constructor.
	 * @param array $instances
	 */
	public function __construct(array $instances=[]) {
		foreach($instances as $name=>$instance)
			$this->set($name, $instance);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setParentClass($name, $parent, $force=false) {
		$name = strtolower($name);
		if($force !== true && isset($this->parents[$name]))
			throw new \Exception($name.' already has a parent class.');
		$this->parents[$name] = $parent;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParentClass($name) {
		$name = strtolower($name);
		if(!isset($this->parents[$name]))
			return;
		return $this->parents[$name];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRegistry() {
		return $this->registry;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getInstances() {
		return $this->instances;
	}

	/**
	 * Get the default instance.
	 * @return ContainerInterface
	 */
	public static function singleton() {
		if(!isset(static::$instance))
			static::$instance = new static;
		return static::$instance;
	}

	/**
	 * Set the default instance.
	 * @param ContainerInterface $instance
	 */
	public static function setInstance($instance) {
		static::$instance = $instance;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAutofacade($facade) {
		$this->autofacade = $facade;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($name) {
		$name = strtolower($name);
		if(!isset($this->instances[$name])) {
			if(!isset($this->registry[$name]))
				throw new \Exception($name.' has not been registered in container.');
			$this->instances[$name] = $this->make($name);
		}

		if(isset($this->parents[$name]) && !$this->instances[$name] instanceof $this->parents[$name])
			throw new \Exception('The service "'.$name.'" did not return a subclass of '.$this->parents[$name]);

		return $this->instances[$name];
	}

	/**
	 * {@inheritDoc}
	 */
	public function set($name, $value) {
		$name = strtolower($name);
		if($this->autofacade)
			$this->createFacade($name);

		$this->instances[$name] = $value;
		return $this;
	}

	/**
	 * Create a facade.
	 * @param string $name Service name.
	 */
	protected function createFacade($name) {
		if(preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $name) && !class_exists(ucfirst($name)))
			eval('class '.ucfirst($name).' extends \Asgard\Container\Facade {}');
	}

	/**
	 * {@inheritDoc}
	 */
	public function has($name) {
		$name = strtolower($name);
		return $this->registered($name) || isset($this->instances[$name]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove($name) {
		$name = strtolower($name);
		unset($this->instances[$name]);
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function make($name, array $params=[], $default=null) {
		$name = strtolower($name);
		if(isset($this->registry[$name]))
			$instance = call_user_func_array($this->registry[$name], array_merge([$this], $params));
		else {
			if(is_callable($default))
				$instance = call_user_func_array($default, $params);
			elseif($default !== null)
				$instance = $default;
			else
				throw new \Exception('There is no constructor for "'.$name.'".');
		}

		if(isset($this->parents[$name]) && !$instance instanceof $this->parents[$name])
			throw new \Exception('The service "'.$name.'" did not return a subclass of '.$this->parents[$name]);
		return $instance;
	}

	/**
	 * {@inheritDoc}
	 */
	public function registered($name) {
		$name = strtolower($name);
		return isset($this->registry[$name]);
	}

	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset must not be null.');
		else
			$this->set($offset, $value);
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetExists($offset) {
		return $this->has($offset);
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetUnset($offset) {
		$this->remove($offset);
	}

	/**
	 * {@inheritDoc}
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __wakeup() {
		if($this->autofacade) {
			foreach($this->instances as $name=>$instance)
				$this->createFacade($name);
		}
	}
}
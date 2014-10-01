<?php
namespace Asgard\Container;

/**
 * Services container.
 */
interface ContainerInterface extends \ArrayAccess {
	/**
	 * Set a service parent class.
	 * @param  string    $name
	 * @param  string    $parent
	 * @param  boolean   $force
	 * @return ContainerInterface $this
	 */
	public function setParentClass($name, $parent, $force=false);

	/**
	 * Return the parent class.
	 * @param  string $name
	 * @return string
	 */
	public function getParentClass($name);

	/**
	 * Get the registry.
	 * @return array
	 */
	public function getRegistry();

	/**
	 * Get the instances.
	 * @return array
	 */
	public function getInstances();

	/**
	 * Set autofacade to true or false.
	 * @param  boolean            $facade
	 * @return ContainerInterface $this
	 */
	public function setAutofacade($facade);

	/**
	 * Get a service.
	 * @param  string $name
	 * @return mixed
	 */
	public function get($name);

	/**
	 * Set a service.
	 * @param  string    $name
	 * @param  mixed     $value
	 * @return ContainerInterface $this
	 */
	public function set($name, $value);

	/**
	 * Check if has a service.
	 * @param  string  $name
	 * @return boolean       true if service exists.
	 */
	public function has($name);

	/**
	 * Remove a service.
	 * @param  string $name
	 */
	public function remove($name);

	/**
	 * Register a service.
	 * @param  string $name    
	 * @param  callable $callback
	 */
	public function register($name, $callback);
	
	/**
	 * Make a service.
	 * @param  string $name  
	 * @param  array  $params
	 * @param  mixed $default
	 * @return mixed
	 */
	public function make($name, array $params=[], $default=null);

	/**
	 * Check if a service was registered.
	 * @param  string $name
	 * @return boolean      true if registered
	 */
	public function registered($name);

	/**
	 * Create a factory.
	 * @param  string|callable $what
	 * @return Factory
	 */
	public function createFactory($what);

	/**
	 * __wakeup magic method
	 */
	public function __wakeup();
}
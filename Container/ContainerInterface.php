<?php
namespace Asgard\Container;

/**
 * Services container.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
interface ContainerInterface extends \ArrayAccess {
	/**
	 * Set a service parent class.
	 * @param  string    $name
	 * @param  string    $parent
	 * @param  boolean   $force
	 * @return ContainerInterface $this
	 * @api
	 */
	public function setParentClass($name, $parent, $force=false);

	/**
	 * Return the parent class.
	 * @param  string $name
	 * @return string
	 * @api
	 */
	public function getParentClass($name);

	/**
	 * Get the registry.
	 * @return array
	 * @api
	 */
	public function getRegistry();

	/**
	 * Get the instances.
	 * @return array
	 * @api
	 */
	public function getInstances();

	/**
	 * Set autofacade to true or false.
	 * @param  boolean            $facade
	 * @return ContainerInterface $this
	 * @api
	 */
	public function setAutofacade($facade);

	/**
	 * Get a service.
	 * @param  string $name
	 * @return mixed
	 * @api
	 */
	public function get($name);

	/**
	 * Set a service.
	 * @param  string    $name
	 * @param  mixed     $value
	 * @return ContainerInterface $this
	 * @api
	 */
	public function set($name, $value);

	/**
	 * Check if has a service.
	 * @param  string  $name
	 * @return boolean       true if service exists.
	 * @api
	 */
	public function has($name);

	/**
	 * Remove a service.
	 * @param  string $name
	 * @api
	 */
	public function remove($name);

	/**
	 * Register a service.
	 * @param  string $name
	 * @param  callable $callback
	 * @api
	 */
	public function register($name, $callback);

	/**
	 * Make a service.
	 * @param  string $name
	 * @param  array  $params
	 * @param  mixed $default
	 * @return mixed
	 * @api
	 */
	public function make($name, array $params=[], $default=null);

	/**
	 * Check if a service was registered.
	 * @param  string $name
	 * @return boolean      true if registered
	 * @api
	 */
	public function registered($name);

	/**
	 * Create a factory.
	 * @param  string|callable $what
	 * @return Factory
	 * @api
	 */
	public function createFactory($what);

	/**
	 * __wakeup magic method
	 */
	public function __wakeup();
}
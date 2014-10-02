<?php
namespace Asgard\Container;

/**
 * Provides an API to generate instances.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
class Factory {
	/**
	 * Callback to create instances.
	 * @var callable
	 */
	protected $callback;
	/**
	 * Services container.
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Constructor.
	 * @param callable $callback
	 * @param ContainerInterface $container
	 * @api
	 */
	public function __construct($callback, ContainerInterface $container=null) {
		$this->callback = $callback;
		$this->container = $container;
	}

	/**
	 * Create an instance.
	 * @param  array $params
	 * @return mixed
	 * @api
	 */
	public function create(array $params=[]) {
		$callback = $this->callback;
		return $callback($this->container, $params);
	}
}
<?php
namespace Asgard\Container;

/**
 * Provides an API to generate instances.
 */
class Factory {
	/**
	 * Callback to create instances.
	 * @var callable
	 */
	protected $callback;
	/**
	 * Services container.
	 * @var \Asgard\Container\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 * @param callable $callback
	 * @param \Asgard\Container\Container $container
	 */
	public function __construct($callback, $container=null) {
		$this->callback = $callback;
		$this->container = $container;
	}

	/**
	 * Create an instance.
	 * @param  array $params
	 * @return mixed
	 */
	public function create(array $params=[]) {
		$callback = $this->callback;
		return $callback($this->container, $params);
	}
}
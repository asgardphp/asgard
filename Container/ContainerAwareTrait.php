<?php
namespace Asgard\Container;

/**
 * Trait for classes using a container.
 */
trait ContainerAwareTrait {
	/**
	 * Container instance.
	 * @var \Asgard\Container\Container
	 */
	protected $container;

	/**
	 * Set the container.
	 * @param \Asgard\Container\Container $container
	 */
	public function setContainer(\Asgard\Container\Container $container) {
		$this->container = $container;
		return $this;
	}

	/**
	 * Get the container.
	 * @return \Asgard\Container\Container
	 */
	public function getContainer() {
		if(!$this->container)
			$this->container = Container::singleton();
		return $this->container;
	}
}
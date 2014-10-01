<?php
namespace Asgard\Container;

/**
 * Trait for classes using a container.
 */
trait ContainerAwareTrait {
	/**
	 * Container instance.
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Set the container.
	 * @param ContainerInterface $container
	 */
	public function setContainer(ContainerInterface $container=null) {
		$this->container = $container;
		return $this;
	}

	/**
	 * Get the container.
	 * @return ContainerInterface
	 */
	public function getContainer() {
		if(!$this->container)
			$this->container = Container::singleton();
		return $this->container;
	}
}
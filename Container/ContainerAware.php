<?php
namespace Asgard\Container;

trait ContainerAware {
	protected $container;

	public function setContainer($container) {
		$this->container = $container;
		return $this;
	}

	public function getContainer() {
		if(!$this->container)
			$this->container = Container::singleton();
		return $this->container;
	}
}
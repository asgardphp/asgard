<?php
namespace Asgard\Container;

trait ContainerAwareTrait {
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
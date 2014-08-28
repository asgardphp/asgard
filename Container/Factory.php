<?php
namespace Asgard\Container;

class Factory {
	protected $callback;
	protected $container;

	public function __construct($callback, $container=null) {
		$this->callback = $callback;
		$this->container = $container;
	}

	public function create(array $params=[]) {
		$callback = $this->callback;
		return $callback($this->container, $params);
	}
}
<?php
namespace Asgard\Entity;

class Behavior {
	protected $params;
	protected $definition;

	public function __construct($params=null) {
		$this->params = $params;
	}

	public function __sleep() {
		$properties = array_keys((array)$this);
		$k = array_search('definition', $properties);
		unset($properties[$k]);
		$k = array_search('app', $properties);
		unset($properties[$k]);
		return $properties;
	}

	public function getApp() {
		return $this->definition->getApp();
	}

	public function setDefinition(EntityDefinition $definition) {
		$this->definition = $definition;
	}

	public function load(EntityDefinition $definition) {}
}
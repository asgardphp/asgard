<?php
namespace Asgard\Entity;

class Behavior {
	protected $params;
	protected $definition;

	public function __construct($params=null) {
		$this->params = $params;
	}

	public function setDefinition(EntityDefinition $definition) {
		$this->definition = $definition;
	}

	public function load(EntityDefinition $definition) {}
}
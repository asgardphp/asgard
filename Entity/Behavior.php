<?php
namespace Asgard\Entity;

/**
 * Entity Behavior.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Behavior {
	/**
	 * Parameters.
	 * @var mixed
	 */
	protected $params;
	/**
	 * Entity definition.
	 * @var Definition
	 */
	protected $definition;

	/**
	 * Constructor.
	 * @param mixed $params
	 */
	public function __construct($params=null) {
		$this->params = $params;
	}

	/**
	 * __sleep magic method.
	 * @return array
	 */
	public function __sleep() {
		$properties = array_keys((array)$this);
		$k = array_search('definition', $properties);
		unset($properties[$k]);
		$k = array_search('app', $properties);
		unset($properties[$k]);
		return $properties;
	}

	/**
	 * Get entity definition container.
	 * @return \Asgard\Container\ContainerInterface
	 */
	public function getContainer() {
		return $this->definition->getContainer();
	}

	/**
	 * Set entity definition.
	 * @param Definition $definition
	 */
	public function setDefinition(Definition $definition) {
		$this->definition = $definition;
	}

	/**
	 * Load the bahavior.
	 * @param  Definition $definition
	 */
	public function load(Definition $definition) {}
}
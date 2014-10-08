<?php
namespace Asgard\Entity;

/**
 * Collection of many elements.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ManyCollection implements \ArrayAccess, \Iterator, \Countable {
	/**
	 * Elements.
	 * @var array
	 */
	protected $elements = [];
	/**
	 * Entity Definition.
	 * @var EntityDefinition
	 */
	protected $definition;
	/**
	 * Entity.
	 * @var Entity
	 */
	protected $entity;
	/**
	 * Name.
	 * @var string
	 */
	protected $name;

	/**
	 * Constructor.
	 * @param EntityDefinition $definition
	 * @param Entity           $entity
	 * @param string           $name
	 */
	public function __construct($definition, $entity, $name) {
		$this->definition = $definition;
		$this->entity     = $entity;
		$this->name       = $name;
	}

	/**
	 * Return number of elements.
	 * @return integer
	 */
	public function count() {
		return count($this->elements);
	}

	/**
	 * Return all elements.
	 * @return array
	 */
	public function all() {
		return $this->elements;
	}

	/**
	 * Add an element.
	 * @param mixed $element
	 */
	public function add($element) {
		if($element === null)
			return;
		$this->definition->processBeforeAdd($this->entity, $this->name, $element);
		$this->elements[] = $element;
		return $element;
	}

	/**
	 * Remove an element.
	 * @param  integer $offset
	 */
	public function remove($offset) {
		unset($this->elements[$offset]);
		$this->elements = array_values($this->elements);
	}

	/**
	 * Return an element.
	 * @param  integer $offset
	 * @return mixed
	 */
	public function get($offset) {
		if(!isset($this->elements[$offset]))
			return null;
		return $this->elements[$offset];
	}

	/**
	 * Array set implementation.
	 * @param  integer $offset
	 * @param  mixed   $value
	 * @throws \LogicException If $offset is null.
	 */
	public function offsetSet($offset, $value) {
		if(!is_null($offset))
			throw new \LogicException('Offset must be null.');
		else
			$this->add($value);
	}

	/**
	 * Array exists implementation.
	 * @param  integer $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return isset($this->elements[$offset]);
	}

	/**
	 * Array unset implementation.
	 * @param  integer $offset
	 */
	public function offsetUnset($offset) {
		$this->remove($offset);
	}

	/**
	 * Array get implementation.
	 * @param  integer $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * Iterator valid implementation.
	 * @return boolean
	 */
	public function valid() {
		$key = key($this->elements);
		return $key !== NULL && $key !== FALSE;
	}

	/**
	 * Iterator rewind implementation.
	 */
	public function rewind() {
		reset($this->elements);
	}

	/**
	 * Iterator current implementation.
	 * @return mixed
	 */
	public function current() {
		return current($this->elements);
	}

	/**
	 * Iterator key implementation.
	 * @return integer
	 */
	public function key()  {
		return key($this->elements);
	}

	/**
	 * Iterator next implementation.
	 * @return mixed
	 */
	public function next()  {
		return next($this->elements);
	}
}
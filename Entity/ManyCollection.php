<?php
namespace Asgard\Entity;

/**
 * 
 */
class ManyCollection implements \ArrayAccess, \Iterator, \Countable {
	/**
	 * [$elements description]
	 * @var [type]
	 */
	protected $elements = [];
	/**
	 * [$definition description]
	 * @var [type]
	 */
	protected $definition;
	/**
	 * [$entity description]
	 * @var [type]
	 */
	protected $entity;
	/**
	 * [$name description]
	 * @var [type]
	 */
	protected $name;

	/**
	 * [__construct description]
	 * @param [type] $definition
	 * @param [type] $entity
	 * @param [type] $name
	 */
	public function __construct($definition, $entity, $name) {
		$this->definition = $definition;
		$this->entity = $entity;
		$this->name = $name;
	}

	/**
	 * [count description]
	 * @return [type] [description]
	 */
	public function count() {
		return count($this->elements);
	}

	/**
	 * [all description]
	 * @return [type]
	 */
	public function all() {
		return $this->elements;
	}

	/**
	 * [add description]
	 * @param [type] $element
	 */
	public function add($element) {
		if($element === null)
			return;
		$this->definition->processBeforeAdd($this->entity, $this->name, $element);
		$this->elements[] = $element;
		return $element;
	}

	/**
	 * [remove description]
	 * @param  [type] $offset
	 * @return [type]
	 */
	public function remove($offset) {
		unset($this->elements[$offset]);
		$this->elements = array_values($this->elements);
	}

	/**
	 * [get description]
	 * @param  [type] $offset
	 * @return [type]
	 */
	public function get($offset) {
		if(!isset($this->elements[$offset]))
			return null;
		return $this->elements[$offset];
	}

	/**
	 * [offsetSet description]
	 * @param  [type] $offset
	 * @param  [type] $value
	 * @return [type]
	 */
	public function offsetSet($offset, $value) {
		if(!is_null($offset))
			throw new \LogicException('Offset must be null.');
		else
			$this->add($value);
	}

	/**
	 * [offsetExists description]
	 * @param  [type] $offset
	 * @return [type]
	 */
	public function offsetExists($offset) {
		return isset($this->elements[$offset]);
	}

	/**
	 * [offsetUnset description]
	 * @param  [type] $offset
	 * @return [type]
	 */
	public function offsetUnset($offset) {
		return $this->remove($offset);
	}

	/**
	 * [offsetGet description]
	 * @param  [type] $offset
	 * @return [type]
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}
	
	/**
	 * [valid description]
	 * @return [type]
	 */
	public function valid() {
		$key = key($this->elements);
		return $key !== NULL && $key !== FALSE;
	}

	/**
	 * [rewind description]
	 * @return [type]
	 */
	public function rewind() {
		reset($this->elements);
	}

	/**
	 * [current description]
	 * @return [type]
	 */
	public function current() {
		return current($this->elements);
	}

	/**
	 * [key description]
	 * @return [type]
	 */
	public function key()  {
		return key($this->elements);
	}

	/**
	 * [next description]
	 * @return function
	 */
	public function next()  {
		return next($this->elements);
	}
}
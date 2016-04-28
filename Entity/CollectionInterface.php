<?php
namespace Asgard\Entity;

interface CollectionInterface extends \ArrayAccess, \Iterator, \Countable {
	/**
	 * Return all elements.
	 * @return array
	 */
	public function all();

	/**
	 * Add an element.
	 * @param mixed $element
	 */
	public function add($element);

	/**
	 * Set all elements/
	 * @param array $elements
	 */
	public function setAll(array $elements);

	/**
	 * Remove an element.
	 * @param  integer $offset
	 */
	public function remove($offset);

	/**
	 * Return an element.
	 * @param  integer $offset
	 * @return mixed
	 */
	public function get($offset);

	/**
	 * Set collection as entity (modified).
	 * @param boolean $dirty
	 */
	public function setDirty($dirty=true);

	/**
	 * Check if collection is dirty.
	 * @return boolean
	 */
	public function isDirty();
}
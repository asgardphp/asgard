<?php
namespace Asgard\Orm;

class PersistentCollection implements \Asgard\Entity\CollectionInterface {
	/**
	 * Elements.
	 * @var array
	 */
	protected $elements = [];
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

	protected $isDirty;
	protected $toAdd = [];
	protected $toRemove = [];
	protected $dataMapper;
	protected $initialized = false;
	protected $cursor = 0;

	/**
	 * Constructor.
	 * @param Entity           $entity
	 * @param string           $name
	 * @param DataMapper       $dataMapper
	 */
	public function __construct(\Asgard\Entity\Entity $entity, $name, $dataMapper) {
		$this->entity     = $entity;
		$this->name       = $name;
		$this->dataMapper        = $dataMapper;
	}

	public function getORM() {
		return $this->dataMapper->related($this->entity, $this->name);
	}

	protected function initialize() {
		$this->elements = $this->getORM()->get();
		$this->initialized = true;
	}

	public function setInitialized($initialized) {
		$this->initialized = $initialized;
	}

	public function reset() {
		$this->elements = [];
		$this->toAdd = [];
		$this->toRemove = [];
		$this->initialized = false;
		$this->setDirty(false);
	}

	public function sync() {
		if(!$this->isDirty)
			return;

		#add
		if($entities = $this->toAdd)
			$this->getORM()->add($entities);

		#remove
		if($entities = $this->toRemove)
			$this->getORM()->remove($entities);

		$this->initialized = false;
		$this->initialized = false;
		$this->toAdd = [];
		$this->toRemove = [];
		$this->setDirty(false);
	}

	/**
	 * Return number of elements.
	 * @return integer
	 */
	public function count() {
		if(!$this->initialized)
			$this->initialize();

		return count($this->elements) - count($this->toRemove) + count($this->toAdd);
	}

	/**
	 * {@inheritDoc}
	 */
	public function all() {
		if(!$this->initialized)
			$this->initialize();

		return $this->elements;
	}

	/**
	 * {@inheritDoc}
	 */
	public function add($element) {
		if($element === null)
			return;
		$this->entity->getDefinition()->processPreAdd($this->entity, $this->name, $element);
		$this->toAdd[] = $element;
		$this->setDirty(true);

		return $this;
	}

	public function _add($element) {
		if($element === null)
			return;
		$this->elements[] = $element;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAll(array $elements) {
		$this->toRemove = $this->elements;
		$this->elements = [];
		foreach($elements as $e)
			$this->add($e);
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove($entity) {
		$this->toRemove[] = $entity;
		$this->setDirty(true);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($offset) {
		if(!$this->initialized)
			$this->initialize();

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
	 * Iterator rewind implementation.
	 */
	public function rewind() {
		if(!$this->initialized)
			$this->initialize();

		reset($this->elements);
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
	public function key() {
		return key($this->elements);
	}

	/**
	 * Iterator next implementation.
	 * @return mixed
	 */
	public function next() {
		return next($this->elements);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDirty($dirty=true) {
		$this->isDirty = $dirty;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isDirty() {
		return $this->isDirty;
	}
}
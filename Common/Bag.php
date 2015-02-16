<?php
namespace Asgard\Common;

/**
 * Bag to manipulate a set of data.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Bag implements BagInterface {
	/**
	 * Data.
	 * @var array
	 */
	protected $data = [];

	/**
	 * Constructor.
	 * @param array $data
	 */
	public function __construct(array $data=[]) {
		$this->set($data);
	}

	/**
	 * {@inheritDoc}
	 */
	public function all() {
		return $this->data;
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear() {
		$this->data = [];
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function size() {
		return count($this->data);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setAll($data) {
		return $this->clear()->set($data);
	}

	/**
	 * {@inheritDoc}
	 */
	public function set($path, $value=null) {
		#to set multiple elements at once
		if(is_array($path)) {
			foreach($path as $k=>$v)
				$this->set($k, $v);
		}
		else
			\Asgard\Common\ArrayUtils::set($this->data, $path, $value);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return \Asgard\Common\ArrayUtils::get($this->data, $path);
	}

	/**
	 * {@inheritDoc}
	 */
	public function has($path) {
		return \Asgard\Common\ArrayUtils::_isset($this->data, $path);
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete($path) {
		\Asgard\Common\ArrayUtils::_unset($this->data, $path);
		return $this;
	}

	/**
	 * Array set implementation.
	 * @param  integer $offset
	 * @param  mixed   $value
	 * @throws \LogicException If $offset is null.
	 */
	public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset must not be null.');
		else
			$this->set($offset, $value);
	}

	/**
	 * Array exists implementation.
	 * @param  integer $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return $this->has($offset);
	}

	/**
	 * Array unset implementation.
	 * @param  integer $offset
	 */
	public function offsetUnset($offset) {
		$this->delete($offset);
	}

	/**
	 * Array get implementation.
	 * @param  integer $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}
}
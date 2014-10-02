<?php
namespace Asgard\Common;

/**
 * Bag to manipulate a set of data.
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
	 * Return all data.
	 * @return array
	 */
	public function all() {
		return $this->data;
	}

	/**
	 * Clear data.
	 * @return Bag  $this
	 */
	public function clear() {
		$this->data = [];
		return $this;
	}

	/**
	 * Return number of elements.
	 * @return integer
	 */
	public function size() {
		return count($this->data);
	}

	/**
	 * Set all elements.
	 * @param array $data
	 * @return Bag  $this
	 */
	public function setAll($data) {
		return $this->clear()->set($data);
	}

	/**
	 * Set a value.
	 * @param string|array $path    nested keys separated by ".".
	 * @param mixed        $value
	 * @return Bag         $this
	 */
	public function set($path, $value=null) {
		#to set multiple elements at once
		if(is_array($path)) {
			foreach($path as $k=>$v)
				$this->set($k, $v);
		}
		else
			\Asgard\Common\ArrayUtils::string_array_set($this->data, $path, $value);

		return $this;
	}

	/**
	 * Get a value.
	 * @param string $path    nested keys separated by ".".
	 * @param mixed  $default
	 * @return mixed
	 */
	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return \Asgard\Common\ArrayUtils::string_array_get($this->data, $path);
	}

	/**
	 * Check if has element.
	 * @param string $path    nested keys separated by ".".
	 * @return boolean
	 */
	public function has($path) {
		return \Asgard\Common\ArrayUtils::string_array_isset($this->data, $path);
	}

	/**
	 * Delete an element.
	 * @param string $path    nested keys separated by ".".
	 * @return Bag  $this
	 */
	public function delete($path) {
		\Asgard\Common\ArrayUtils::string_array_unset($this->data, $path);
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
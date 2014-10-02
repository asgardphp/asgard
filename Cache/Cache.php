<?php
namespace Asgard\Cache;

/**
 * Cache wrapper.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
class Cache implements \Doctrine\Common\Cache\Cache, \ArrayAccess {
	/**
	 * Cache driver.
	 * @var \Doctrine\Common\Cache\Cache
	 */
	protected $driver;

	/**
	 * Constructor.
	 * @param \Doctrine\Common\Cache\Cache $driver Doctrine cache object
	 * @api
	 */
	public function __construct($driver=null) {
		if($driver == null)
			$driver = new NullCache;
		$this->driver = $driver;
	}

	/**
	 * Set the driver
	 * @param \Doctrine\Common\Cache\Cache $driver Doctrine cache object
	 * @api
	 */
	public function setDriver($driver) {
		$this->driver = $driver;
	}

	/**
	 * Fetch a value.
	 * @param  string  $id
	 * @param  mixed  $default
	 * @return mixed
	 * @api
	 */
	public function fetch($id, $default=false) {
		$res = $this->driver->fetch($id);

		if($res === false) {
			if($default === false)
				return false;
			if(is_callable($default))
				$res = $default();
			else
				$res = $default;
			$this->save($id, $res);
		}

		return $res;
	}

	/**
	 * Check if contains a key.
	 * @param  string $id
	 * @return boolean
	 * @api
	 */
	public function contains($id) {
		return $this->driver->contains($id);
	}

	/**
	 * Save a key-value.
	 * @param  string  $id
	 * @param  mixed   $data
	 * @param  integer $lifeTime
	 * @return boolean
	 * @api
	 */
	public function save($id, $data, $lifeTime=0) {
		return $this->driver->save($id, $data, $lifeTime);
	}

	/**
	 * Delete a key.
	 * @param  string $id
	 * @return boolean
	 * @api
	 */
	public function delete($id) {
		return $this->driver->delete($id);
	}

	/**
	 * Return cache statistics.
	 * @return array
	 * @api
	 */
	public function getStats() {
		return $this->driver->getStats();
	}

	/**
	 * Array set method
	 * @param  integer $offset
	 * @param  mixed   $value
	 * @return boolean          success
	 * @api
	 */
	public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset cannot be empty.');
		else
			$this->save($offset, $value);
	}

	/**
	 * Array exists method
	 * @param  integer $offset
	 * @return boolean         if offset exists
	 * @api
	 */
	public function offsetExists($offset) {
		return $this->contains($offset);
	}

	/**
	 * Array unset method
	 * @param  integer $offset
	 * @return boolean         success
	 * @api
	 */
	public function offsetUnset($offset) {
		return $this->delete($offset);
	}


	/**
	 * Array get method
	 * @param  integer $offset
	 * @return boolean         value
	 * @api
	 */
	public function offsetGet($offset) {
		return $this->fetch($offset);
	}
}
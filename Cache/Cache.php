<?php
namespace Asgard\Cache;

class Cache implements \Doctrine\Common\Cache\Cache, \ArrayAccess {
	protected $driver;

	/**
	 * Constructor.
	 * @param \Doctrine\Common\Cache\Cache $driver Doctrine cache object
	 */
	public function __construct($driver=null) {
		if($driver == null)
			$driver = new NullCache;
		$this->driver = $driver;
	}

	/**
	 * Set the driver
	 * @param \Doctrine\Common\Cache\Cache $driver Doctrine cache object
	 */
	public function setDriver($driver) {
		$this->driver = $driver;
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function contains($id) {
		return $this->driver->contains($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save($id, $data, $lifeTime=0) {
		return $this->driver->save($id, $data, $lifeTime);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($id) {
		return $this->driver->delete($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStats() {
		return $this->driver->getStats();
	}
	
	/**
	 * Array set method
	 * @param  integer $offset
	 * @param  mixed   $value
	 * @return boolean          success
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
	 */
    public function offsetExists($offset) {
		return $this->contains($offset);
    }
	
	/**
	 * Array unset method
	 * @param  integer $offset
	 * @return boolean         success
	 */
    public function offsetUnset($offset) {
		return $this->delete($offset);
    }
	
	
	/**
	 * Array get method
	 * @param  integer $offset
	 * @return boolean         value
	 */
    public function offsetGet($offset) {
		return $this->fetch($offset);
    }
}
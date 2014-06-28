<?php
namespace Asgard\Cache;

class Cache implements \Doctrine\Common\Cache\Cache, \ArrayAccess {
	protected $driver;

	public function __construct($driver=null) {
		if($driver == null)
			$driver = new NullCache;
		$this->driver = $driver;
	}

	public function setDriver($driver) {
		$this->driver = $driver;
	}

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

	public function contains($id) {
		return $this->driver->contains($id);
	}

	public function save($id, $data, $lifeTime=0) {
		return $this->driver->save($id, $data, $lifeTime);
	}

	public function delete($id) {
		return $this->driver->delete($id);
	}

	public function getStats() {
		return $this->driver->getStats();
	}
	
    public function offsetSet($offset, $value) {
		if(is_null($offset))
			throw new \LogicException('Offset cannot be empty.');
		else
			$this->save($offset, $value);
    }
	
    public function offsetExists($offset) {
		return $this->contains($offset);
    }
	
    public function offsetUnset($offset) {
		return $this->delete($offset);
    }
	
    public function offsetGet($offset) {
		return $this->fetch($offset);
    }
}
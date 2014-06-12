<?php
namespace Asgard\Common;

class Bag implements \ArrayAccess {
	protected $data = [];
	
	public function __construct($data=[]) {
		$this->load($data);
	}
	
	public function load(array $data) {
		foreach($data as $key=>$value)
			$this->set($key, $value);
		return $this;
	}

	public function all() {
		return $this->data;
	}

	public function clear() {
		$this->data = [];
		return $this;
	}

	public function size() {
		return count($this->data);
	}

	public function setAll($data) {
		return $this->clear()->set($data);
	}
	
	public function set($path, $value=null) {
		if(is_array($path)) {
			foreach($path as $k=>$v)
				$this->set($k, $v);
		}
		else
			\Asgard\Common\ArrayUtils::string_array_set($this->data, $path, $value);
		return $this;
	}
	
	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return \Asgard\Common\ArrayUtils::string_array_get($this->data, $path);
	}
	
	public function has($path) {
		return \Asgard\Common\ArrayUtils::string_array_isset($this->data, $path);
	}
	
	public function delete($path) {
		return \Asgard\Common\ArrayUtils::string_array_unset($this->data, $path);
	}

    public function offsetSet($offset, $value) {
        if(is_null($offset))
            throw new \LogicException('Offset must not be null.');
        else
       		$this->set($offset, $value);
    }

    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function offsetUnset($offset) {
        return $this->delete($offset);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }
}
<?php
namespace Asgard\Entity;

class Multiple implements \ArrayAccess, \Iterator {
	protected $elements = array();

	public function all() {
		return $this->elements;
	}

	public function add($element) {
		$this->elements[] = $element;
	}

	public function remove($offset) {
		unset($this->elements[$offset]);
		$this->elements = array_values($this->elements);
	}

	public function get($offset) {
		if(!isset($this->elements[$offset]))
			return null;
		return $this->elements[$offset];
	}

    public function offsetSet($offset, $value) {
        if(!is_null($offset))
            throw new \LogicException('Offset must be null.');
        else
       		$this->add($value);
    }

    public function offsetExists($offset) {
        throw new \LogicException('Not implemented.');
    }

    public function offsetUnset($offset) {
        return $this->remove($offset);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }
	
    public function valid() {
		$key = key($this->elements);
		return $key !== NULL && $key !== FALSE;
    }

    public function rewind() {
		reset($this->elements);
    }
  
    public function current() {
		return current($this->elements);
    }
  
    public function key()  {
		return key($this->elements);
    }
  
    public function next()  {
		return next($this->elements);
    }
}
<?php
namespace Asgard\Http\Inputs;

abstract class InputsBag implements \ArrayAccess {
	protected $inputs;

	public function __construct($inputs=array()) {
		$this->inputs = $inputs;
	}

	public function offsetSet($offset, $value) {
		if(is_null($offset))
			$this->inputs[] = $value;
		else
			$this->inputs[$offset] = $value;
	}

	public function offsetExists($offset) {
		return isset($this->inputs[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->inputs[$offset]);
	}
	
	public function offsetGet($offset) {
		return isset($this->inputs[$offset]) ? $this->inputs[$offset] : null;
	}

	public function get($name, $default=null) {
		return \Asgard\Utils\Tools::array_get($this->inputs, $name, $default);
	}

	public function set($name, $value=null) {
		if(is_array($name) && $value===null) {
			foreach($name as $k=>$v)
				$this->set($k, $v);
		}
		else
			\Asgard\Utils\Tools::array_set($this->inputs, $name, $value);
		return $this;
	}

	public function has($name) {
		return \Asgard\Utils\Tools::array_isset($this->inputs, $name);
	}

	public function remove($name) {
		\Asgard\Utils\Tools::array_unset($this->inputs, $name);
		return $this;
	}

	public function all() {
		return $this->inputs;
	}

	public function clear() {
		$this->inputs = array();
		return $this;
	}

	public function setAll($all) {
		return $this->clear()->set($all);
	}

	public function _setAll($all) {
		return $this->clear()->_set($all);
	}

	public function _set($what, $value=null) {
		return $this->set($what, $value);
	}

	public function size() {
		return count($this->inputs);
	}
}
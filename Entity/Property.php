<?php
namespace Asgard\Entity;

use Jeremeamia\SuperClosure\SerializableClosure;

class Property {
	protected $position;
	protected $definition;
	protected $name;
	public $params = array();

	public function __construct($params) {
		$this->params = $params;
	}

	public function __sleep() {
		foreach($this->params as $k=>$v) {
			if($v instanceof \Closure)
				$this->params[$k] = new SerializableClosure($v);
		}
		return array('position', 'definition', 'name', 'params');
	}

	public function setPosition($position) {
		$this->params['position'] = $position;
		return $this;
	}

	public function getPosition() {
		return isset($this->params['position']) ? $this->params['position']:null;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function setDefinition($definition) {
		$this->definition = $definition;
	}

	public function required() {
		if(isset($this->params['required']))
			return $this->params['required'];
		if(isset($this->params['validation']['required']))
			return $this->params['validation']['required'];
	}

	public function __get($what) {
		return $this->get($what);
	}

	public function get($what) {
		if(!isset($this->params[$what]))
			return;
		return $this->params[$what];
	}

	public function getParams() {
		return $this->params;
	}

	public function getName() {
		return $this->name;
	}

	public function __toString() {
		return $this->getName();
	}

	public function getDefault() {
		if($this->get('multiple'))
			return new Multiple();
		elseif(isset($this->params['default'])) {
			if(is_callable($this->params['default']))
				return $this->params['default']();
			else
				return $this->params['default'];
		}
		else
			return $this->_getDefault();
	}

	protected function _getDefault() {
		return '';
	}

	public function getRules() {
		$res = isset($this->params['validation']) ? $this->params['validation']:array();
		if(!is_array($res))
			$res = array('validation' => $res);
		if($this->get('required'))
			$res['required'] = true;

		return $res;
	}

	public function getMessages() {
		return array();
	}

	public function serialize($val) {
		if($this->get('multiple')) {
			if(!$val instanceof Multiple)
				return serialize(array());
			$r = array();
			foreach($val as $v)
				$r[] = $this->doSerialize($v);
			return serialize($r);
		}
		else
			return $this->doSerialize($val);
	}

	protected function doSerialize($val) {
		return (string)$val;
	}

	public function unserialize($str) {
		if($this->get('multiple')) {
			$arr = unserialize($str);
			if(!is_array($arr))
				return array();
			$r = new Multiple();
			foreach($arr as $v)
				$r[] = $this->doUnserialize($v);
			return $r;
		}
		else
			return $this->doUnserialize($str);
	}

	protected function doUnserialize($str) {
		return $str;
	}

	public function set($val) {
		if($this->get('multiple')) {
			if($val instanceof Multiple)
				return $val;
			$res = new Multiple();
			if(is_array($val)) {
				foreach($val as $v)
					$res[] = $this->doSet($v);
			}
			return $res;
		}
		else
			return $this->doSet($val);
	}

	protected function doSet($val) {
		return $val;
	}
}
<?php
namespace Asgard\Entity;

use Jeremeamia\SuperClosure\SerializableClosure;

class Property {
	protected $entity;
	protected $position;
	protected $definition;
	protected $name;
	public $params = [];

	public function __construct($params) {
		$this->params = $params;
	}

	public function __sleep() {
		foreach($this->params as $k=>$v) {
			if($v instanceof \Closure)
				$this->params[$k] = new SerializableClosure($v);
		}
		return ['position', 'definition', 'name', 'params'];
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

	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return \Asgard\Common\ArrayUtils::string_array_get($this->params, $path);
	}
	
	public function has($path) {
		return \Asgard\Common\ArrayUtils::string_array_isset($this->params, $path);
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

	public function getDefault($entity, $name) {
		if($this->get('multiple'))
			return new Multiple($this->definition, $entity, $name);
		elseif(isset($this->params['default'])) {
			if(is_callable($this->params['default']))
				return $this->params['default']();
			else
				return $this->params['default'];
		}
		else
			return $this->_getDefault($entity);
	}

	protected function _getDefault() {
		return '';
	}

	public function getRules() {
		$res = isset($this->params['validation']) ? $this->params['validation']:[];
		if(!is_array($res))
			$res = [$res];
		if($this->get('required'))
			$res['required'] = true;

		return $res;
	}

	public function getMessages() {
		return [];
	}

	public function serialize($val) {
		if($this->get('multiple')) {
			if(!$val instanceof Multiple)
				return serialize([]);
			$r = [];
			foreach($val as $v) {
				$s = $this->doSerialize($v);
				if($s !== null)
					$r[] = $s;
			}
			return serialize($r);
		}
		else
			return $this->doSerialize($val);
	}

	protected function doSerialize($val) {
		if(is_string($val) || is_numeric($val) || is_bool($val) || is_null($val))
			return $val;
		else
			return serialize($val);
	}

	public function unserialize($str, $entity, $name) {
		if($this->get('multiple')) {
			$arr = unserialize($str);
			if(!is_array($arr))
				return [];
			$r = new Multiple($this->definition, $entity, $name);
			foreach($arr as $v)
				$r[] = $this->doUnserialize($v, $entity);
			return $r;
		}
		else
			return $this->doUnserialize($str, $entity);
	}

	protected function doUnserialize($str) {
		$json = json_decode($str);
		if($json === null)
			return $str;
		return $json;
	}

	public function set($val, $entity, $name) {
		if($this->get('multiple')) {
			if($val instanceof Multiple)
				return $val;
			$res = new Multiple($this->definition, $entity, $name);
			if(is_array($val)) {
				foreach($val as $v)
					$res[] = $this->doSet($v, $entity);
			}
			return $res;
		}
		else
			return $this->doSet($val, $entity);
	}

	public function doSet($val) {
		return $val;
	}
}
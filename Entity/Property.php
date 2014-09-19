<?php
namespace Asgard\Entity;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * 
 */
class Property {
	/**
	 * [$entity description]
	 * @var [type]
	 */
	protected $entity;
	/**
	 * [$position description]
	 * @var [type]
	 */
	protected $position;
	/**
	 * [$definition description]
	 * @var [type]
	 */
	protected $definition;
	/**
	 * [$name description]
	 * @var [type]
	 */
	protected $name;
	/**
	 * [$params description]
	 * @var [type]
	 */
	public $params = [];

	/**
	 * [__construct description]
	 * @param [type] $params
	 */
	public function __construct($params) {
		$this->params = $params;
	}

	/**
	 * [__sleep description]
	 * @return array
	 */
	public function __sleep() {
		foreach($this->params as $k=>$v) {
			if($v instanceof \Closure)
				$this->params[$k] = new SerializableClosure($v);
		}
		return ['position', 'definition', 'name', 'params'];
	}

	/**
	 * [setPosition description]
	 * @param [type] $position
	 */
	public function setPosition($position) {
		$this->params['position'] = $position;
		return $this;
	}

	/**
	 * [getPosition description]
	 * @return [type]
	 */
	public function getPosition() {
		return isset($this->params['position']) ? $this->params['position']:null;
	}

	/**
	 * [setName description]
	 * @param [type] $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * [setDefinition description]
	 * @param [type] $definition
	 */
	public function setDefinition($definition) {
		$this->definition = $definition;
	}

	/**
	 * [required description]
	 * @return [type]
	 */
	public function required() {
		if(isset($this->params['required']))
			return $this->params['required'];
		if(isset($this->params['validation']['required']))
			return $this->params['validation']['required'];
	}

	/**
	 * [__get description]
	 * @param  [type] $what
	 * @return [type]
	 */
	public function __get($what) {
		return $this->get($what);
	}

	/**
	 * [get description]
	 * @param  [type] $path
	 * @param  [type] $default
	 * @return [type]
	 */
	public function get($path, $default=null) {
		if(!$this->has($path))
			return $default;
		return \Asgard\Common\ArrayUtils::string_array_get($this->params, $path);
	}
	
	/**
	 * [has description]
	 * @param  [type]  $path
	 * @return boolean
	 */
	public function has($path) {
		return \Asgard\Common\ArrayUtils::string_array_isset($this->params, $path);
	}

	/**
	 * [getParams description]
	 * @return [type]
	 */
	public function getParams() {
		return $this->params;
	}

	/**
	 * [getName description]
	 * @return [type]
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * [__toString description]
	 * @return string
	 */
	public function __toString() {
		return $this->getName();
	}

	/**
	 * [getDefault description]
	 * @param  [type] $entity
	 * @param  [type] $name
	 * @return [type]
	 */
	public function getDefault($entity, $name) {
		if($this->get('many'))
			return new ManyCollection($this->definition, $entity, $name);
		elseif(isset($this->params['default'])) {
			if(is_callable($this->params['default']))
				return $this->params['default']();
			else
				return $this->params['default'];
		}
		else
			return $this->_getDefault($entity);
	}

	/**
	 * [_getDefault description]
	 * @return [type]
	 */
	protected function _getDefault() {
		return null;
	}

	/**
	 * [getRules description]
	 * @return [type]
	 */
	public function getRules() {
		$res = isset($this->params['validation']) ? $this->params['validation']:[];
		if(!is_array($res))
			$res = [$res];
		if($this->get('required'))
			$res['required'] = true;

		return $res;
	}

	/**
	 * [getMessages description]
	 * @return [type]
	 */
	public function getMessages() {
		if(isset($this->params['messages']))
			return $this->params['messages'];
	}

	/**
	 * [serialize description]
	 * @param  [type] $val
	 * @return [type]
	 */
	public function serialize($val) {
		if($this->get('many')) {
			if(!$val instanceof ManyCollection)
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

	/**
	 * [doSerialize description]
	 * @param  [type] $val
	 * @return [type]
	 */
	protected function doSerialize($val) {
		if(is_string($val) || is_numeric($val) || is_bool($val) || is_null($val))
			return $val;
		else
			return serialize($val);
	}

	/**
	 * [unserialize description]
	 * @param  [type] $str
	 * @param  [type] $entity
	 * @param  [type] $name
	 * @return [type]
	 */
	public function unserialize($str, $entity, $name) {
		if($this->get('many')) {
			$r = new ManyCollection($this->definition, $entity, $name);
			$arr = unserialize($str);
			if(!is_array($arr))
				return $r;
			foreach($arr as $v)
				$r[] = $this->doUnserialize($v, $entity);
			return $r;
		}
		else
			return $this->doUnserialize($str, $entity);
	}

	/**
	 * [doUnserialize description]
	 * @param  [type] $str
	 * @return [type]
	 */
	protected function doUnserialize($str) {
		$json = json_decode($str);
		if($json === null)
			return $str;
		return $json;
	}

	/**
	 * [set description]
	 * @param [type] $val
	 * @param [type] $entity
	 * @param [type] $name
	 */
	public function set($val, $entity, $name) {
		if($this->get('many')) {
			if($val instanceof ManyCollection)
				return $val;
			$res = new ManyCollection($this->definition, $entity, $name);
			if(is_array($val)) {
				foreach($val as $v)
					$res[] = $this->doSet($v, $entity);
			}
			return $res;
		}
		else
			return $this->doSet($val, $entity);
	}

	/**
	 * [doSet description]
	 * @param  [type] $val
	 * @return [type]
	 */
	public function doSet($val) {
		return $val;
	}
}
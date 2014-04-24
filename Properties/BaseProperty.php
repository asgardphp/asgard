<?php
namespace Asgard\Core\Properties;

class BaseProperty {
	protected $entity = null;
	protected $name = null;
	public $params = array();

	public function __construct($entity, $name, $params) {
		$this->entity = $entity;
		$this->name = $name;
		$this->params = $params;
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
		if(isset($this->params['default'])) {
			if(is_callable($this->params['default']))
				return $this->params['default']();
			else
				return $this->params['default'];
		}
		else
			return $this->_getDefault();
	}

	public function _getDefault() {
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

	public function serialize($obj) {
		return (string)$obj;
	}

	public function unserialize($str) {
		return $str;
	}

	public function set($val) {
		return $val;
	}
}
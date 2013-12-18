<?php
namespace Coxis\Core\Properties;

class BaseProperty {
	protected $entity = null;
	protected $name = null;
	public $params = array();

	function __construct($entity, $name, $params) {
		$this->entity = $entity;
		$this->name = $name;
		$this->params = $params;
	}

	public function __get($str_path) {
		return \Coxis\Utils\Tools::pathGet($this->params, $str_path);
	}

	public function __toString() {
		return $this->getName();
	}

	public function getName() {
		return $this->name;
	}

	public function getParams() {
		return $this->params;
	}

	public function getDefault() {
		if(isset($this->params['default']))
			return $this->params['default'];
		elseif(method_exists($this, '_getDefault'))
			return $this->_getDefault();
		else
			return '';
	}

	public function getRules() {
		$res = $this->params;
		$res[$res['type']] = true;
		unset($res['type']);
		unset($res['setHook']);
		if($this->i18n)
			$res['is_array'] = true;
		return $res;
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
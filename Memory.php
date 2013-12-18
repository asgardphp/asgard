<?php
namespace Coxis\Core;

class Memory {
	protected $registry = array();
	
	public function set($str_path, $value) {
		\Coxis\Utils\Tools::pathSet($this->registry, $str_path, $value);
	}
	
	public function get($str_path) {
		return \Coxis\Utils\Tools::pathGet($this->registry, $str_path);
	}
}
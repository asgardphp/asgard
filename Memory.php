<?php
namespace Asgard\Core;

class Memory {
	protected $registry = array();
	
	public function set($str_path, $value) {
		\Asgard\Utils\Tools::pathSet($this->registry, $str_path, $value);
	}
	
	public function get($str_path) {
		return \Asgard\Utils\Tools::pathGet($this->registry, $str_path);
	}
}
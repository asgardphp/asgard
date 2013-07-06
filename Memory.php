<?php
namespace Coxis\Core;

class Memory {
	protected $arrs = array();
	
	public function set() {
		$args = func_get_args();
		$arr =& $this->arrs;
		$key = $args[sizeof($args)-2];
		$value = $args[sizeof($args)-1];
		array_pop($args);
		array_pop($args);
		
		foreach($args as $parent)
			$arr =& $arr[$parent];
		$arr[$key] = $value;
	}
	
	public function get() {
		//todo use access()
		$args = func_get_args();
		$result = $this->arrs;
		foreach(func_get_args() as $key)
			if(!isset($result[$key]))
				return null;
			else
				$result = $result[$key];
		
		return $result;
	}
}
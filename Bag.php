<?php
namespace Asgard\Tester;

class Bag extends \Asgard\Common\Bag {
	public $accessed = [];

	public function get($path, $default=null) {
		$this->accessed[] = $path;
		return parent::get($path, $default);
	}

	public function size() {
		return count($this->accessed);
	}

	public function getAccessed() {
		$all = $this->all();
		$accessed = array_unique($this->accessed);

		$res = [];

		foreach($all as $k=>$v) {
			if(in_array($k, $accessed))
				$res[$k] = $v;
		}

		return $res;
	}
}
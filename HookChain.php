<?php
namespace Asgard\Hook;

class HookChain {
	public $calls;
	protected $continue = true;
	public $executed = 0;

	function __construct($calls=array()) {
		$this->calls = $calls;
	}

	public function run($args, $print) {
		foreach($this->calls as $call) {
			$res = call_user_func_array($call, array_merge(array($this), $args));
			$this->executed++;
			if($print)
				echo $res;
			else {
				if($res !== null)
					return $res;
				if(!$this->continue)
					return;
			}
		}
	}

	public function stop() {
		$this->continue = false;
	}
}
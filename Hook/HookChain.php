<?php
namespace Asgard\Hook;

class HookChain {
	public $calls;
	protected $continue = true;
	public $executed = 0;
	public $app;

	public function __construct(\Asgard\Core\App $app) {
		$this->app = $app;
	}

	public function run(array $args, $print) {
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
<?php
namespace Asgard\Hook;

/**
 * Chain of hooks to be executed.
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
class HookChain {
	public $calls;
	protected $continue = true;
	public $executed = 0;
	public $app;
	
	/**
	 * Constructor.
	 * 
	 * @param \Asgard\Container\Container app Application container.
	*/
	public function __construct($app=null) {
		$this->app = $app;
	}
	
	/**
	 * Executes the chain.
	 * 
	 * @return mixed
	*/
	public function run(array $args) {
		foreach($this->calls as $call) {
			if($call instanceof \Jeremeamia\SuperClosure\SerializableClosure)
				$call = $call->getClosure();
			$res = call_user_func_array($call, array_merge([$this], $args));
			$this->executed++;
			if($res !== null)
				return $res;
			if(!$this->continue)
				return;
		}
	}
	
	/**
	 * Stops the execution.
	 * 
	 * @api 
	*/
	public function stop() {
		$this->continue = false;
	}
}
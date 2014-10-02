<?php
namespace Asgard\Hook;

/**
 * Chain of hooks to be executed.
 * @author Michel Hognerud <michel@hognerud.net>
*/
class HookChain {
	use \Asgard\Container\ContainerAwareTrait;

	/**
	 * All callbacks.
	 * @var array
	 */
	public $calls;
	/**
	 * To continue the chain calls.
	 * @var boolean
	 */
	protected $continue = true;
	/**
	 * Number of executed calls.
	 * @var integer
	 */
	public $executed = 0;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container Application container.
	*/
	public function __construct(\Asgard\Container\ContainerInterface $container=null) {
		$this->container = $container;
	}

	/**
	 * Execute the chain.
	 * @param array $args
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
	 * Stop the execution.
	*/
	public function stop() {
		$this->continue = false;
	}
}
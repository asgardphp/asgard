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
	protected $calls;
	/**
	 * Number of executed calls.
	 * @var integer
	 */
	protected $executed = 0;
	/**
	 * To continue the chain calls.
	 * @var boolean
	 */
	protected $continue = true;

	/**
	 * Constructor.
	 * @param \Asgard\Container\ContainerInterface $container Application container.
	*/
	public function __construct(\Asgard\Container\ContainerInterface $container=null) {
		$this->container = $container;
	}

	/**
	 * Set calls.
	 * @param array $calls
	 */
	public function setCalls(array $calls) {
		$this->calls = $calls;
	}

	/**
	 * Return the number of executed calls.
	 * @return integer
	 */
	public function executed() {
		return $this->executed;
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
<?php
namespace Asgard\Hook;

/**
 * Extend "Hookable" to make hookable instances.
 * @author Michel Hognerud <michel@hognerud.net>
*/
trait HookableTrait {
	/**
	 * HooksManager dependency.
	 * @var HooksManagerInterface
	 */
	protected $hooksManager;
	
	/**
	 * Check if has a hook.
	 * @param string $name
	 * @return boolean
	*/
	public function hasHook($name) {
		if(!$this->getHooksManager())
			return false;
		return $this->getHooksManager()->has($name);
	}
	
	/**
	 * Trigger a hook.
	 * @param string    $name
	 * @param array     $args
	 * @param callable  $cb
	 * @param HookChain $chain
	 * @return mixed
	*/
	public function trigger($name, array $args=[], $cb=null, &$chain=null) {
		if(!$this->getHooksManager())
			return;
		return $this->getHooksManager()->trigger($name, $args, $cb, $chain);
	}
	
	/**
	 * Set a hook.
	 * @param string   $hookName
	 * @param callable $cb
	 * @return mixed
	*/
	public function hook($hookName, $cb) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hook'], $args);
	}
	
	/**
	 * Set a "before" hook.
	 * @param string   $hookName
	 * @param Callable $cb
	 * @return mixed
	*/
	public function hookBefore($hookName, $cb) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hookBefore'], $args);
	}
	
	/**
	 * Set an "after" hook.
	 * 
	 * @param string   $hookName
	 * @param callable $cb
	 * @return mixed
	*/
	public function hookAfter($hookName, $cb) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hookAfter'], $args);
	}
	
	/**
	 * Get the hooks manager.
	 * @return HooksManagerInterface
	*/
	public function getHooksManager() {
		if(!$this->hooksManager)
			$this->hooksManager = new \Asgard\Hook\HooksManager;
		return $this->hooksManager;
	}
	
	/**
	 * Set the hooks manager.
	 * @param HooksManagerInterface $hooksManager
	*/
	public function setHooksManager(HooksManagerInterface $hooksManager) {
		$this->hooksManager = $hooksManager;
	}
}

<?php
namespace Asgard\Hook;

/**
 * Extend "Hookable" to make hookable instances.
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
class Hookable {
	protected $hooksManager;
	
	/**
	 * Checks if has a hook.
	 * 
	 * @param string name
	 * 
	 * @return boolean
	 * 
	 * @api 
	*/
	public function hasHook($name) {
		if(!$this->getHooksManager()) return false;
		return $this->getHooksManager()->has($name);
	}
	
	/**
	 * Triggers a hook.
	 * 
	 * @param string name
	 * @param Callback cb
	 * 
	 * @return mixed
	 * 
	 * @api 
	*/
	public function trigger($name, array $args=[], $cb=null) {
		if(!$this->getHooksManager()) return;
		return $this->getHooksManager()->trigger($name, $args, $cb);
	}
	
	/**
	 * Triggers a hook with a given chain.
	 * 
	 * @param string name
	 * @param array args
	 * @param Callback cb
	 * @param boolean print
	 * 
	 * @api 
	*/
	public function triggerChain(HookChain $chain, $name, array $args=[], $cb=null, $print=false) {
		if(!$this->getHooksManager()) return;
		return $this->getHooksManager()->triggerChain($chain, $name, $args, $cb, $print);
	}
	
	/**
	 * Sets a hook.
	 * 
	 * @param string hookName
	 * @param Callback cb
	 * @param integer priority Hook priority.
	 * 
	 * @return mixed
	 * 
	 * @api 
	*/
	public function hook($hookName, $cb, $priority=0) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hook'], $args);
	}
	
	/**
	 * Sets a "before" hook.
	 * 
	 * @param string hookName
	 * @param Callback cb
	 * @param intege priority Hook priority.
	 * 
	 * @return mixed
	 * 
	 * @api 
	*/
	public function hookBefore($hookName, $cb, $priority=0) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hookBefore'], $args);
	}
	
	/**
	 * Sets an
	 * 
	 * @param string hookName
	 * @param Callback cb
	 * @param integer priority Hook priority.
	 * 
	 * @return mixed
	 * 
	 * @api 
	*/
	public function hookAfter($hookName, $cb, $priority=0) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hookAfter'], $args);
	}
	
	/**
	 * Returns all the hooks for this instance.
	 * 
	 * @return array
	*/
	public function hooks() {
		if(!$this->getHooksManager()) return [];
		return $this->getHooksManager()->getHooks();
	}
	
	/**
	 * Gets the hooks manager.
	 * 
	 * @return HooksManager
	*/
	public function getHooksManager() {
		if(!$this->hooksManager)
			$this->hooksManager = new \Asgard\Hook\HooksManager;
		return $this->hooksManager;
	}
	
	/**
	 * Sets the hooks manager.
	 * 
	 * @param HooksManager hooksManager
	*/
	public function setHook(HooksManager $hooksManager) {
		$this->hooksManager = $hooksManager;
	}
}

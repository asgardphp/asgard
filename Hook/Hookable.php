<?php
namespace Asgard\Hook;

/**
 * Extend "Hookable" to make hookable instances.
 * 
 * @author Michel Hognerud <michel@hognerud.net>
*/
trait Hookable {
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
	public function trigger($name, array $args=[], $cb=null, &$chain=null) {
		if(!$this->getHooksManager()) return;
		return $this->getHooksManager()->trigger($name, $args, $cb, $chain);
	}
	
	/**
	 * Sets a hook.
	 * 
	 * @param string hookName
	 * @param Callback cb
	 * 
	 * @return mixed
	 * 
	 * @api 
	*/
	public function hook($hookName, $cb) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hook'], $args);
	}
	
	/**
	 * Sets a "before" hook.
	 * 
	 * @param string hookName
	 * @param Callback cb
	 * 
	 * @return mixed
	 * 
	 * @api 
	*/
	public function hookBefore($hookName, $cb) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hookBefore'], $args);
	}
	
	/**
	 * Sets an
	 * 
	 * @param string hookName
	 * @param Callback cb
	 * 
	 * @return mixed
	 * 
	 * @api 
	*/
	public function hookAfter($hookName, $cb) {
		$args = [$hookName, $cb];
		return call_user_func_array([$this->getHooksManager(), 'hookAfter'], $args);
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

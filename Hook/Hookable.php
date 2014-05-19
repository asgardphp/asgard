<?php
namespace Asgard\Hook;

class Hookable {
	protected $hook;

	/* INSTANCE */
	public function hasHook($name) {
		if(!$this->getHook()) return false;
		return $this->getHook()->has(array('instances', spl_object_hash($this), $name));
	}

	public function trigger($name, array $args=array(), $cb=null, $print=false) {
		if(!$this->getHook()) return;
		return $this->getHook()->trigger(array('instances', spl_object_hash($this), $name), $args, $cb, $print);
	}

	public function triggerChain(HookChain $chain, $name, array $args=array(), $cb=null, $print=false) {
		if(!$this->getHook()) return;
		return $this->getHook()->triggerChain($chain, array('instances', spl_object_hash($this), $name), $args, $cb, $print);
	}

	public function hook() {
		return call_user_func_array(array($this, 'hookOn'), func_get_args());
	}

	public function hookOn($hookName, $cb, $priority=0) {
		if(!$this->getHook()) return;
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array($this->getHook(), 'hookOn'), $args);
	}

	public function hookBefore($hookName, $cb, $priority=0) {
		if(!$this->getHook()) return;
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array($this->getHook(), 'hookBefore'), $args);
	}

	public function hookAfter($hookName, $cb, $priority=0) {
		if(!$this->getHook()) return;
		$args = array(array('instances', spl_object_hash($this), $hookName), $cb);
		return call_user_func_array(array($this->getHook(), 'hookAfter'), $args);
	}

	public function getHooks() {
		if(!$this->getHook()) return array();
		return $this->getHook()->getHooks(array('instances', spl_object_hash($this)));
	}

	public function getHook() {
		return $this->hook;
	}

	public function setHook(Hook $hook) {
		$this->hook = $hook;
	}
}
